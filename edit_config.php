<?php
require './inc/header.php';

// Default values
$default_config = [
  'db_name' => 'showconnections',
  'db_user' => 'root',
  'db_pass' => '',
  'graph_command' => 'dot',
  'commands' => [
    'svg' => 'dot -Tsvg {file} -o {output}',
    'png' => 'dot -Tpng {file} -o {output}'
  ],
];

$write_to_file = false;

$config = [];
if (isset($_POST['default'])) {
  $config = $default_config;
  $write_to_file = true;
} else {
  if (file_exists('./gen/config.php')) {
    // Load $config from file
    require './gen/config.php';
    $config = merge_default_into_current_config($config, $default_config);
  }

  if (isset($_POST['update'])) {
    $write_to_file = true;
    foreach ($default_config as $key => $value) {
      if ($key !== 'commands') {
        $config[$key] = Utils::getScalarInput(INPUT_POST, $key, $value);
      }
    }
    $commands = check_format_commands(Utils::getArrayInput(INPUT_POST, 'commands', null));
    if ($commands !== false) {
      $config['commands'] = $commands;
    }
  }
}

$message = '';
if ($write_to_file) {
  $fh = fopen('./gen/config.php', 'w');
  if ($fh === false) {
    $message = 'Error! Could not write to the config file (./gen/config.php).'
      . ' Please ensure that ./gen/ exists and that the file is writable.';
  } else {
    fwrite($fh, '<?php $config = ' . var_export($config, true) . ';');
    fclose($fh);
    $message = 'Saved the changes!';
  }
}

$id = 0;
$commands_for_html = [];
foreach ($config['commands'] as $name => $command) {
  $commands_for_html[] = [
    'name' => htmlspecialchars($name),
    'command' => htmlspecialchars($command),
    'id' => $id++
  ];
}
$tags = ['commands' => $commands_for_html, 'message' => $message];

foreach ($config as $key => $value) {
  if ($key !== 'commands') {
    $tags[$key] = htmlspecialchars($value);
  }
}

Template::displayTemplate('inc/edit_config/edit_config.html', $tags);

function check_format_commands($format_cmds) {
  if (!isset($format_cmds) || !is_array($format_cmds)) {
    return false;
  }

  $result = [];
  foreach ($format_cmds as $command) {
    if (empty($command) || !isset($command['name']) || !isset($command['command'])) {
      continue;
    } else if (!preg_match('~^[a-z0-9_-]+$~i', $command['name'])) {
      return false;
    }
    $result[$command['name']] = $command['command'];
  }
  return $result;
}

function merge_default_into_current_config(array $config, array $default_config) {
  foreach ($default_config as $key => $value) {
    if ($key !== 'commands' && (!isset($config[$key]) || !is_scalar($config[$key]))) {

      $config[$key] = $value;
    }
  }
  if (!isset($config['commands']) || !is_array($config['commands'])) {
    $config['commands'] = $default_config['commands'];
  }
  return $config;
}
