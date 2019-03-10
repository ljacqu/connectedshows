<?php
error_reporting(E_ALL);
require './gen/config.php';
require './inc/functions.php';
require './inc/Template.php';
require './inc/DatabaseHandler.php';
require './inc/Utils.php';

require './inc/save_show_data/SaveShowDatabaseController.php';
require './inc/save_show_data/ImdbShowPageRetriever.php';
require './inc/save_show_data/HtmlDataRetriever.php';

$show_reset = false;
$show_input = Utils::getScalarInput(INPUT_POST, 'id', '');
$result = '';
$existing_shows = [];
$error = '';

$databaseHandler = new DatabaseHandler($config);
$db_controller = new SaveShowDatabaseController($databaseHandler->getDbh());

do {
  try {
    $idToDelete = Utils::getScalarInput(INPUT_POST, 'delete', '');
    if (!empty($idToDelete)) {
      if ($db_controller->showExists($idToDelete)) {
        $db_controller->deleteShow($idToDelete);
        $result = 'Deleted show';
        break;
      } else {
        throw new Exception('Show to delete does not exist');
      }
    }

    // Keeps track whether we need to do a reset (show already persisted -> delete before writing)
    $delete_show_before_write = false;

    $idToReload = Utils::getScalarInput(INPUT_POST, 'reload', '');
    if (!empty($idToReload)) {
      $show_id = str_pad($idToReload, 7, '0', STR_PAD_LEFT);
      $delete_show_before_write = true;
    } else if (!empty($show_input)) {
      $show_id = ImdbShowPageRetriever::retrieveShowIdFromUrl($show_input);
      $delete_show_before_write = isset($_POST['reset']);
    } else {
      break;
    }

    // Check if show exists
    if ($db_controller->showExists($show_id)) {
      if (!$delete_show_before_write) {
        $show_reset = true;
        throw new Exception('Show already exists! Did not save.<br>Use reset below to copy the data from IMDb again.');
      }
    }

    // Load cast page HTML
    $imdb_page = ImdbShowPageRetriever::loadCastPageForId($show_id);
    // Extract title and check that ID matches
    $show_title = HtmlDataRetriever::getShowTitle($show_id, $imdb_page);
    // Extract info from HTML into array
    $entry = HtmlDataRetriever::extractCastEntries($imdb_page);

    // Delete existing entries of show if so defined
    if ($delete_show_before_write) {
      $db_controller->deleteShow($show_id);
    }

    // Save show & output info
    $db_controller->saveShowInfo($show_title, $show_id, $entry);
    $result = "Saved show info for $show_title ($show_id)"
            . '<br>Actors: ' . count($entry);
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
} while (0);

if (isset($_GET['showexisting'])) {
  $dbh = $databaseHandler->getDbh();
  $show_data = $dbh->query('SELECT id, title, episodes, retrieval_date FROM shows ORDER BY retrieval_date, title');

  foreach ($show_data as $entry) {
    $existing_shows[] = [
      'id' => $entry['id'],
      'title' => $entry['title'],
      'episodes' => $entry['episodes'],
      'retrieval_date' => $entry['retrieval_date']
    ];
  }
}

$tags = [
  'result' => $result,
  'form_error' => $error,
  'form_input' => htmlspecialchars($show_input),
  'show_reset' => $show_reset,
  'existing_shows' => $existing_shows
];
Template::displayTemplate('inc/save_show_data/save_show_data.html', $tags);
