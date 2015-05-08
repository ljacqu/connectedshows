<?php
error_reporting(E_ALL);
require './inc/Template.php';

// Default values
$config_input = [
 'db_name' => 'showconnections',
 'db_user' => 'root',
 'db_pass' => '',
 'graph_command' => 'dot',
 'format_commands' => [
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
			if (isset($config[$key]) && is_scalar($config[$key]) && $key !== 'format_commands') {
				$config_input[$key] = $config[$key];
			}
		}
		$config_input['format_commands'] = isset($config['format_commands'])
				? $config['format_commands'] : $config_input['format_commands'];
	}
	if (isset($_POST['update'])) {
		$write_to_file = true;
		foreach ($config_input as $key => $value) {
			if ($key === 'format_commands') continue;
			$config_input[$key] = filter_input(INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_SCALAR)
						?: $config_input[$key];
		}
		$format_names = filter_input(INPUT_POST, 'format_names', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY); 
		$format_cmds = filter_input(INPUT_POST, 'format_commands', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
		$config_input['format_commands'] = construct_format_cmd_array($format_names, $format_cmds)
					?: $config_input['format_commands'];
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
$config_input['format_commands'][""] = "";
foreach ($config_input['format_commands'] as $type => $command) {
	$format_rows .= "\n<tr><td><input type=\"text\" name=\"format_names[]\" "
		. "value=\"" . htmlspecialchars($type) . "\"></td>"
		. "<td><input type=\"text\" name=\"format_commands[]\" "
		. "value=\"" . htmlspecialchars($command) . "\"></td></tr>";
}
$tags = ['format_rows' => $format_rows];
foreach ($config_input as $key => $value) {
	if ($key !== 'format_commands') {
		$tags[$key] = htmlspecialchars($value);
	}
}
$tags['message'] = $message;

Template::displayTemplate('edit_config', $tags);



function construct_format_cmd_array($format_names, $format_cmds) {
	if (!$format_names || !$format_cmds || count($format_names) !== count($format_cmds)) {
		return false;
	}
	$result = [];
	reset($format_cmds);
	foreach ($format_names as $name) {
		if (empty($name) && empty(current($format_cmds))) {
			continue;
		} else if (!preg_match('/^\\w+$/', $name) || isset($result[$name])) {
			return false;
		}
		$result[$name] = current($format_cmds);
		next($format_cmds);
	}
	return $result;
}