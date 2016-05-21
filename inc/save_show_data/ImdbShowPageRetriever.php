<?php

/**
 * Checks the user input show ID/URL and retrieves the contents of IMDb's cast page.
 */
class ImdbShowPageRetriever {

  static function retrieveShowIdFromUrl($input) {
    // http://www.imdb.com/title/tt3155320/?ref_=rvi_tt
    // http://www.imdb.com/title/tt3155320/fullcredits?ref_=tt_ov_st_sm
    $urlPattern = '~^(http://)?(www\\.)?imdb\\.com/title/tt(?<id>[0-9]+)'
      . '(/[a-z0-9=&_\\?-]*)?~';

    if (preg_match($urlPattern, $input, $matches)) {
      return $matches['id'];
    } else if (preg_match('/^tt([0-9]+)$/', $input, $matches)) {
      return $matches[1];
    } else if (preg_match('/^([0-9]+)$/', $input)) {
      return $input;
    } else {
      throw new Exception('Did not recognize a valid URL or ID');
    }
  }

  static function loadCastPageForId($showId) {
    $url = self::buildCastUrl($showId);
    $response = self::loadWebPage($url);
    if ($response['errno'] !== 0 || $response['http_code'] !== 200) {
      throw new Exception('Error fetching page! ' . $response['errno'] . ' : ' . $response['errmsg']);
    }
    return $response['content'];
  }

  /**
   * Get a web file (HTML, XHTML, XML, image, etc.) from a URL.  Return an
   * array containing the HTTP server response header fields and content.
   */
  private static function loadWebPage($url) {
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

  private static function buildCastUrl($id) {
    return "http://www.imdb.com/title/tt{$id}/fullcredits";
  }

}
