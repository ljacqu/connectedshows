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

  /**
   * Returns whether the given input is an array whose values are all numeric.
   *
   * @param $input int the code of the input to use (e.g. INPUT_POST)
   * @param $index string the name of the value to verify
   * @return bool true if the array is all numeric, false otherwise
   */
  public static function isInputNumericArray($input, $index) {
    $value = self::getArrayInput($input, $index, null);
    if ($value === null) {
      return false;
    }

    foreach ($value as $entry) {
      // more intuitive is_int check
      // http://php.net/manual/en/function.is-int.php#82857
      if (!ctype_digit(strval($entry))) {
        return false;
      }
    }
    return true;
  }

}