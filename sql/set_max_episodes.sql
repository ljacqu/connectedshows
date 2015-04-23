UPDATE `shows`
INNER JOIN (
  SELECT `show_id`,
    MAX(`played_in`.`episodes`)
     AS `max_episodes`
  FROM `played_in`
  GROUP BY `played_in`.`show_id`
) AS `sub` ON `shows`.`id` = `sub`.`show_id`
SET `shows`.`episodes` = `max_episodes`
WHERE `episodes` <= 0