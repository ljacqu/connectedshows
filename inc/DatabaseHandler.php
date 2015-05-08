<?php
class DatabaseHandler {
	
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
	private $showTitleQuery;
	
	function __construct(array $config) {
		if (empty($config)) throw new PDOException("Could not get config data!");
		$dsn = 'mysql:dbname='.$config['db_name'].';host=localhost';
		$this->dbh = new PDO($dsn, $config['db_user'], $config['db_pass']);
		$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->prepareQueries();
	}
	
	function getDbh() {
		return $this->dbh;
	}
	
	function saveShowInfo($title, $showId, array $actors) {
		$this->dbh->beginTransaction();
		$max = 0;
		foreach ($actors as $actor) {
			if (!$this->actorExists($actor[0])) {
				$this->registerActor($actor[0], $actor[1]);
			}
			$this->registerRole($actor[0], $showId, $actor[2], $actor[3]);
			if ($max < $actor[3]) $max = $actor[3];
		}
		$this->registerShow($showId, $title, $max);
		$this->dbh->commit();
	}
	
	function getAllShows() {
		return $this->dbh->query(
		   'SELECT `id`, `title`
			FROM `shows`
			ORDER BY `title`');
	}
	
	function getTotalShows() {
		return $this->dbh->query('SELECT COUNT(1) FROM `shows`')->fetch()[0];
	}
	
	function showExists($id) {
		$this->showTitleQuery->execute([$id]);
		return ($this->showTitleQuery->rowCount() > 0);
	}
	
	function showTitle($id) {
		$this->showTitleQuery->execute([$id]);
		return $this->showTitleQuery->fetch(PDO::FETCH_NUM)[0];
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
	
	private function registerShow($id, $title, $maxEpisodes) {
		return $this->registerShowQuery->execute([$id, $title, $maxEpisodes]);
	}
	
	private function registerRole($actor_id, $show_id, $role, $episodes) {
		$this->registerRoleQuery->execute([$actor_id, $show_id, $role, $episodes]);
	}
	
	private function prepareQueries() {
		$registerActor = 'INSERT INTO `actors` (`id`, `name`)
			VALUES (?, ?)';
		$this->registerActorQuery = $this->dbh->prepare($registerActor);
		
		$registerShow = 'INSERT INTO `shows` (`id`, `title`, `episodes`)
			VALUES (?, ?, ?)';
		$this->registerShowQuery = $this->dbh->prepare($registerShow);
		
		$registerRole = 'INSERT INTO `played_in` (`actor_id`, `show_id`, `role`, `episodes`)
			VALUES (?, ?, ?, ?)';
		$this->registerRoleQuery = $this->dbh->prepare($registerRole);
		
		$actorExists = 'SELECT 1 FROM `actors`
			WHERE `id` = ?';
		$this->actorExistsQuery = $this->dbh->prepare($actorExists);
		
		$showTitle = 'SELECT `title` FROM `shows`
			WHERE `id` = ?';
		$this->showTitleQuery = $this->dbh->prepare($showTitle);
	}
}
