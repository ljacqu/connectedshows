<?php
error_reporting(E_ALL);

require 'config.php';
require './inc/DatabaseHandler.php';
require './inc/SqlHelper.php';
require './inc/Template.php';
require './inc/functions.php';

$dbh = new DatabaseHandler;
$form_file = '';
$shows = [];
$form_error = '';
$result = '';
$limited = true;
$threshold = 0;
$type = ['min' => false, 'max' => false, 'sum' => false];
$unit = ['n' => false, 'p' => false];

do {
	if (isset($_POST['shows']) && is_array($_POST['shows']) && all_numeric($_POST['shows'])) {
		$shows = $_POST['shows'];
	} else {
		$form_error .= '<br>Please select the shows to include.';
	}
	
	if (isset($_POST['file']) && is_scalar($_POST['file'])) {
		if (preg_match('/^[a-z0-9-_\(\)]+(\.dot)?$/i', $_POST['file'], $matches)) {
			$form_file = $_POST['file'] . (isset($matches[1]) ? '' : '.dot');
			if (file_exists('./gen/' . $form_file)) {
				$result .= 'Note: file already exists... overwriting';
			}
		} else {
			$form_file = htmlspecialchars($_POST['file']);
			$form_error .= '<br>Please enter a valid filename!';
		}
	} else {
		$form_error .= '<br>Please enter a filename.';
	}

	
	if (isset($_POST['threshold']) && is_numeric($_POST['threshold'])) {
		$threshold = (int) $_POST['threshold'];
	}
	
	if (isset($_POST['type']) && is_string($_POST['type']) && isset($type[$_POST['type']])) {
		$type[$_POST['type']] = true;
	} else {
		$type['min'] = true;
	}
	
	if (isset($_POST['unit']) && is_string($_POST['unit']) && isset($unit[$_POST['unit']])) {
		$unit[$_POST['unit']] = true;
	} else {
		$unit['n'] = true;
	}

	if (!empty($form_error)) {
		if (!isset($_POST['shows'])) $form_error = '';
		break;
	}
	
	$limited = isset($_POST['limited']);

	
	// Execute SQL query
	$sql_query = SqlHelper::connSqlQuery($threshold, $type, $limited, $unit['p']);
	$sql_query = str_replace('{list}', '(' . implode(',', $shows) . ')', $sql_query);
	
	$query = $dbh->getDbh()->query($sql_query);
	if ($query->rowCount() === 0) {
		$form_error .= '<br>Could not get any movie connections!';
		break;
	}
	
	// Write DOT file
	$penwidth = function ($common_actors) {
		$max = 3;
		$weighted_edge = round(pow($common_actors, 1/1.5), 2);
		return $weighted_edge <= $max ? $weighted_edge : $max;
	};
	$color_function = function ($penwidth, $show1, $show2) {
		//$colors = ['red', 'dodgerblue2', 'chartreuse4', 'darkorchid2', 'darkorange', 'gold', 'chocolate4', 'deeppink3', 'gray19'];
		//return $colors[rand(0, count($colors)-1)];
		return 'black';
	};
	require './inc/DotWriter.php';
	$dot_writer = new DotWriter('./gen/' . $form_file, $penwidth, $color_function, $dbh);
	$dot_writer->createFile($query);
	
	// Output success and some figures
	$max_connections = max_connections($limited, $shows, $dbh);
	$result .= '<br>Successfully wrote to file ' . $form_file
		. '<br>Found ' . $query->rowCount() . ' connections for '
		. count($shows) . ' shows with limited=' . strval($limited)
		. '<br>We would expect a maximum of ' . $max_connections . ' connections.';
	
	// If desired/allowed, run DOT command
	//$output_file = './gen/' . substr($form_file, 0, -4) . '.png';
	//$cmd = "dot -Tpng \"./gen/$form_file\" -o \"$output_file\"";
	$output_file = './gen/' . substr($form_file, 0, -4) . '.svg';
	$cmd = "dot -Tsvg \"./gen/$form_file\" -o \"$output_file\"";
	exec($cmd);
	
	$result .= '<hr>Created .png file.'
		. "<br><a href=\"{$output_file}\">"
		//. "<img src=\"{$output_file}\" alt=\"Graph\" style=\"max-width: 100%; max-height: 100%\">"
		. "<object data=\"$output_file\" type=\"image/svg+xml\"></object>"
		. "</a>";
	
} while(0);


$form_shows = make_shows_dropdown($dbh->getAllShows(), $shows);

$radio_unit = create_radios('unit', $unit, ['Number of episodes', 'Percentage']);
$radio_type = create_radios('type', $type, ['min(a,b)', 'sum(a,b)', 'max(a,b)']);

$tags = [
	'result'     => $result,
	'form_error' => $form_error,
	'php_self'   => $_SERVER['PHP_SELF'],
	'form_file'  => $form_file,
	'checked_limited' => ($limited ? ' checked="checked"' : ''),
	'form_threshold'  => $threshold,
	'radio_type' => $radio_type,
	'radio_unit' => $radio_unit,
	'form_shows' => $form_shows
];
Template::displayTemplate('create_dot_file', $tags);


/**
 * Creates HTML output of radios
 * @param string $name The name of the radio (HTML name attribute)
 * @param array $options The options of the radio, where the key is the option
 *  value and the associated value a boolean, indicating if it is checked
 * @param array $text The text next to the radio (key: option value,
 *   value: text ot show)
 * @return string The generated HTML code
 */
function create_radios($name, array $options, array $text) {
	$result = "";
	foreach ($options as $value => $is_checked) {
		$id = $name . $value;
		$result .= "\n<input type=\"radio\" name=\"$name\" value=\"$value\" id=\"$id\""
			. ($is_checked ? ' checked="checked"' : '')
			. "> <label for=\"$id\">" . current($text) . "</label>";
		next($text);
	}
	return $result;
}

function max_connections($limited, array $shows, DatabaseHandler $dbh) {
	if ($limited) {
		return count($shows) * (count($shows)-1) / 2;
	} else {
		$total_shows = $dbh->getTotalShows();
		return count($shows) * ($total_shows-1) / 2;
	}
}