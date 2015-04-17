
# Show the number of connections from a given show to other shows
# by number of common actors
# var = :show

SELECT `show`, COUNT(`actor`) AS `common_actors`
FROM (
    SELECT `actor_id` AS `actor`, `shows`.`title` AS `show`
    FROM `played_in`
    INNER JOIN `shows`
    ON `shows`.`id` = `played_in`.`show_id`
    WHERE `actor_id` IN (
        SELECT `actor_id`
	FROM  `played_in`
	WHERE `show_id` = :show
    )
    AND `played_in`.`show_id` <> :show
) AS `p`
GROUP BY `show`
ORDER BY `common_actors` DESC