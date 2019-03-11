<?php
require '../gen/config.php';
require '../inc/DatabaseHandler.php';
require '../inc/Template.php';
require '../inc/Utils.php';

require './inc/connections/functions.php';

$dbh = new DatabaseHandler($config);

$allShowsData = $dbh->getAllShows();
$allShows = [];
foreach ($allShowsData as $entry) {
  $allShows[] = [
    'title' => htmlspecialchars($entry['title']),
    'short_title' => htmlspecialchars(abbreviateTitle($entry['title'], 15)),
    'id' => $entry['id']
  ];
}

$selectedShowIds = [];
$showsTag = [];
$connectionsTag = [];
$leastThreshold = 1;
$greatestThreshold = 1;

do {
  $selectedShowIds = Utils::getArrayInput(INPUT_POST, 'shows', []);
  $selectedShowIds = array_intersect(
    $selectedShowIds,
    array_map(function ($show) { return $show['id']; }, $allShows));

  if (count($selectedShowIds) < 2) {
    break;
  }

  $leastThreshold = max(1, Utils::getIntegerInput(INPUT_POST, 'least', 1));
  $greatestThreshold = max(1, Utils::getIntegerInput(INPUT_POST, 'greatest', 1));

  $connectionsTag = [];
  $connectionsMatrix = createShowConnectionsArray($dbh, implode(',', $selectedShowIds), $leastThreshold, $greatestThreshold);
  $percentiles = calculatePercentiles($connectionsMatrix);

  $allSelectedShows = array_filter($allShows, function ($show) use ($selectedShowIds) {
    return in_array($show['id'], $selectedShowIds);
  });
  foreach ($allSelectedShows as $show) {
    $value = [ ['val' => $show['title'], 'show_link' => false] ];
    $curId = $show['id'];
    foreach ($allSelectedShows as $connectedShow) {
      $cellValue = $connectedShow['id'] === $curId ? '/'
        : getNumberFrom2dArrayOrZero($connectionsMatrix, $curId, $connectedShow['id']);
      $showLink = $cellValue !== '/' && $cellValue > 0;
      $grade = getGradeFromValue($cellValue, $percentiles);
      $value[] = ['val' => $cellValue, 'id1' => $curId, 'id2' => $connectedShow['id'], 'show_link' => $showLink,
        'grade' => $grade];
    }
    $connectionsTag[] = ['cells' => $value];
  }

  $showsTag = [
    ['title' => '', 'id' => '', 'short_title' => '']
  ];
  $showsTag = array_merge($showsTag, $allSelectedShows);

} while (0);

$formShows = make_shows_dropdown($allShows, $selectedShowIds, []);

Template::displayTemplate('connections_table.html', [
  'form_shows' => $formShows,
  'shows' => $showsTag,
  'connections' => $connectionsTag,
  'least' => $leastThreshold,
  'greatest' => $greatestThreshold
]);


function createShowConnectionsArray(DatabaseHandler $dbh, string $showList,
                                    int $leastThreshold, int $greatestThreshold) {
  $showConnectionStatements = $dbh->getDbh()->query(
    str_replace(
      ['{show_list}', '{least_threshold}', '{greatest_threshold}'],
      [$showList, $leastThreshold, $greatestThreshold],
      file_get_contents('./inc/connections/get_connections_within_shows.sql')));
  $showConnections = [];
  foreach ($showConnectionStatements as $connection) {
    $show1 = $connection['show_a'];
    $show2 = $connection['show_b'];

    if (!isset($showConnections[$show1])) {
      $showConnections[$show1] = [];
    }
    $showConnections[$show1][$show2] = $connection['actors'];

    if (!isset($showConnections[$show2])) {
      $showConnections[$show2] = [];
    }
    $showConnections[$show2][$show1] = $connection['actors'];
  }
  return $showConnections;
}

function getNumberFrom2dArrayOrZero($arr, $key1, $key2) {
  if (isset($arr[$key1])) {
    return $arr[$key1][$key2] ?? 0;
  }
  return 0;
}

function calculatePercentiles($connectionMatrix) {
  $values = [];
  foreach ($connectionMatrix as $k => $connections) {
    foreach ($connections as $j => $connection) {
      if ($k < $j) {
        $values[] = $connection;
      }
    }
  }

  sort($values);
  $length = count($values);
  $quarter = $length / 4;

  return [
    0,
    $values[min($length, $quarter)],
    $values[min($length, $quarter * 2)],
    $values[min($length, $quarter * 3)],
  ];
}

function getGradeFromValue($cellValue, $percentiles) {
  if ($cellValue === '/') {
    return false;
  } else if ($cellValue <= $percentiles[0]) {
    return 4;
  } else if ($cellValue < $percentiles[1]) {
    return 3;
  } else if ($cellValue < $percentiles[2]) {
    return 2;
  }
  return 1;
}

function abbreviateTitle($title, $len) {
  if (strlen($title) > $len) {
    return substr($title, 0, $len) . '…';
  }
  return $title;
}