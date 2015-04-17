<?php
require './inc/functions.php';
require './inc/DatabaseHandler.php';

$dbh = new DatabaseHandler;
$dbh_dbh = $dbh->getDbh();

$q = $dbh_dbh->query('
	SELECT distinct `show_id`, `shows`.`title`
	FROM `played_in`
	INNER JOIN `shows`
	ON `shows`.`id` = `show_id`
	WHERE `episodes` = 0
	ORDER BY `title` DESC
');

foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $show) {
	echo <<<HTML
<form action="save_show_data.php" method="post">
  <input type="hidden" name="id" value="{$show['show_id']}">
  <input type="submit" name="reset" value="{$show['title']}" />
</form>
  <br>

HTML;

}