<?php

if (!isset($_GET['id']) || !is_scalar($_GET['id'])) {
  die(json_encode(['error' => 'Actor id is required']));
}
require '../../../gen/config.php';
require '../../../inc/DatabaseHandler.php';
$dbh = (new DatabaseHandler($config))->getDbh();

$actor_id = $_GET['id'];

$sql_roles = $dbh->prepare(
  file_get_contents('find_shows_of_actor.sql'));
$sql_roles->execute(['actor_id' => $actor_id]);
$roles = $sql_roles->fetchAll(PDO::FETCH_ASSOC);

$sql_actors = $dbh->prepare(
  file_get_contents('find_most_common_actors.sql'));
$sql_actors->execute(['actor_id' => $actor_id]);
$similar_actors = $sql_actors->fetchAll(PDO::FETCH_ASSOC);

$similar_actors_id_list = implode(',',
  array_map(function ($entry) { return $entry['similar_actor_id']; }, $similar_actors));

$sql_actor_roles = $dbh->prepare(
  str_replace(':actor_list', $similar_actors_id_list,
    file_get_contents('related_actor_roles.sql')));
$sql_actor_roles->execute(['main_actor' => $actor_id]);
$similar_actor_roles = $sql_actor_roles->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'roles' => $roles,
  'actors' => $similar_actors,
  'actor_roles' => $similar_actor_roles
]);