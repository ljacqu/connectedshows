<?php

class ActorDetailController {

  /** @var string the actor id. */
  private $actorId;
  /** @var PDO connection to the database. */
  private $dbh;

  function __construct($actorId, DatabaseHandler $dbh) {
    $this->actorId = $actorId;
    $this->dbh = $dbh->getDbh();
  }

  function run() {
    // Initial tag values
    $error = '';
    $hasActorData = false;
    $roles = [];
    $similar_actors = [];
    $actorName = $this->getActorName();

    if ($actorName) {
      $sql_roles = $this->dbh->prepare(
          file_get_contents('./inc/actors/find_shows_of_actor.sql'));
      $sql_roles->execute(['actor_id' => $this->actorId]);
      $roles = $sql_roles->fetchAll(PDO::FETCH_ASSOC);

      $sql_actors = $this->dbh->prepare(
          file_get_contents('./inc/actors/find_most_common_actors.sql'));
      $sql_actors->execute(['actor_id' => $this->actorId]);
      $similar_actors = $sql_actors->fetchAll(PDO::FETCH_ASSOC);

      $hasActorData = true;
    } else {
      $error = 'Actor ID is unknown.';
    }

    $tags = [
      'error' => $error,
      'roles' => $roles,
      'has_actor_data' => $hasActorData,
      'actor_name' => $actorName,
      'actor_id' => $this->actorId,
      'similar_actors' => $similar_actors
    ];

    Template::displayTemplate('./inc/actors/actor_detail.html', $tags);
  }

  private function getActorName() {
    $query = $this->dbh->prepare('SELECT name FROM actors WHERE id = ?');
    $query->execute([$this->actorId]);
    $actorData = $query->fetch(PDO::FETCH_ASSOC);
    return $actorData ? $actorData['name'] : null;
  }

}