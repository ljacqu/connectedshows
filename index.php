<?php
error_reporting(E_ALL);
require './inc/Template.php';

$pages = [
	'create_dot_file' => 'Generate graph',
	'save_show_data' => 'Save show data'
];

$page_list = '';
foreach ($pages as $file => $title) {
	$page_list .= "\n <li><a href=\"$file.php\">$title</a>";
}

Template::displayTemplate("home", ['page_list' => $page_list]);