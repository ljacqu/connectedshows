<?php

class Template {

  private function __construct() {
  }

  static function displayTemplate($name, array $tags) {
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

  static function prepareTemplate($template, array $tags) {
    foreach ($tags as $name => $value) {
      $template = self::handleConditionalTag($template, $name, $value);
      $template = is_array($value)
        ? self::handleRepetitionTag($template, $name, $value)
        : str_replace("{{$name}}", $value, $template);
    }
    return $template;
  }

  private static function handleConditionalTag($template, $tagName, $value) {
    if (strpos($template, "[$tagName]") !== false) {
      $innerReplacement = $value ? '\\2' : '';
      return preg_replace("~(\\[{$tagName}](.*?)\\[/{$tagName}])~s", $innerReplacement, $template);
    }
    return $template;
  }

  private static function handleRepetitionTag($template, $tagName, array $value) {
    if (strpos($template, "[#$tagName]") !== false) {
      preg_match("~\\[#$tagName](.*?)\\[/#$tagName]~s", $template, $matches);
      $innerText = $matches[1];
      $replacements = array_map(function ($values) use ($innerText) {
        return self::prepareTemplate($innerText, $values);
      }, $value);
      return preg_replace("~(\\[#$tagName].*?\\[/#$tagName])~s", implode("\n", $replacements), $template);
    }
    return $template;
  }

}