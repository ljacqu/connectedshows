<?php
/**
 * Returns the saved configurations.
 */
error_reporting(E_ALL);

header('Content-Type: application/json');
require '../../gen/configs.json';
