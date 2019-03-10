<?php

/**
 * Manager for roles returned by a database query. Groups the roles by actors and
 * allows to sort the actors subsequently by their relevance.
 */
class ShowConnectionsManager {

  /**
   * The relevance of each actor is computed by the number of episodes he has appeared in
   * each show. The relevance of each actor is computed as follows:
   * sum(min(MAX_VALUE_PER_SHOW, n^RELEVANCE_EXPONENT))
   * where n is the number of episodes the actor appeared in.
   */
  const RELEVANCE_EXPONENT = 0.25;
  const MAX_VALUE_PER_SHOW = 3;

  /** @var Actor[] */
  private $actors = [];
  /** @var float[] */
  private $actorRelevance = [];
  /** @var string[] the ids of the selected shows, in the order to return the roles in. */
  private $selectedShows;

  /**
   * Constructor.
   *
   * @param $selectedShows string[] the ids of the selected shows, in the order to
   * return each actor's role in
   */
  function __construct($selectedShows) {
    $this->selectedShows = $selectedShows;
  }

  /**
   * Saves a role.
   *
   * @param $actor_id string the id of the actor
   * @param $actor_name string the actor's name
   * @param $show_id string the id of the show
   * @param $role string the actor's role in the show
   * @param $episodes int number of episodes the actor appeared in
   */
  function addRole($actor_id, $actor_name, $show_id, $role, $episodes) {
    if (!isset($this->actors[$actor_id])) {
      $this->actors[$actor_id] = new Actor($actor_id, $actor_name);
    }
    $this->actors[$actor_id]->addRole($show_id, $role, $episodes);
  }

  function getActorTags() {
    $this->sortActorsByRelevance();
    return array_map(function (Actor $actor) {
      return $actor->getActorTags($this->selectedShows);
    }, $this->actors);
  }

  /**
   * Sorts the actors by their relevance for the selected shows.
   */
  private function sortActorsByRelevance() {
    usort($this->actors, function($a, $b) {
      $aRelevance = $this->getRelevance($a);
      $bRelevance = $this->getRelevance($b);
      return $bRelevance <=> $aRelevance;
    });
  }

  /**
   * Computes the relevance of an actor.
   *
   * @param $actor Actor the actor
   * @return float the relevance
   */
  private function getRelevance(Actor $actor) {
    if (isset($this->actorRelevance[$actor->getId()])) {
      return $this->actorRelevance[$actor->getId()];
    }

    $sum = 0;
    foreach ($actor->getRoles() as $role) {
      $index = pow($role->episodes, self::RELEVANCE_EXPONENT);
      $sum += min($index, self::MAX_VALUE_PER_SHOW);
    }
    $this->actorRelevance[$actor->getId()] = $sum;
    return $sum;
  }

}

class Actor {

  /** @var string */
  private $id;
  /** @var string the actor's name */
  private $name;
  /** @var Role[] */
  private $roles = [];

  function __construct($id, $name) {
    $this->id = $id;
    $this->name = $name;
  }

  function addRole($show_id, $role, $episodes) {
    $this->roles[] = new Role($show_id, $role, $episodes);
  }

  function getId() {
    return $this->id;
  }

  function getRoles() {
    return $this->roles;
  }

  function getActorTags($showOrder) {
    return [
      'actor_id' => $this->id,
      'actor_name' => $this->name,
      'actor_roles' => $this->getActorRoleTags($showOrder),
      'grade' => $this->computeGrade()
    ];
  }

  private function getActorRoleTags($showOrder) {
    $this->sortRoles($showOrder);
    $roles = [];
    foreach ($this->roles as $role) {
      $roles[] = [
        'actor_role_name' => $role->role,
        'actor_role_episodes' => $role->episodes
      ];
    }
    return $roles;
  }

  private function sortRoles($showOrder) {
    usort($this->roles, function($a, $b) use ($showOrder) {
      $aKey = array_search($a->show, $showOrder);
      $bKey = array_search($b->show, $showOrder);
      return $aKey <=> $bKey;
    });
  }

  private function computeGrade() {
    $max = array_reduce($this->roles, function ($carry, $role) {
      return max($carry, $role->episodes);
    }, 0);
    $min = array_reduce($this->roles, function ($carry, $role) {
      return min($carry, $role->episodes);
    }, PHP_INT_MAX);
    if ($min >= 5) {
      return 1;
    } else if ($min >= 2) {
      return 2;
    } else if ($max > 1) {
      return 3;
    }
    return 4;
  }
}

class Role {

  public $show;
  public $role;
  public $episodes;

  function __construct($show, $role, $episodes) {
    $this->show = $show;
    $this->role = $role;
    $this->episodes = $episodes;
  }

  function __set($name, $value) {
    // Role fields are set at instantiation
    throw new Exception('Cannot set fields of Role after instantiation');
  }

}