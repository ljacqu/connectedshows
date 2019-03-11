<?php

class SqlHelper {

  private function __construct() { }

  static function connSqlQuery(ConnectionParameters $params) {
    $sql = self::connSqlTemplate($params);
    $constraint = $params->getUnit() === ConnectionParameters::UNIT_PERCENTAGE
        ? self::connPercentualConstraint($params)
        : self::connAbsoluteConstraint($params);
    $show_list = '(' . implode(',', $params->getShows()) . ')';
    return str_replace('{list}', $show_list,
      str_replace('{constraint}', $constraint, $sql));
  }

  private static function connAbsoluteConstraint(ConnectionParameters $params) {
    $threshold = $params->getThreshold();
    if ($params->getThresholdType() === ConnectionParameters::TYPE_SUM) {
      return "q.episodes + p.episodes >= $threshold";
    } else {
      $sqlOp = $params->getThresholdType() === ConnectionParameters::TYPE_MIN ? 'AND' : 'OR';
      return "q.episodes >= $threshold $sqlOp p.episodes >= $threshold";
    }
  }

  private static function connPercentualConstraint(ConnectionParameters $params) {
    $rate1 = '(p1.episodes / s1.episodes) * 100';
    $rate2 = '(p2.episodes / s2.episodes) * 100';
    $threshold = $params->getThreshold();
    if ($params->getThresholdType() === ConnectionParameters::TYPE_SUM) {
      return "$rate1 + $rate2 >= $threshold";
    } else {
      $sqlOp = $params->getThresholdType() === ConnectionParameters::TYPE_MIN ? 'AND' : 'OR';
      return "$rate1 >= $threshold $sqlOp $rate2 >= $threshold";
    }
  }

  private static function connSqlTemplate(ConnectionParameters $params) {
    $file = './sql/show_connections'
      . ($params->getLimitToSelection() ? '_limited' : '')
      . ($params->getUnit() === ConnectionParameters::UNIT_PERCENTAGE ? '_perc' : '')
      . '.sql';
    return file_get_contents($file);
  }

}