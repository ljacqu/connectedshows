<?php

class DotWriter {
	/** @var string */
	private $filename;
	/** @var string[] */
	private $showToId;
	
	function __construct($filename) {
		$this->filename = $filename;
		$this->showToId = [];
	}
	
	function createFile(Traversable $rows, callable $penwidth, callable $color, $rand=false) {
		$contents = "";
		foreach ($rows as $connection) {
			$contents .= "\n\t" . $this->createEntry(
					$connection['show1'],
					$connection['show2'],
					$connection['common_actors'],
					$penwidth,
					$color);
		}
		$contents = $this->createShowRegister($rand) . "\n" . $contents;
		$contents = "graph {" . $contents . "\n}";
		$this->saveToFile($contents);
	}
	
	private function createEntry($show1, $show2, $commonActors, callable $penwidth, callable $color) {
		$show = [$this->showToId($show1), $this->showToId($show2)];
		return $show[0] . " -- " . $show[1] 
							. " [penwidth=" . $penwidth($commonActors) . ",color=\"".$color()."\"];";
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
	
	private function showToId($show) {
		$key = $this->dotFriendlyHash($show);
		if (!isset($this->showToId[$key])) {
			$this->showToId[$key] = $show;
		}
		return $key;
	}

	private function dotFriendlyHash($title) {
		return str_replace(
				range(0, 9),
				range('g', 'p'),
				substr(md5($title), 0, 8));
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
