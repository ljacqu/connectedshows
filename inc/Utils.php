<?php

/**
 * Common utilities.
 */
final class Utils {

  private function __construct() {
  }

  /**
   * Returns a value from an input source and ensures it is scalar.
   *
   * @param $input int the input code corresponding to PHP's filter_input() (e.g. INPUT_POST)
   * @param $index string the name of the value to retrieve
   * @param $default string the default value
   * @return string the trimmed value or empty string upon failure
   */
  public static function getScalarInput($input, $index, $default) {
    $value = filter_input($input, $index, FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR) ?: $default;
    return trim($value);
  }

  /**
   * Returns a value from an input source and ensures it is an array.
   *
   * @param $input int the input code corresponding to PHP's filter_input() (e.g. INPUT_POST)
   * @param $index string the name of the value to retrieve
   * @param $default array the default value
   * @return array the value or empty string upon failure
   */
  public static function getArrayInput($input, $index, $default) {
    return filter_input($input, $index, FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY) ?: $default;
  }

}