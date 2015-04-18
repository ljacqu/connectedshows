<?php
error_reporting(E_ALL);
require 'config.php';
require './inc/Template.php';
require './inc/DatabaseHandler.php';

$pages = [
	'create_dot_file' => 'View TV Show Connections',
	'save_show_data' => 'Save data of a TV show',
	'install' => 'Install &amp; Status Check'
];

$problem = false;
try {
	if (!function_exists('curl_init') || !function_exists('exec')) {
		$problem = true;
	} else {
		$dbh = new DatabaseHandler;
	}
} catch (PDOException $ex) {
	$problem = true;
}

$page_list = '';
if ($problem) {
	$page_list .= '<p class="errorbox">You may need '
		. 'to install or troubleshoot this instance.'
		. '<br>&gt; <a href="install.php">Installer</a></p>';
}

$page_list .= "\n<p>Pages:</p>\n<ol class=\"pagelist\">";
foreach ($pages as $file => $title) {
	$page_list .= "\n <li><a href=\"$file.php\">$title</a>";
}
$page_list .= '</ol>';

Template::displayTemplate("home", ['page_list' => $page_list]);