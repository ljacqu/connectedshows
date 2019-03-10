<?php
error_reporting(E_ALL);

require '../inc/Utils.php';
require '../inc/Template.php';
require '../gen/config.php';
require '../inc/DatabaseHandler.php';

require './inc/actors/ActorDetailController.php';
require './inc/actors/ActorOverviewController.php';

$actor_id = Utils::getIntegerInput(INPUT_GET, 'id', '');

$dbh = new DatabaseHandler($config);
if ($actor_id) {
  $actorDetailCtrl = new ActorDetailController($actor_id, $dbh);
  $actorDetailCtrl->run();
} else {
  $minimum_input = max(1, Utils::getIntegerInput(INPUT_POST, 'min', 5));
  $actorOverviewCtrl = new ActorOverviewController($minimum_input, $dbh);
  $actorOverviewCtrl->run();
}

