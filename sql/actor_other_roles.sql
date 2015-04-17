# show actors sorted by roles in other shows
# var = :show

SELECT `name`, COUNT(DISTINCT `show_id`)-1 AS `other_roles`
FROM (
    SELECT `actors`.`name`, `played_in`.`show_id`
    FROM `played_in`
    INNER JOIN `actors`
    ON `actors`.`id` = `played_in`.`actor_id`
    WHERE `actor_id` IN (
        SELECT `actor_id`
        FROM `played_in`
        WHERE `show_id` = :show
    )
    AND `played_in`.`show_id` <> :show
) AS `p`
GROUP BY `name`
ORDER BY `other_roles` DESC
