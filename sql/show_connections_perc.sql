/*
Tag: {list}
Constraint: Valid SQL code. For instance:
 (`p1`.`episodes`/`m1`.`episodes`) > 0.3
 AND  (`p2`.`episodes`/`m2`.`episodes`) > 0.3
 */

SELECT `p1`.`actor_id` AS `actor`,
 `p1`.`show_id`  AS `show1`,
 `p2`.`show_id`  AS `show2`,
 `p1`.`episodes`/`m1`.`episodes` AS `rate1`,
 `p2`.`episodes`/`m2`.`episodes` AS `rate2`
FROM `played_in` `p1`
INNER JOIN `played_in` `p2`
 ON `p1`.`actor_id` = `p2`.`actor_id`
  AND `p1`.`show_id` <> `p2`.`show_id`
INNER JOIN `max_episodes` `m1`
 ON `p1`.`show_id` = `m1`.`show_id`
INNER JOIN `max_episodes` `m2`
 ON `p2`.`show_id` = `m2`.`show_id`
WHERE `p1`.`show_id` IN {list}
  AND `p2`.`show_id` IN {list}
  AND ({constraint})
