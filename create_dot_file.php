<?php

require './inc/header.php';
require './gen/config.php';
require './inc/DatabaseHandler.php';

require './inc/create_dot_file/ConnectionParameters.php';
require './inc/create_dot_file/SqlHelper.php';
require './inc/create_dot_file/DotWriter.php';

$dbh = new DatabaseHandler($config);
$form_error = '';
$result = '';
$connection_params = new ConnectionParameters();

do {
  if (!isset($_POST['shows'])) {
    break;
  }

  $connection_params->readFromInput();
  if ($connection_params->hasErrors()) {
    $form_error .= implode('<br>', $connection_params->getErrors());
    break;
  }

  // Execute SQL query
  $sql_query = SqlHelper::connSqlQuery($connection_params);

  $query = $dbh->getDbh()->query($sql_query);
  if ($query->rowCount() === 0) {
    $form_error .= '<br>Could not get any movie connections!';
    break;
  }

  // Write DOT file
  $penwidth = function ($common_actors) {
    $max = 3;
    $weighted_edge = round(pow($common_actors, 1 / 1.5), 2);
    return $weighted_edge <= $max ? $weighted_edge : $max;
  };
  $color_function = function ($penwidth, $show1, $show2) {
  //$colors = ['red', 'dodgerblue2', 'chartreuse4', 'darkorchid2', 'darkorange', 'gold', 'chocolate4', 'deeppink3', 'gray19'];
  //return $colors[rand(0, count($colors)-1)];
    return 'black';
  };
  $form_file = $connection_params->getFile();
  $dot_writer = new DotWriter('./gen/' . $form_file, $penwidth, $color_function, $dbh);
  $dot_writer->createFile($query);

  // Output success and some figures
  $show_selection_size = count($connection_params->getShows());
  $limit_to_selection = $connection_params->getLimitToSelection();
  $max_connections = max_connections($limit_to_selection, $show_selection_size, $dbh);
  $result .= '<br>Successfully wrote to file ' . $form_file
    . '<br>Found ' . $query->rowCount() . ' connections for '
    . $show_selection_size . ' shows, limited to selection: ' . ($limit_to_selection ? 'true' : 'false')
    . '<br>We would expect a maximum of ' . $max_connections . ' connections.';

  // If desired/allowed, run DOT command
  //$output_file = './gen/' . substr($form_file, 0, -4) . '.png';
  //$cmd = "dot -Tpng \"./gen/$form_file\" -o \"$output_file\"";
  $output_file = './gen/' . substr($form_file, 0, -4) . '.svg';
  $cmd = "dot -Tsvg \"./gen/$form_file\" -o \"$output_file\"";
  exec($cmd);

  $result .= '<hr>Created .png file.'
    . "<br><a href=\"{$output_file}\">"
    //. "<img src=\"{$output_file}\" alt=\"Graph\" style=\"max-width: 100%; max-height: 100%\">"
    . "<object data=\"$output_file\" type=\"image/svg+xml\"></object>"
    . "</a>";
} while (0);

$all_shows = [];
$selected_shows = $connection_params->getShows();
foreach ($dbh->getAllShows() as $show) {
  $is_checked = ['is_checked' => in_array($show['id'], $selected_shows)];
  $all_shows[] = array_merge($show, $is_checked);
}

$types = [];
foreach (['min', 'sum', 'max'] as $type) {
  $types[] = [
    'type' => $type,
    'type_checked' => $connection_params->getThresholdType() === $type,
    'type_text' => $type . '(a,b)'
  ];
}

$available_units = ['n' => 'Number of episodes', 'p' => 'Percentage'];
$units = [];
foreach ($available_units as $code => $title) {
  $units[] = [
    'unit_code' => $code,
    'unit_text' => $title,
    'unit_checked' => $connection_params->getUnit() === $code
  ];
}

$tags = array_merge($connection_params->getTagCollection(), [
  'all_shows' => $all_shows,
  'result' => $result,
  'form_error' => $form_error,
  'units' => $units,
  'types' => $types
]);

Template::displayTemplate('inc/create_dot_file/create_dot_file.html', $tags);

/**
 * Computes the maximum possible number of connections between shows
 * @param bool $limited If the connections are limited among the selected shows
 * @param int $selected_shows The number of selected shows
 * @param DatabaseHandler $dbh DatabaseHandler object
 * @return int maximum possible number of connections
 */
function max_connections($limited, $selected_shows, DatabaseHandler $dbh) {
  if ($limited) {
    return $selected_shows * ($selected_shows - 1) / 2;
  } else {
    $total_shows = $dbh->getTotalShows() - $selected_shows;
    return $selected_shows * ($selected_shows - 1) / 2 + $selected_shows * $total_shows;
  }
}
