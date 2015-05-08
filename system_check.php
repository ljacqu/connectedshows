<?php
error_reporting(E_ALL);

require './inc/DatabaseHandler.php';
require './inc/Template.php';

$test_result = [];

// ==================
// Quick directory check
// ==================
$dirs = ['./gen/', './html/', './inc/', './sql/'];
$missing_dir = [];
foreach ($dirs as $dir) {
	if (!file_exists($dir) || !is_dir($dir)) {
		$missing_dir[] = $dir;
	}
}
$test_result[0] = new TestItem("Folders exist");
$test_result[1] = new TestItem("./gen/ folder writable");
if (empty($missing_dir)) {
	if (!is_writable('./gen/')) {
		$test_result[1]->setError("Cannot write!");
	}
} else {
	$test_result[0]->setError("Directories do not exist: " . implode(" ", $missing_dir));
	$test_result[1]->setUndefined();
}


// =================
// Test for config file
// =================
unset($config);
$test_result[2] = new TestItem("./gen/config.php exists");
$config_ok = false;
if (file_exists('./gen/config.php')) {
	include './gen/config.php';
	if (!isset($config) || !is_array($config)) {
		$test_result[2]->setError("File exists but could not find config data."
			. " Please reset the config <a href=\"edit_config.php\">here</a>.");
	}
}
else {
	$test_result[2]->setError("File ./gen/config.php does not exist! Please run "
		. " the <a href=\"edit_config.php\">config module</a>.");
}


// ==================
// Test database
// ==================
$test_result[3] = new TestItem("Database login");
$test_result[4] = new TestItem("Retrieve database info");
$test_key = 0;
try {
	if ($test_result[2]->cssClass !== 'testgood') {
		throw new PDOException("noconfig", -12345);
	}
	$dbh = new DatabaseHandler($config);
	$test_result[3]->message = 'Can login';
	$total = $dbh->getTotalShows();
	$test_result[4]->message = 'Could get shows. (Found ' . $total . ' shows)';
} catch (PDOException $ex) {
	$test_key = empty($test_result[3]->message) ? 3 : 4;
	register_db_error($test_result[$test_key], $ex);
	if ($test_key == 3) $test_result[4]->setUndefined();
	else $test_result[4]->message .= '<br>&raquo; <a href="?maketables">Create tables</a>';
}


// ==================
// Create tables if desired
// ==================
$message = '';
if (isset($_GET['maketables'])) {
	if ($test_key == 3) {
		$message = '<span class="error">Error! Cannot run database installation'
			. ' because the login/database details are not correct.';
	} else {
		$sql = file_get_contents('./sql/create_tables.sql');
		$dbh->getDbh()->exec($sql);
		$message = 'Created any missing tables. Please run the '
				. '<a href="install.php">status check</a> to verify that everything works.';
		Template::displayTemplate('install', ['message' => $message, 'table' => '', 'show_footer' => 'none']);
		exit;
	}
}



// ==================
// Test exec dot
// ==================
$test_result[5] = new TestItem("<code>exec()</code> is enabled");
$test_result[6] = new TestItem("Can use <code>dot</code> command");
if (!function_exists('exec')) {
	$test_result[5]->setError("Function is disabled!");
	$test_result[6]->setUndefined();
} else if (!isset($config['graph_command'])) {
	$test_result[6]->setError("Could not find the config data to run command!");
} else {
	try {
		exec($config['graph_command'] . ' -?', $output, $code);
		if ($code !== 0) {
			$test_result[6]->setError("Command <code>" . htmlspecialchars($config['graph_command'])
				. " -?</code> returned code " . $code . "; please ensure that "
				. "the dot command is configured correctly in config.php");
		}
	} catch (Exception $ex) {
		$test_result[6]->setError("Encountered exception " . $ex->getMessage());
	}
}



// ==================
// Test cURL
// ==================
$test_result[7] = new TestItem("cURL extension is loaded");
$test_result[8] = new TestItem("Can connect to IMDb.com");
if (!function_exists('curl_init')) {
	$test_result[7]->setError("cURL is not loaded! Please add the extension to PHP."
		. "<br><small>You can still use this script but you cannot save any new show data.</small>");
	$test_result[8]->setUndefined();
} else {
	try {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://www.imdb.com/");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		$info = curl_getinfo($ch);
		if ($info['http_code'] !== 200) {
			$test_result[8]->setError("Encountered HTTP code " . $info['http_code']
				. " instead of expected 200! (Bad PHP setting? No internet connection? IMDb down?)"
				. "<br><small>You can still use this script but you cannot save any new show data.</small>");
		}
		curl_close($ch);
	}
	catch (Exception $ex) {
		$test_result[8]->setError("Encountered unexpected exception: <br>"
			. $ex->getMessage());
	}
}


// ------------------
// Output tests
// ------------------
$table = '<table class="overview">';
foreach ($test_result as $key => $test) {
	$bar = ($key%2 === 1 && $key > 1) ? ' class="bar"' : '';
	$table .= "\n <tr$bar><td>{$test->testName}</td>"
		. "<td class=\"{$test->cssClass}\">" . $test->getMessage() . '</td></tr>';
}
$table .= '</table>';
Template::displayTemplate('install', ['table' => $table, 'message' => $message, 'show_footer' => 'block']);



function register_db_error(TestItem $test_item, PDOException $ex) {
	global $config;
	
	switch ($ex->getCode()) {
		case -12345:
			$test_item->setUndefined(); return;
		case 1049:
			$test_item->setError("Could not find database <b>" 
					. htmlspecialchars($config['db_name']) . "</b>. Please create it or change config.php");
			break;
		case 1044:
			$test_item->setError("Invalid login with user <b>" 
					. htmlspecialchars($config['db_user']) . "</b>. Please verify the details in config.php");
			break;
		case '42S02':
		case '42S22':
			$test_item->setError("Database or column does not exist!"
					. " Please run database installation.");
			break;
		default:
			$test_item->setError("Encountered an error!");
	}
	$test_item->message .= "<br><small>(" . $ex->getMessage() . ")</small>";
}


class TestItem {	
	public $testName;
	public $message;
	public $cssClass;
	
	function __construct($testName) {
		$this->testName = $testName;
		$this->cssClass = 'testgood';
	}
	
	function setError($err) {
		$this->message = $err;
		$this->cssClass = 'testbad';
	}
	
	function getMessage() {
		return ($this->cssClass === "testgood" ? '&#x2713; ' : '&#x2718; ')
		 . (empty($this->message) ? 'Yes' : $this->message);
	}
	
	function setUndefined() {
		$this->message = 'Cannot evaluate (fix other error)';
		$this->cssClass = 'testvoid';
	}
}