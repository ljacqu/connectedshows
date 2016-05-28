-- tag = {list}, e.g. (410975, 903747, 212671)
-- tag {constraint}: Valid SQL code. For instance:
-- (p1.episodes/s1.episodes) > 0.3
-- OR  (p2.episodes/s2.episodes) > 0.3

SELECT
	p1.show_id AS show1,
	p2.show_id AS show2,
	COUNT(p1.actor_id) AS common_actors
FROM played_in p1
INNER JOIN played_in p2
 ON p1.actor_id = p2.actor_id
  AND p1.show_id < p2.show_id
INNER JOIN shows s1
 ON s1.id = p1.show_id
INNER JOIN shows s2
 ON s2.id = p2.show_id
WHERE p1.show_id IN {list}
  OR  p2.show_id IN {list}
  AND ({constraint})
GROUP BY show1, show2