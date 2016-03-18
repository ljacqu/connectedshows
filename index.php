<?php
require './inc/header.php';

/**
 * @var string[] the available pages
 */
$pages = [
  'create_dot_file' => 'View TV Show Connections',
  'save_show_data' => 'Save data of a TV show',
  'system_check' => 'System Check'
];

// -------
// Check system status
// -------
$has_problem = doesSystemHaveProblem();
if (!$has_problem) {
  require './gen/config.php';
  require './inc/DatabaseHandler.php';
  $has_problem = doesDatabaseHaveProblem($config);
}

$page_list = '';
foreach ($pages as $file => $title) {
  $page_list .= "\n <li><a href=\"$file.php\">$title</a>";
}

Template::displayTemplate("home", ['page_list' => $page_list, 'has_problem' => $has_problem]);


function doesSystemHaveProblem() {
  // We require the CURL extension for retrieving IMDb data, and exec for generating the graphs via GraphWiz
  return !function_exists('curl_init') || !function_exists('exec') || !file_exists('./gen/config.php');
}

function doesDatabaseHaveProblem($config) {
  try {
    $dbh = new DatabaseHandler($config);
    return $dbh->healthcheck() !== '1';
  } catch (PDOException $ex) {
    return true;
  }
}