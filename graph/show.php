<?php
error_reporting(E_ALL);

require '../inc/Utils.php';
require '../inc/Template.php';
require '../gen/config.php';
require '../inc/DatabaseHandler.php';

$show_name = '';
$similar_shows = [];
$message = '';

do {
  $show_id = Utils::getScalarInput(INPUT_GET, 'id', false);
  if (!$show_id || !preg_match('/^\\d+$/', $show_id)) {
    break;
  }

  $dbh = new DatabaseHandler($config);
  $get_title_query = $dbh->getDbh()->prepare('SELECT title FROM shows WHERE id = ?');
  $get_title_query->execute([$show_id]);
  $show_data = $get_title_query->fetch(PDO::FETCH_ASSOC);
  if (!$show_data) {
    $error = 'Unknown show ID';
    break;
  }
  $show_name = $show_data['title'];

  $similar_shows = $dbh->getDbh()->query(
    str_replace('{show_id}', $show_id,
      file_get_contents('./inc/shows_with_common_actors.sql')))->fetchAll();

  $message = 'There are ' . count($similar_shows) . ' shows with common actors';

} while (0);


$tags = [
  'message' => $message,
  'show_id' => $show_id,
  'show_name' => htmlspecialchars($show_name),
  'similar_shows' => $similar_shows
];
Template::displayTemplate('show.html', $tags);