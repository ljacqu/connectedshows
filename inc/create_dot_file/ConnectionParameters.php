<?php

/**
 * Parameters for which connections to include into the graph.
 */
class ConnectionParameters {

  const TYPE_MIN = 'min';
  const TYPE_MAX = 'max';
  const TYPE_SUM = 'sum';

  const UNIT_NUMBER = 'n';
  const UNIT_PERCENTAGE = 'p';

  /** @var string[] errors found while processing the input */
  private $errors = array();
  /** @var int[] the shows to include in the graph */
  private $shows = array();
  /** @var string name of the dot file to write to */
  private $file = '';
  /** @var number threshold to consider a connection */
  private $threshold = 0;
  /** @var string threshold type (min, max, sum) */
  private $thresholdType = '';
  /** @var string the unit (number of episodes, percentage) to use */
  private $unit = '';
  /** @var bool limit the connections to the selected shows */
  private $limitToSelection = true;

  function hasErrors() {
    return !empty($this->errors);
  }

  function getErrors() {
    return $this->errors;
  }

  function getShows() {
    return $this->shows;
  }

  function getFile() {
    return $this->file;
  }

  function getThreshold() {
    return $this->threshold;
  }

  function getThresholdType() {
    return $this->thresholdType;
  }

  function getUnit() {
    return $this->unit;
  }

  function getLimitToSelection() {
    return $this->limitToSelection;
  }

  function getTagCollection() {
    return [
      'shows' => $this->shows,
      'file' => htmlspecialchars($this->file),
      'threshold' => $this->threshold,
      'threshold_type' => $this->thresholdType,
      'unit' => $this->unit,
      'limit_to_selection' => $this->limitToSelection
    ];
  }

  function readFromInput() {
    if (Utils::isInputNumericArray(INPUT_POST, 'shows')) {
      $this->shows = $_POST['shows'];
    } else {
      $this->addError('Please select the shows to include.');
    }

    $this->limitToSelection = isset($_POST['limited']);
    $this->getFileFromInput();
    $this->getThresholdFromInput();
    $this->getThresholdTypeFromInput();
    $this->getUnitFromInput();
  }

  private function addError($msg) {
    $this->errors[] = $msg;
  }

  private function getFileFromInput() {
    $file = Utils::getScalarInput(INPUT_POST, 'file', null);
    if ($file) {
      if (preg_match('~^[a-z0-9_\\(\\)\\[\\]-]+(\\.dot)?$~i', $_POST['file'], $matches)) {
        $this->file = $_POST['file'] . (isset($matches[1]) ? '' : '.dot');
      } else {
        $this->addError('Please enter a valid filename.');
      }
    } else {
      $this->addError('Please enter a filename.');
    }
  }

  private function getThresholdFromInput() {
    $threshold = Utils::getScalarInput(INPUT_POST, 'threshold', null);
    if ($threshold && is_numeric($threshold)) {
      $this->threshold = $threshold;
    } else {
      $this->addError('Please enter a threshold.');
    }
  }

  private function getThresholdTypeFromInput() {
    $type = Utils::getScalarInput(INPUT_POST, 'type', false);
    if (array_search($type, [self::TYPE_MIN, self::TYPE_MAX, self::TYPE_SUM]) !== false) {
      $this->thresholdType = $type;
    } else {
      $this->addError('Please select the threshold type.');
    }
  }

  private function getUnitFromInput() {
    $unit = Utils::getScalarInput(INPUT_POST, 'unit', false);
    if (array_search($unit, [self::UNIT_NUMBER, self::UNIT_PERCENTAGE]) !== false) {
      $this->unit = $unit;
    } else {
      $this->addError('Please select the unit.');
    }
  }

}