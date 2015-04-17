<?php
function make_shows_dropdown($show_data, $checked_shows=null) {
	$checked_shows = (!empty($checked_shows) && is_array($checked_shows))
			? $checked_shows : [];
	$output = '';
	
	foreach ($show_data as $show) {
		$checked = in_array($show['id'], $checked_shows) ? ' checked="checked"' : '';
		$output .= "\r<br><input type=\"checkbox\" name=\"shows[]\" "
			. "value=\"{$show['id']}\" id=\"s{$show['id']}\"$checked>"
			. " <label for=\"s{$show['id']}\">{$show['title']}</label>";
	}
	return $output;
}


function all_numeric(array $arr) {
	foreach ($arr as $entry) {
		// more intuitive is_int check
		// http://php.net/manual/en/function.is-int.php#82857
		if (!ctype_digit(strval($entry))) return false;
	}
	return true;
}

function fix_multiple_spaces($text) {
	return preg_replace('/(\s+)/', ' ', $text);
}