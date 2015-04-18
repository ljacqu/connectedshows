<?php

class DotWriter {
	/** @var string */
	private $filename;
	/** @var string[] */
	private $showToId;
	/**
	 * Function which determines the width of the edge.
	 * float penwidthFn(int $commonActors)
	 * @var callable
	 */
	private $penwidthFn;
	/**
	 * Function to determine what color an edge should have.
	 * string colorFn(float $penwidth, int $showid1, int $showid2)
	 * @var callable
	 */
	private $colorFn;
	/** @var DatabaseHandler */
	private $dbh;
	
	function __construct($filename, callable $penwidthFn, callable $colorFn, DatabaseHandler $dbh) {
		$this->filename = $filename;
		$this->penwidthFn = $penwidthFn;
		$this->colorFn = $colorFn;
		$this->dbh = $dbh;
	}
	
	function createFile(Traversable $rows, $rand=false) {
		$this->showToId = [];
		$contents = "";
		foreach ($rows as $connection) {
			$contents .= "\n\t" . $this->createEntry(
					$connection['show1'],
					$connection['show2'],
					$connection['common_actors']);
		}
		$contents = $this->createShowRegister($rand) . "\n" . $contents;
		$contents = "graph {\n node [target=\"_top\"];\n edge [target=\"_top\"];\n" 
			. $contents . "\n}";
		$this->saveToFile($contents);
	}
	
	/**
	 * Generates the Dot line for one show connection
	 * @param array $show1 The ID of the first show
	 * @param array $show2 The ID of the second show
	 * @param int $commonActors Number of actors the shows share
	 * @return string The generated Dot line
	 */
	private function createEntry($show1, $show2, $commonActors) {
		$penwidthFn = $this->penwidthFn;
		$colorFn    = $this->colorFn;
		$showId = [$this->showToId($show1), $this->showToId($show2)];
		$penwidth = $penwidthFn($commonActors);
		$href = "../connection.php?$show1-$show2";
		return "{$showId[0]} -- {$showId[1]} [penwidth=$penwidth," 
			. "color=\"" . $colorFn($penwidth, $show1, $show2) . "\","
			. "href=\"$href\"]";
	}
	
	private function saveToFile($contents) {
		$fh = fopen($this->filename, 'w');
		if ($fh === false) {
			throw new Exception('Error! Could not open file!');
		}
		fwrite($fh, $contents);
		fclose($fh);
	}
	
	private function createShowRegister($rand) {
		$output = '';
		if ($rand) $this->showToId = $this->shuffleAssoc($this->showToId);
		foreach ($this->showToId as $key => $name) {
			$href = "../show.php?" . substr($key, 1);
			$output .= "\n\t{$key} [label=\"{$name}\",href=\"$href\"];";
		}
		return $output;
	}
	
	/**
	 * Register the show as node in the graph; return an ID for the show
	 *  that is valid for the DOT format (may not start with digit).
	 * @param string[] $show The show ID
	 * @return string Dot-conform ID
	 */
	private function showToId($show) {
		$key = 'm' . $show;
		if (!isset($this->showToId[$key])) {
			$this->showToId[$key] = $this->dbh->showTitle($show);
		}
		return $key;
	}
	
	private function shuffleAssoc($list) {
		$keys = array_keys($list); 
		shuffle($keys); 
		$random = array();
		foreach ($keys as $key) { 
			$random[$key] = $list[$key]; 
		}
		return $random; 
	} 
}
