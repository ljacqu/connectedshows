<?php

class ActorOverviewController {

  /** @var int minimum episode threshold for a role to be considered. */
  private $minEpisodes;
  /** @var PDO connection to the database. */
  private $dbh;

  function __construct($minEpisodes, DatabaseHandler $dbh) {
    $this->dbh = $dbh->getDbh();
    $this->minEpisodes = $minEpisodes;
  }

  function run() {
    $sqlData = $this->dbh->query(
      str_replace(':min_episodes', $this->minEpisodes,
        file_get_contents('./inc/actors/actors_by_role_with_minimum.sql')));
    $actors = $sqlData->fetchAll(PDO::FETCH_ASSOC);

    $tags = [
      'no_actors_found' => empty($actors),
      'actors' => $actors,
      'episode_minimum' => $this->minEpisodes
    ];
    Template::displayTemplate('./inc/actors/actor_overview.html', $tags);
  }

}