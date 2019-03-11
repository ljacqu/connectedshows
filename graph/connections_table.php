<?php
require '../gen/config.php';
require '../inc/DatabaseHandler.php';
require '../inc/Template.php';

$dbh = new DatabaseHandler($config);

$all_shows_data = $dbh->getAllShows();

$allShows = [];
foreach ($all_shows_data as $entry) {
  $allShows[] = [
    'title' => htmlspecialchars($entry['title']),
    'short_title' => htmlspecialchars(abbreviateTitle($entry['title'], 15)),
    'id' => $entry['id']
  ];
}

$connectionsTag = [];
$connectionsMatrix = createShowConnectionsArray($dbh);
$percentiles = calculatePercentiles($connectionsMatrix);

foreach ($allShows as $show) {
  $value = [ ['val' => $show['title'], 'show_link' => false] ];
  $curId = $show['id'];
  foreach ($allShows as $connectedShow) {
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
$showsTag = array_merge($showsTag, $allShows);


Template::displayTemplate('connections_table.html', [
  'shows' => $showsTag,
  'connections' => $connectionsTag
]);


function createShowConnectionsArray(DatabaseHandler $dbh) {
  $showConnectionStatements = $dbh->getDbh()->query(
    file_get_contents('./inc/connections/get_connections_within_all_shows.sql'));
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