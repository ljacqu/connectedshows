<?php
/**
 * Deletes a saved configuration.
 */
error_reporting(E_ALL);

require '../HttpStatus.php';
define('SETS_FILE', '../../gen/configs.json');

if (!isset($_POST['id'])) {
  http_response_code(HttpStatus::BAD_REQUEST);
  die('Missing id');
}

$sets = json_decode(file_get_contents(SETS_FILE), true);
$input_id = $_POST['id'];

if (isset($sets[$input_id])) {
  unset($sets[$input_id]);
  $fh = fopen(SETS_FILE, 'w');
  if ($fh) {
    fwrite($fh, json_encode($sets));
    fclose($fh);
    http_response_code(HttpStatus::OK);
    die('Config deleted');
  } else {
    http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
    die('Could not write to file');
  }
} else {
  http_response_code(HttpStatus::NOT_FOUND);
  die('Config not found');
}
