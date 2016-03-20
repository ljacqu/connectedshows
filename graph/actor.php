<?php
error_reporting(E_ALL);

require '../inc/Utils.php';
require '../inc/Template.php';
require '../gen/config.php';
require '../inc/DatabaseHandler.php';

$actor_id = Utils::getScalarInput(INPUT_GET, 'id', '');
$error = '';
$has_actor_data = false;
$roles = [];
$message = '';
$actor_name = '';

if ($actor_id && preg_match('/^\\d+$/', $actor_id)) {
  $dbh = new DatabaseHandler($config);

  $get_name_query = $dbh->getDbh()->prepare('SELECT name FROM actors WHERE id = ?');
  $get_name_query->execute([$actor_id]);
  $actor_data = $get_name_query->fetch(PDO::FETCH_ASSOC);

  if ($actor_data) {
    $actor_name = $actor_data['name'];

    $sql_data = $dbh->getDbh()->query(
      str_replace('{actor_id}', $actor_id,
        file_get_contents('./inc/find_shows_of_actor.sql')));
    $roles = $sql_data->fetchAll(PDO::FETCH_ASSOC);
    $has_actor_data = true;
  } else {
    $error = 'Actor ID is unknown.';
  }
} else {
  // TODO: Need a better entry page
  $message = 'No actor ID specified.';
}

$tags = [
  'message' => $message,
  'error' => $error,
  'roles' => $roles,
  'has_actor_data' => $has_actor_data,
  'actor_name' => $actor_name,
  'actor_id' => $actor_id
];

Template::displayTemplate('actor.html', $tags);