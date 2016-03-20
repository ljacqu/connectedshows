<?php
error_reporting(E_ALL);
require './gen/config.php';
require './inc/functions.php';
require './inc/Template.php';

$display_reset_button = false;
$show_input = isset($_POST['id']) ? $_POST['id'] : '';
$result = '';
$error = '';

do {
  if (empty($show_input)) {
    break;
  }

  // Accept full URLs, tt1266020 or 1266020 as options
  $show_input_id = get_imdb_id($show_input);
  if ($show_input_id === false) {
    $error .= '<br>Did not recognize a valid URL/ID.';
    break;
  }
  $url = get_imdb_cast_url($show_input_id);

  // Get webpage
  $webpage = get_web_page($url);
  if ($webpage['errno'] != 0 || $webpage['http_code'] != 200) {
    $error .= "<br>Error fetching page! {$webpage['errno']} : {$webpage['errmsg']}";
    break;
  }

  // Trim webpage down to table
  $cast_html = explode('</tr>', get_cast_table($webpage['content']));

  // Get series title & id
  $title_preg = '~<h3 itemprop="name">\s+'
    . '<a href="/title/tt([0-9]+)/\?ref_=ttfc_fc_tt"\s+itemprop=\'url\'>'
    . '(.*?)</a>~';
  if (preg_match($title_preg, $webpage['content'], $matches)) {
    $show_title = $matches[2];
    $show_id = $matches[1];
    if ($show_id != $show_input_id) {
      $error .= '<br>Show ID did not match from URL! Aborting.';
      break;
    }
  } else {
    $error .= '<br>Could not extract ID/name';
    break;
  }

  // Extract info from HTML into array
  $name_preg = '~<span class="itemprop" itemprop="name">(.*?)</span>~';
  $id_preg = '~<a href="/name/nm([0-9]+)/\?ref_=ttfc_fc_cl_i[0-9]+"~';
  $entry_preg = '~<div>\s+(.*?)\s+\(([0-9]+) episode(s)?~';
  $entry = [];
  foreach ($cast_html as $cast) {
    // Regex doesn't take kindly to new lines, so we replace it with a space
    // We replace &nbsp; with a space because it doesn't always appear before
    // an actor's character... This helps us keep $entry_preg simple
    $cast = str_replace(["\n", '&nbsp;'], ' ', $cast);
    if (preg_match($name_preg, $cast, $matches)) {
      $name = $matches[1];
      preg_match($id_preg, $cast, $matches);
      $id = $matches[1];
      preg_match($entry_preg, $cast, $matches);
      $role = strip_tags(fix_multiple_spaces($matches[1]));
      $episodes = $matches[2];

      $entry[] = [$id, $name, $role, $episodes];
    } else {
      $result .= '<br>Skipped HTML line: ' . htmlspecialchars($cast);
    }
  }

  // Check if show exists
  require './inc/DatabaseHandler.php';
  $dbh = new DatabaseHandler($config);
  if ($dbh->showExists($show_id)) {
    if (isset($_POST['reset'])) {
      $dbh->deleteShow($show_id);
    } else {
      $error .= '<br>Show already exists! Did not save.';
      $display_reset_button = true;
      break;
    }
  }

  // Save show & output info
  $dbh->saveShowInfo($show_title, $show_id, $entry);
  $result .= "<br><br>Saved show info for $show_title ($show_id)";
  $result .= '<br>Actors: ' . count($entry);
} while (0);

$tags = [
  'result' => $result,
  'form_error' => $error,
  'form_input' => htmlspecialchars($show_input),
  'form_reset_button' => ($display_reset_button ? ' <input type="submit" name="reset" value="Reset" class="submit">' : '')
];
Template::displayTemplate('inc/save_show_data/save_show_data.html', $tags);



// --------------
// Functions
// --------------

/**
 * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
 * array containing the HTTP server response header fields and content.
 */
function get_web_page($url) {
  $user_agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0';
  $options = array(
    CURLOPT_CUSTOMREQUEST => "GET", //set request type post or get
    CURLOPT_POST => false, //set to GET
    CURLOPT_USERAGENT => $user_agent, //set user agent
    CURLOPT_COOKIEFILE => "cookie.txt", //set cookie file
    CURLOPT_COOKIEJAR => "cookie.txt", //set cookie jar
    CURLOPT_RETURNTRANSFER => true, // return web page
    CURLOPT_HEADER => false, // don't return headers
    CURLOPT_FOLLOWLOCATION => true, // follow redirects
    CURLOPT_ENCODING => "", // handle all encodings
    CURLOPT_AUTOREFERER => true, // set referer on redirect
    CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
    CURLOPT_TIMEOUT => 120, // timeout on response
    CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
  );

  $ch = curl_init($url);
  curl_setopt_array($ch, $options);
  $content = curl_exec($ch);
  $err = curl_errno($ch);
  $errmsg = curl_error($ch);
  $header = curl_getinfo($ch);
  curl_close($ch);

  $header['errno'] = $err;
  $header['errmsg'] = $errmsg;
  $header['content'] = $content;
  return $header;
}

function get_cast_table($html) {
  $trimmed_html = substr($html, strpos($html, '<table class="cast_list">') + 26);
  return substr($trimmed_html, 0, strpos($trimmed_html, '</table>'));
}

function get_imdb_id($url) {
  // http://www.imdb.com/title/tt3155320/?ref_=rvi_tt
  // http://www.imdb.com/title/tt3155320/fullcredits?ref_=tt_ov_st_sm
  $movie_pattern = '~^(http://)?(www\.)?imdb.com/title/tt(?<id>[0-9]+)'
    . '(/[a-z0-9=&_\?-]*)?~';

  if (preg_match($movie_pattern, $url, $matches)) {
    return $matches['id'];
  } else if (preg_match('/^tt([0-9]+)$/', $url, $matches)) {
    return $matches[1];
  } else if (preg_match('/^([0-9]+)$/', $url)) {
    return $url;
  } else {
    return false;
  }
}

function get_imdb_cast_url($id) {
  return "http://www.imdb.com/title/tt{$id}/fullcredits";
}
