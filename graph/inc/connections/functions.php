<?php

function make_shows_dropdown(array $show_data, array $checked_shows, array $shows_with_connections) {
	$output = '';
	$gray_out_if_no_connection = !empty($checked_shows);

	foreach ($show_data as $show) {
		$checked = in_array($show['id'], $checked_shows) ? ' checked="checked"' : '';
		$actors = $shows_with_connections[$show['id']] ?? null;
		if ($actors) {
      $output .= "\n<br><input type=\"checkbox\" name=\"shows[]\" "
        . "value=\"{$show['id']}\" id=\"s{$show['id']}\"$checked>"
        . " <label for=\"s{$show['id']}\">{$show['title']} ($actors)</label>";
    } else {
		  $style = ($checked || !$gray_out_if_no_connection) ? '' : 'style="color: #666"';
      $output .= "\n<br><input type=\"checkbox\" name=\"shows[]\" "
        . "value=\"{$show['id']}\" id=\"s{$show['id']}\"$checked>"
        . " <label for=\"s{$show['id']}\" $style>{$show['title']}</label>";
    }
	}
	return $output;
}
