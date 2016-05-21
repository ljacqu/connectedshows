<?php
class DatabaseHandler {
  
  /** @var PDO */
  private $dbh;

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

  function getAllShows() {
    return $this->dbh->query('SELECT id, title FROM shows ORDER BY title');
  }

  function healthcheck() {
    return $this->dbh->query('SELECT 1 chk FROM shows')->fetch()[0];
  }

  function getTotalShows() {
    return $this->dbh->query('SELECT COUNT(1) FROM shows')->fetch()[0];
  }

  function showTitle($id) {
    $this->showTitleQuery->execute([$id]);
    return $this->showTitleQuery->fetch(PDO::FETCH_NUM)[0];
  }

  private function prepareQueries() {
    $showTitle = 'SELECT `title` FROM `shows`
      WHERE `id` = ?';
    $this->showTitleQuery = $this->dbh->prepare($showTitle);
  }
}
