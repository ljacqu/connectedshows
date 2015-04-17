# Shows actor name & show title of actors with multiple roles #

SELECT `actors`.`name`, `shows`.`title`, `played_in`.`role`, `played_in`.`episodes`
FROM `played_in`
INNER JOIN `actors`
ON `actors`.`id` = `played_in`.`actor_id`
INNER JOIN `shows`
ON `shows`.`id` = `played_in`.`show_id`
WHERE `actors`.`id` IN (
    SELECT `actor_id`
    FROM `played_in`
    GROUP BY `actor_id`
    HAVING COUNT(*) > 1
)