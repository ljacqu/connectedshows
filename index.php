<?php

error_reporting(E_ALL);
require './inc/Template.php';

$pages = [
  'create_dot_file' => 'View TV Show Connections',
  'save_show_data' => 'Save data of a TV show',
  'system_check' => 'System Check'
];

$problem = false;
try {
  if (!function_exists('curl_init') || !function_exists('exec') 
      || !file_exists('./gen/config.php')) {
    $problem = true;
  } else {
    require './gen/config.php';
    require './inc/DatabaseHandler.php';
    $dbh = new DatabaseHandler($config);
    $dbh->getTotalShows();
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
