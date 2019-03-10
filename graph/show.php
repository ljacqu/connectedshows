<?php
error_reporting(E_ALL);

require '../inc/Utils.php';
require '../inc/Template.php';
require '../gen/config.php';
require '../inc/DatabaseHandler.php';

require './inc/shows/ShowOverviewController.php';
require './inc/shows/ShowDetailController.php';

$show_name = '';
$similar_shows = [];
$message = '';


$dbh = new DatabaseHandler($config);

$show_id = Utils::getScalarInput(INPUT_GET, 'id', '');
if ($show_id) {
  $ctrl = new ShowDetailController($dbh);
  $ctrl->run($show_id);
} else {
  $ctrl = new ShowOverviewController($dbh);
  $ctrl->run();
}

