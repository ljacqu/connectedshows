<?php

class ShowOverviewController {

  /** @var DatabaseHandler */
  private $dbh;

  /** @param $dbh DataBaseHandler */
  function __construct($dbh) {
    $this->dbh = $dbh;
  }

  function run() {
    $show_data = $this->dbh->getDbh()->query(file_get_contents('./inc/shows/get_shows_overview.sql'));

    $shows = [];
    foreach ($show_data as $entry) {
      $shows[] = [
        'id' => $entry['id'],
        'title' => $entry['title'],
        'episodes' => $entry['episodes'],
        'actors' => $entry['actors']
      ];
    }

    Template::displayTemplate('./inc/shows/show_overview.html', [
      'shows' => $shows
    ]);
  }
}