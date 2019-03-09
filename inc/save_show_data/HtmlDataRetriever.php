<?php

/**
 * Retrieves data from an IMDb cast page.
 */
class HtmlDataRetriever {

  static function getShowTitle($show_id, $html) {
    $title_regexp = '~<h3 itemprop="name">\\s+'
      . '<a href="/title/tt(\\d+)/\\?ref_=ttfc_fc_tt"\\s+itemprop=\'url\'>'
      . '(.*?)</a>~';
    if (preg_match($title_regexp, $html, $matches)) {
      $matched_show_id = $matches[1];
      if ($matched_show_id !== $show_id) {
        throw new Exception('Show ID did not match from URL! Aborting.');
      }
      return $matches[2];
    }
    throw new Exception('Could not extract ID/name');
  }

  static function extractCastEntries($html) {
    // Trim HTML down to the cast table
    $castTableHtml = explode('</tr>', self::isolateCastTableHtml($html));

    $name_preg = '~<a href="/name/nm\\d+/\\?ref_=ttfc_fc_cl_t\\d+" >(.*?)</a>~';
    $id_preg = '~<a href="/name/nm(\\d+)/\\?ref_=ttfc_fc_cl_i[0-9]+"~';
    $role_preg = '~<td class="character">\\s+(.*?)<a[^>]+>(\\d+) episodes?~';
    $spinner_row_preg = '~<tr class="(even|odd)">\\s+<td colspan="4" id="episodes[^>]+>\\s+<img src="[^>]+spinner-[^>]+>\\s+</td>\\s*$~';
    $entry = [];
    foreach ($castTableHtml as $i => $cast) {
      // Regex doesn't take kindly to new lines, so we replace them with a space
      // We also replace &nbsp; with a space because it doesn't always appear before
      // an actor's character... This helps us keep $role_preg simple
      $cast = str_replace(["\n", '&nbsp;'], ' ', $cast);
      if (preg_match($name_preg, $cast, $matches)) {
        $name = $matches[1];
        preg_match($id_preg, $cast, $matches);
        $id = $matches[1];
        preg_match($role_preg, $cast, $matches);
        $role = strip_tags(self::trimMultipleSpaces($matches[1]));
        $episodes = $matches[2];

        $entry[] = [$id, $name, $role, $episodes];
      } else if (!preg_match($spinner_row_preg, $cast)) {
        echo '<br>Debug - skipped line ' . $i . ': ' . htmlspecialchars($cast) . '<hr/>';
      }
    }
    return $entry;
  }

  private static function isolateCastTableHtml($html) {
    $trimmed_html = substr($html, strpos($html, '<table class="cast_list">') + 26);
    return substr($trimmed_html, 0, strpos($trimmed_html, '</table>'));
  }

  private static function trimMultipleSpaces($text) {
    return preg_replace('/(\\s+)/', ' ', $text);
  }

}
