<?php


function make_shows_dropdown(Traversable $show_data, array $checked_shows=[]) {
	$output = '';
	
	foreach ($show_data as $show) {
		$checked = in_array($show['id'], $checked_shows) ? ' checked="checked"' : '';
		$output .= "\r<br><input type=\"checkbox\" name=\"shows[]\" "
			. "value=\"{$show['id']}\" id=\"s{$show['id']}\"$checked>"
			. " <label for=\"s{$show['id']}\">{$show['title']}</label>";
	}
	return $output;
}

function fix_multiple_spaces($text) {
	return preg_replace('/(\s+)/', ' ', $text);
}