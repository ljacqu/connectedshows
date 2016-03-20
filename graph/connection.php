<?php
error_reporting(E_ALL);

require '../inc/Utils.php';
require '../inc/Template.php';
require '../gen/config.php';
require '../inc/functions.php';
require '../inc/DatabaseHandler.php';

$dbh = new DatabaseHandler($config);
$all_shows = [];
foreach ($dbh->getAllShows() as $show) {
  $all_shows[$show['id']] = $show['title'];
}
$input_shows = [];
$message = '';
$error = '';
$show_roles_table = false;

do {
  if (empty($_SERVER['QUERY_STRING'])) {
    break;
  }
  $raw_shows = explode('-', $_SERVER['QUERY_STRING']);
  foreach ($raw_shows as $show) {
    if (isset($all_shows[$show])) {
      $input_shows[] = $show;
    }
  }
  $total_shows = count($input_shows);
  if ($total_shows < 2) {
    $error = 'Need at least two shows!';
    break;
  }

  $show_filter = "`show_id` = '" . implode("' OR `show_id` = '", $input_shows) . "'";
  $sql = str_replace(['{show_filter}', '{total_shows}'], [$show_filter, $total_shows], 
    file_get_contents('../sql/find_actors.sql'));
  $sql_data = $dbh->getDbh()->query($sql);

  Actor::$dbh = $dbh;
  foreach ($sql_data as $entry) {
    Actor::addRole($entry['actor_id'], $entry['show_id'], $entry['role'], $entry['episodes']);
  }
  Actor::sortActors();

  Actor::$inputShows = $input_shows;
  $actors = [];
  foreach (Actor::$register as $actor) {
    $actors[] = $actor->getActorTags();
  }
} while (0);

$selected_shows = array_map(function ($show) use ($dbh) {
  return ['selected_show_title' => $dbh->showTitle($show)];
}, $input_shows);

$form_shows = make_shows_dropdown($dbh->getAllShows(), $input_shows);
$tags = [
  'form_shows' => $form_shows,
  'actors' => $actors ?: [],
  'selected_shows' => $selected_shows,
  'message' => $message,
  'error' => $error
];
Template::displayTemplate('connection.html', $tags);

class Actor {

  /** @var DatabaseHandler */
  public static $dbh;

  /** @var Actor[] */
  public static $register = [];

  /** @var string[] */
  public static $inputShows;

  /** @var string */
  private $id;

  /** @var Role[] */
  private $roles;

  /** @var float */
  private $relevance;

  static function addRole($actor_id, $show_id, $role, $episodes) {
    if (!isset(self::$register[$actor_id])) {
      self::$register[$actor_id] = new Actor($actor_id);
    }
    self::$register[$actor_id]->roles[] = new Role($show_id, $role, $episodes);
  }

  static function sortActors() {
    usort(self::$register, function($a, $b) {
      if ($a->getRelevanceIndex() < $b->getRelevanceIndex())
        return 1;
      else if ($a->getRelevanceIndex() > $b->getRelevanceIndex())
        return -1;
      else
        return 0;
    });
  }

  function __construct($id) {
    $this->roles = [];
    $this->id = $id;
  }

  function getActorTags() {
    return [
      'actor_id' => $this->id,
      'actor_name' => $this->getName(),
      'actor_roles' => $this->getActorRoleTags()
    ];
  }

  private function getActorRoleTags() {
    $this->sortRoles();
    $roles = [];
    foreach ($this->roles as $role) {
      $roles[] = [
        'actor_role_name' => $role->role,
        'actor_role_episodes' => $role->episodes
      ];
    }
    return $roles;
  }

  function sortRoles() {
    usort($this->roles, function($a, $b) {
      $aKey = array_search($a->show, self::$inputShows);
      $bKey = array_search($b->show, self::$inputShows);
      if ($aKey < $bKey)
        return -1;
      else if ($aKey > $bKey)
        return 1;
      else
        return 0;
    });
  }

  private function getName() {
    $sql = self::$dbh->getDbh()->prepare('SELECT `name` FROM `actors`'
      . ' WHERE `id` = ?');
    $sql->execute([$this->id]);
    return $sql->fetch()[0];
  }

  private function getRelevanceIndex() {
    if (!isset($this->relevance)) {
      $sum = 0;
      foreach ($this->roles as $role) {
        $index = pow($role->episodes, 1 / 4);
        $sum += ($index > 3 ? 3 : $index);
      }
      $this->relevance = $sum;
    }
    return $this->relevance;
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

}
