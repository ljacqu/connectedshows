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
$similar_actors = [];

do {
  if (!$actor_id || !preg_match('/^\\d+$/', $actor_id)) {
    // TODO: Need a better entry page
    $message = 'No actor ID specified.';
    break;
  }

  $dbh = new DatabaseHandler($config);

  $get_name_query = $dbh->getDbh()->prepare('SELECT name FROM actors WHERE id = ?');
  $get_name_query->execute([$actor_id]);
  $actor_data = $get_name_query->fetch(PDO::FETCH_ASSOC);

  if (!$actor_data) {
    $error = 'Actor ID is unknown.';
    break;
  }

  $actor_name = $actor_data['name'];

  $sql_roles = $dbh->getDbh()->query(
    str_replace('{actor_id}', $actor_id,
      file_get_contents('./inc/find_shows_of_actor.sql')));
  $roles = $sql_roles->fetchAll(PDO::FETCH_ASSOC);

  $sql_actors = $dbh->getDbh()->query(
    str_replace('{actor_id}', $actor_id,
      file_get_contents('./inc/find_most_common_actors.sql')));
  $similar_actors = $sql_actors->fetchAll(PDO::FETCH_ASSOC);

  $has_actor_data = true;
} while (0);

$tags = [
  'message' => $message,
  'error' => $error,
  'roles' => $roles,
  'has_actor_data' => $has_actor_data,
  'actor_name' => $actor_name,
  'actor_id' => $actor_id,
  'similar_actors' => $similar_actors
];

Template::displayTemplate('actor.html', $tags);