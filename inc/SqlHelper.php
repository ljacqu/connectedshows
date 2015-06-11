<?php

class SqlHelper {
	
	private function __construct() { }
	
	static function connSqlQuery($threshold, $type, $limited, $percentual) {
		$sql = self::connSqlTemplate($limited, $percentual);
		$constraint = $percentual
				? self::connPercentualConstraint($threshold, $type)
				: self::connAbsoluteConstraint($threshold, $type);
		return str_replace('{constraint}', $constraint, $sql);
	}
	
	private static function connAbsoluteConstraint($threshold, $type) {
		if ($type['sum']) {
			return "`q`.`episodes` + `p`.`episodes` >= $threshold";
		} else {
			$sqlOp = $type['min'] ? 'AND' : 'OR';
			return "`q`.`episodes` >= $threshold $sqlOp `p`.`episodes` >= $threshold";
		}
	}
	
	private static function connPercentualConstraint($threshold, $type) {
		$rate1 = '(`p1`.`episodes`/`s1`.`episodes`)*100';
		$rate2 = '(`p2`.`episodes`/`s2`.`episodes`)*100';
		if ($type['sum']) {
			return "$rate1 + $rate2 >= $threshold";
		} else {
			$sqlOp = $type['min'] ? 'AND' : 'OR';
			return "$rate1 >= $threshold $sqlOp $rate2 >= $threshold";
		}
	}
	
	private static function connSqlTemplate($limited, $percentual) {
		$file = './sql/show_connections'
			. ($limited    ? '_limited' : '')
			. ($percentual ? '_perc'    : '')
			. '.sql';
		return file_get_contents($file);
	}
	
}