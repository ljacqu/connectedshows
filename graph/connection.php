<?php
error_reporting(E_ALL);

require '../inc/Utils.php';
require '../inc/Template.php';
require '../gen/config.php';
require '../inc/functions.php';
require '../inc/DatabaseHandler.php';

require './inc/ShowConnectionsManager.php';

$dbh = new DatabaseHandler($config);
$all_shows = $dbh->getAllShows()->fetchAll();

$input_shows = [];
$message = '';
$error = '';
$actors = [];
$show_roles_table = false;

do {
  if (empty($_SERVER['QUERY_STRING'])) {
    break;
  }

  $input_shows = array_intersect(
    explode('-', $_SERVER['QUERY_STRING']),
    array_map(function ($show) { return $show['id']; }, $all_shows));

  $total_shows = count($input_shows);
  if ($total_shows < 2) {
    $error = 'Need at least two shows!';
    break;
  }

  $show_list = implode(',', $input_shows);
  $sql = str_replace(['{show_list}', '{total_shows}'], [$show_list, $total_shows],
    file_get_contents('./inc/find_actors.sql'));
  $sql_data = $dbh->getDbh()->query($sql);

  $connections_manager = new ShowConnectionsManager($input_shows);
  foreach ($sql_data as $entry) {
    $connections_manager->addRole(
      $entry['actor_id'], $entry['name'], $entry['show_id'], $entry['role'], $entry['episodes']);
  }

  $actors = $connections_manager->getActorTags();
  $show_roles_table = true;
} while (0);

$selected_shows = array_map(function ($show) use ($dbh) {
  return ['selected_show_title' => $dbh->showTitle($show)];
}, $input_shows);

$form_shows = make_shows_dropdown(new ArrayIterator($all_shows), $input_shows);
$tags = [
  'form_shows' => $form_shows,
  'show_roles_table' => $show_roles_table,
  'actors' => $actors ?: [],
  'selected_shows' => $selected_shows,
  'message' => $message,
  'error' => $error
];
Template::displayTemplate('connection.html', $tags);


