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
	
	function __construct($filename, $penwidthFn, $colorFn) {
		$this->filename = $filename;
		$this->penwidthFn = $penwidthFn;
		$this->colorFn = $colorFn;
	}
	
	function createFile(Traversable $rows, $rand=false) {
		$this->showToId = [];
		$contents = "";
		foreach ($rows as $connection) {
			$contents .= "\n\t" . $this->createEntry(
					[$connection['showid1'], $connection['show1']],
					[$connection['showid2'], $connection['show2']],
					$connection['common_actors']);
		}
		$contents = $this->createShowRegister($rand) . "\n" . $contents;
		$contents = "graph {" . $contents . "\n}";
		$this->saveToFile($contents);
	}
	
	/**
	 * Generates the Dot line for one show connection
	 * @param array $show1 [0 => show_id, 1 => show_name]
	 * @param array $show2 [0 => show_id, 1 => show_name]
	 * @param int $commonActors Number of actors the shows share
	 * @return string The generated Dot line
	 */
	private function createEntry(array $show1, array $show2, $commonActors) {
		$penwidthFn = $this->penwidthFn;
		$colorFn    = $this->colorFn;
		$showId = [$this->showToId($show1), $this->showToId($show2)];
		$penwidth = $penwidthFn($commonActors);
		return "{$showId[0]} -- {$showId[1]} [penwidth=$penwidth," 
			. "color=\"" . $colorFn($penwidth, $show1[0], $show2[0]) . "\"]";
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
			$output .= "\n\t{$key} [label=\"{$name}\"];";
		}
		return $output;
	}
	
	/**
	 * Register the show as node in the graph; return an ID for the show
	 *  that is valid for the DOT format (may not start with digit).
	 * @param string[] $show [0=>id, 1=>name]
	 * @return string Dot-conform ID
	 */
	private function showToId(array $show) {
		$key = 'm' . $show[0];
		if (!isset($this->showToId[$key])) {
			$this->showToId[$key] = $show[1]; // TODO -----------------------------------------
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
