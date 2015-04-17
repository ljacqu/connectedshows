# Shows all connections from a given set of shows to all other shows
# tag = {list}, e.g. (1520211, 1741256)
# tag = {ep_threshold}, e.g. 20

SELECT `shows2`.`title` AS `show1`,
       `shows1`.`title` AS `show2`,
       `actors` AS `common_actors`
FROM (
    SELECT COUNT(`p`.`actor_id`) AS `actors`,
           `q`.`show_id` AS `show1`,
           `p`.`show_id` AS `show2`
    FROM `played_in` `p`
    INNER JOIN `played_in` `q`
    ON `p`.`actor_id` = `q`.`actor_id` 
     AND `p`.`show_id` <> `q`.`show_id`
    WHERE `p`.`show_id` IN {list} /* replace */
     AND ({constraint})
    GROUP BY `show1`, `show2`
) `connections`
INNER JOIN `shows` `shows1`
ON `connections`.`show1` = `shows1`.`id`
INNER JOIN `shows` `shows2`
ON `connections`.`show2` = `shows2`.`id`
#ORDER BY `common_actors` DESC