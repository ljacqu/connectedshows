<?php
error_reporting(E_ALL);
require './inc/Template.php';

// Default values
$config_input = [
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

if (isset($_POST['default'])) {
	$write_to_file = true;
}
else {
	if (file_exists('./gen/config.php')) {
		include './gen/config.php';
		foreach ($config_input as $key => $value) {
			if (isset($config[$key]) && is_scalar($config[$key]) && $key !== 'commands') {
				$config_input[$key] = $config[$key];
			}
		}
		$config_input['commands'] = isset($config['commands'])
				? $config['commands'] : $config_input['commands'];
	}
	if (isset($_POST['update'])) {
		$write_to_file = true;
		foreach ($config_input as $key => $value) {
			if ($key === 'commands') continue;
			$config_input[$key] = filter_input(INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_SCALAR)
						?: $config_input[$key];
		}
		$commands = filter_input(INPUT_POST, 'commands', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		$config_input['commands'] = check_format_commands($commands)
					?: $config_input['commands'];
	}	
}

$message = '';
if ($write_to_file) {
	$fh = fopen('./gen/config.php', 'w');
	if ($fh === false) {
		$message = 'Error! Could not write to the config file (./gen/config.php).'
				. ' Please ensure that ./gen/ exists and that the file is writable.';
	} else {
		fwrite($fh, '<?php $config = ' . var_export($config_input, true) . ';');
		fclose($fh);
		$message = 'Saved the changes!';
	}
}

$format_rows = "";
$tpl_format_row = Template::getTemplateText('edit_config_format_row');
$id = 0;
foreach ($config_input['commands'] as $type => $command) {
	$format_tags = ['type' => htmlspecialchars($type), 'command' => htmlspecialchars($command), 'id' => ++$id];
	$format_rows .= Template::prepareTemplate($tpl_format_row, $format_tags);
}
$tags = ['format_rows' => $format_rows];
foreach ($config_input as $key => $value) {
	if ($key !== 'commands') {
		$tags[$key] = htmlspecialchars($value);
	}
}
$tags['message'] = $message;

Template::displayTemplate('edit_config', $tags);



function check_format_commands($format_cmds) {
	$result = [];
	foreach ($format_cmds as $command) {
		if (empty($command) || !isset($command['name']) || !isset($command['command'])) {
			continue;
		} else if (!preg_match('~^\\w+$~', $command['name'])) {
			return false;
		}
		$result[$command['name']] = $command['command'];
	}
	return $result;
}