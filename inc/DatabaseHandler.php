<?php
class DatabaseHandler {
	
	const DB_USER = 'root';
	const DB_PASS = '';
	const DB_NAME = 'movies';
	
	/** @var PDO */
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
	/** @var PDOStatement */
	private $maxEpisodeQuery;
	
	function __construct() {
		$dsn = 'mysql:dbname='.self::DB_NAME.';host=localhost';
		$this->dbh = new PDO($dsn, self::DB_USER, self::DB_PASS);
		$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->prepareQueries();
	}
	
	function getDbh() {
		return $this->dbh;
	}
	
	function saveShowInfo($title, $show_id, array $cast) {
		$result = $this->registerShow($show_id, $title);
		if (!$result) {
			throw new Exception('Could not save show! (See errors.)');
		}
		foreach ($cast as $actor) {
			if (!$this->actorExists($actor[0])) {
				$this->registerActor($actor[0], $actor[1]);
			}
			$this->registerRole($actor[0], $show_id, $actor[2], $actor[3]);
		}
	}
	
	function getAllShows() {
		return $this->dbh->query(
		   'SELECT `id`, `title`
			FROM `shows`
			ORDER BY `title`');
	}
	
	function showExists($id) {
		$this->showExistsQuery->execute([$id]);
		return ($this->showExistsQuery->rowCount() > 0);
	}
	
	function getMaxEpisode($showId) {
		$this->maxEpisodeQuery->execute([$showId]);
		if ($this->maxEpisodeQuery->rowCount() === 1) {
			return $this->maxEpisodeQuery->fetch(PDO::FETCH_NUM);
		} else if ($this->showExists($showId)) {
			$maxQuery = $this->dbh->query('SELECT MAX(`episodes`) FROM `played_in`
				WHERE `show_id` = \''.$showId.'\';');
			$maxValue = $maxQuery->fetch(PDO::FETCH_NUM);
			
			$insertMax = $this->dbh->exec('INSERT INTO `max_episodes` (`show_id`, `episodes`)
					VALUES('.$showId.', '.$maxValue[0].')');
			if ($insertMax != 1) {
				throw new Exception('Failed inserting max episodes for show ' . $showId);
			} else {
				return $maxValue;
			}
		} else {
			throw new Exception('Unknown show ' . $showId);
		}
	}
	
	function actorExists($id) {
		$this->actorExistsQuery->execute([$id]);
		return ($this->actorExistsQuery->rowCount() > 0);
	}
	
	function deleteShow($id) {
		$del_show = 'DELETE FROM `shows`
			WHERE `id` = ?';
		$del_show_query = $this->dbh->prepare($del_show);
		$del_show_query->execute([$id]);
		
		$del_roles = 'DELETE FROM `played_in`
			WHERE `show_id` = ?';
		$del_roles_query = $this->dbh->prepare($del_roles);
		$del_roles_query->execute([$id]);
	}
	
	private function registerActor($id, $name) {
		$this->registerActorQuery->execute([$id, $name]);
	}
	
	private function registerShow($id, $title) {
		return $this->registerShowQuery->execute([$id, $title]);
	}
	
	private function registerRole($actor_id, $show_id, $role, $episodes) {
		$this->registerRoleQuery->execute([$actor_id, $show_id, $role, $episodes]);
	}
	
	private function prepareQueries() {
		$registerActor = 'INSERT INTO `actors` (`id`, `name`)
			VALUES (?, ?)';
		$this->registerActorQuery = $this->dbh->prepare($registerActor);
		
		$registerShow = 'INSERT INTO `shows` (`id`, `title`)
			VALUES (?, ?)';
		$this->registerShowQuery = $this->dbh->prepare($registerShow);
		
		$registerRole = 'INSERT INTO `played_in` (`actor_id`, `show_id`, `role`, `episodes`)
			VALUES (?, ?, ?, ?)';
		$this->registerRoleQuery = $this->dbh->prepare($registerRole);
		
		$actorExists = 'SELECT * FROM `actors`
			WHERE `id` = ?';
		$this->actorExistsQuery = $this->dbh->prepare($actorExists);
		
		$showExists = 'SELECT * FROM `shows`
			WHERE `id` = ?';
		$this->showExistsQuery = $this->dbh->prepare($showExists);
		
		$maxEpisode = 'SELECT `episodes` FROM `max_episodes`
			WHERE `show_id` = ?';
		$this->maxEpisodeQuery = $this->dbh->prepare($maxEpisode);
	}
}
