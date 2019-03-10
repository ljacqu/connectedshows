<?php

/**
 * Controller for interacting with the database when saving / resetting a show.
 */
class SaveShowDatabaseController {

  /** @var PDO database handler. */
  private $dbh;

  /** @var PDOStatement */
  private $registerActorQuery;
  /** @var PDOStatement */
  private $registerShowQuery;
  /** @var PDOStatement */
  private $registerRoleQuery;
  /** @var PDOStatement */
  private $actorExistsQuery;
  /** @var PDOStatement */
  private $showExistsQuery;

  function __construct(PDO $dbh) {
    $this->dbh = $dbh;
    $this->prepareQueries();
  }

  function saveShowInfo($title, $showId, array $actors) {
    $max_episodes = array_reduce($actors, function ($carry, $actor) {
      return max($carry, $actor[3]);
    }, 0);

    $this->dbh->beginTransaction();
    $this->registerShow($showId, $title, $max_episodes);
    foreach ($actors as $actor) {
      if (!$this->actorExists($actor[0])) {
        $this->registerActor($actor[0], $actor[1]);
      }
      $this->registerRole($actor[0], $showId, $actor[2], $actor[3]);
    }
    $this->dbh->commit();
  }

  function showExists($id) {
    $this->showExistsQuery->execute([$id]);
    return ($this->showExistsQuery->rowCount() > 0);
  }

  function deleteShow($id) {
    $this->dbh->beginTransaction();
    $del_roles_query = $this->dbh->prepare('DELETE FROM played_in WHERE show_id = ?');
    $del_roles_query->execute([$id]);
    $del_show_query = $this->dbh->prepare('DELETE FROM shows WHERE id = ?');
    $del_show_query->execute([$id]);
    $this->dbh->commit();
  }

  private function actorExists($id) {
    $this->actorExistsQuery->execute([$id]);
    return ($this->actorExistsQuery->rowCount() > 0);
  }

  private function registerActor($id, $name) {
    $this->registerActorQuery->execute([$id, $name]);
  }

  private function registerShow($id, $title, $maxEpisodes) {
    return $this->registerShowQuery->execute([$id, $title, $maxEpisodes]);
  }

  private function registerRole($actor_id, $show_id, $role, $episodes) {
    $this->registerRoleQuery->execute([$actor_id, $show_id, $role, $episodes]);
  }

  private function prepareQueries() {
    $this->registerActorQuery = $this->dbh->prepare(
      'INSERT INTO actors (id, name) VALUES (?, ?)');
    $this->registerShowQuery = $this->dbh->prepare(
      'INSERT INTO shows (id, title, episodes, retrieval_date) VALUES (?, ?, ?, NOW())');
    $this->registerRoleQuery = $this->dbh->prepare(
      'INSERT INTO played_in (actor_id, show_id, role, episodes) VALUES (?, ?, ?, ?)');
    $this->actorExistsQuery = $this->dbh->prepare(
      'SELECT 1 FROM actors WHERE id = ?');
    $this->showExistsQuery = $this->dbh->prepare(
      'SELECT 1 FROM shows WHERE id = ?');
  }

}
