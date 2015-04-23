-- Shows all connections from a given set of shows to all other shows
-- tag = {list}, e.g. (1520211, 1741256)
-- tag = {constraint}, valid SQL constraint

SELECT 
    `q`.`show_id` AS `show1`,
    `p`.`show_id` AS `show2`,
    COUNT(`p`.`actor_id`) AS `common_actors`
FROM `played_in` `p`
INNER JOIN `played_in` `q`
 ON `p`.`actor_id` = `q`.`actor_id` 
 AND `p`.`show_id` <> `q`.`show_id`
WHERE `p`.`show_id` IN {list}
 AND ({constraint})
GROUP BY `show1`, `show2`
