<?php
error_reporting(E_ALL);
require 'config.php';
require './inc/DatabaseHandler.php';
require './inc/Template.php';

$dbh = new DatabaseHandler;
$all_shows = [];
foreach ($dbh->getAllShows() as $show) {
	$all_shows[$show['id']] = $show['title'];
}
$input_shows = [];
$message = '';
$error = '';

do {
	if (empty($_SERVER['QUERY_STRING'])) {
		break;
	}
	$raw_shows = explode('-', $_SERVER['QUERY_STRING']);
	foreach ($raw_shows as $show) {
		if (isset($all_shows[$show])) $input_shows[] = $show;
	}
	$total_shows = count($input_shows);
	if ($total_shows < 2) {
		$error = 'Need at least two valid shows!';
		break;
	}
	
	$show_filter = "`show_id` = '" . implode("' OR `show_id` = '", $input_shows) . "'";
	$sql = str_replace(['{show_filter}', '{total_shows}'],
		[$show_filter, $total_shows],
		file_get_contents('./sql/find_actors.sql'));
	$sql_data = $dbh->getDbh()->query($sql);
	
	Actor::$dbh = $dbh;
	foreach ($sql_data as $entry) {
		Actor::addRole($entry['actor_id'], $entry['show_id'], $entry['role'], $entry['episodes']);
	}
	Actor::sortActors();
	
	$message = '<table class="roles">';
	foreach (Actor::$register as $actor) {
		$message .= $actor->getTableEntry();
	}
	$message .= "\n</table>";
	
} while(0);

Template::displayTemplate('connection', ['message' => $message, 'error' => $error]);




class Actor {
	/** @var DatabaseHandler */
	public static $dbh;
	/** @var Actor[] */
	public static $register = [];

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
			if (     $a->getRelevanceIndex() < $b->getRelevanceIndex()) return 1;
			else if ($a->getRelevanceIndex() > $b->getRelevanceIndex()) return -1;
			else return 0;
		});
	}
	
	function __construct($id) {
		$this->roles = [];
		$this->id = $id;
	}
	function getTableEntry() {
		$row = "\n<tr><td><a href=\"actor.php?{$this->id}\">"
			. $this->getName() . "</a></td>";
		foreach ($this->roles as $role) {
			$row .= "<td>{$role->role}</td><td>{$role->episodes}</td>";
		}
		return $row . "</tr>";
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
				$index = pow($role->episodes, 1/3);
				$sum += ($index > 10) ? 10 : $index;
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