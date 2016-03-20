<?php
require './inc/header.php';

/**
 * the available pages
 */
$pages = [
  page('create_dot_file', 'View TV Show Connections'),
  page('save_show_data', 'Save data of a TV show'),
  page('edit_config', 'Edit config'),
  page('system_check', 'System Check')
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

Template::displayTemplate("inc/index/index.html", ['pages' => $pages, 'has_problem' => $has_problem]);


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

function page($file, $title) {
  return ['file' => $file, 'title' => $title];
}