<?php
/**
 * Edits or saves a new configuration.
 */
error_reporting(E_ALL);

require '../HttpStatus.php';
define('SETS_FILE', '../../gen/configs.json');

if (!isset($_POST['config']) || !is_array($_POST['config'])) {
  http_response_code(HttpStatus::BAD_REQUEST);
  die('No data.');
}

$input_config = sanitize_input_config($_POST['config']);
$sets = json_decode(file_get_contents(SETS_FILE), true);

if (empty($input_config['id'])) {
  $set_id = uniqid();
} else {
  $set_id = $input_config['id'];
  if (!isset($sets[$set_id])) {
    http_response_code(HttpStatus::NOT_FOUND);
    die('Config does not exist.');
  }
  // Unset id property as not to save it, since it is present in the key
  unset($input_config['id']);
}
$sets[$set_id] = $input_config;

$fh = fopen(SETS_FILE, 'w');
if ($fh) {
  fwrite($fh, json_encode($sets));
  fclose($fh);
  header('Content-Type: application/json');
  http_response_code(HttpStatus::OK);
  die(json_encode(['id' => $set_id]));
} else {
  http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
  die('Could not open configs file.');
}

/**
 * Performs some basic validation on an input configuration and only keeps valid properties.
 *
 * @param $raw_config mixed[] the raw configuration to sanitize
 * @return mixed[] sanitized configuration
 */
function sanitize_input_config($raw_config) {
  // Keys of properties with scalar values
  $keys = ['id', 'name', 'fileName', 'threshold', 'type', 'unit'];
  $clean_config = [];
  foreach ($keys as $key) {
    if (!empty($raw_config[$key]) && is_scalar($raw_config[$key])) {
      $clean_config[$key] = $raw_config[$key];
    }
  }
  if (!empty($raw_config['shows']) && is_array($raw_config['shows'])) {
    $clean_config['shows'] = $raw_config['shows'];
  }
  return $clean_config;
}
