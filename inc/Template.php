<?php

class Template {
	
	static function displayTemplate($name, array &$tags) {
		$template = self::getTemplateText($name);
		echo self::prepareTemplate($template, $tags);
	}
	
	static function getTemplateText($name) {
		if (!preg_match('/^[a-zA-Z0-9-_]+$/', $name)) {
			throw new Exception('Invalid characters in template!');
		}
		if (!file_exists("./html/{$name}.html")) {
			throw new Exception("Template $name does not exist!");
		}
		return file_get_contents("./html/{$name}.html");
	}
	
	static function prepareTemplate($template, array &$tags) {
		foreach ($tags as $name => $value) {
			$template = str_replace("{{$name}}", $value, $template);
		}
		return $template;
	}
	
	
}