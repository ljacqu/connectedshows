<?php

class ShowDetailController {

  /** @var DatabaseHandler */
  private $dbh;

  /**
   * @param $dbh DatabaseHandler
   */
  function __construct($dbh) {
    $this->dbh = $dbh;
  }

  function run($show_id) {
    $show_name = '';
    $similar_shows = [];
    $significant_actors = [];
    $actors_by_other_shows = [];
    $message = '';
    $error = '';

    do {
      $get_title_query = $this->dbh->getDbh()->prepare('SELECT title FROM shows WHERE id = ?');
      $get_title_query->execute([$show_id]);
      $show_data = $get_title_query->fetch(PDO::FETCH_ASSOC);
      if (!$show_data) {
        $error = 'Unknown show ID';
        break;
      }
      $show_name = $show_data['title'];

      $similar_shows_stmt = $this->dbh->getDbh()->prepare(
        file_get_contents('./inc/shows/shows_with_common_actors.sql'));
      $similar_shows_stmt->execute(['show_id' => $show_id]);
      $similar_shows = $similar_shows_stmt->fetchAll();

      $significant_actors_stmt = $this->dbh->getDbh()->prepare(
        file_get_contents('./inc/shows/actors_by_episode_count.sql'));
      $significant_actors_stmt->execute(['show_id' => $show_id]);
      $significant_actors = $significant_actors_stmt->fetchAll();

      $actors_by_other_shows_stmt = $this->dbh->getDbh()->prepare(
        file_get_contents('./inc/shows/actors_other_roles.sql'));
      $actors_by_other_shows_stmt->execute(['show_id' => $show_id]);
      $actors_by_other_shows = $actors_by_other_shows_stmt->fetchAll();

      $message = 'There are ' . count($similar_shows) . ' shows with common actors';

    } while (0);


    $tags = [
      'error' => $error,
      'message' => $message,
      'show_id' => $show_id,
      'show_name' => htmlspecialchars($show_name),
      'similar_shows' => $similar_shows,
      'significant_actors' => $significant_actors,
      'actors_by_other_shows' => $actors_by_other_shows
    ];
    Template::displayTemplate('./inc/shows/show_detail.html', $tags);
  }
}