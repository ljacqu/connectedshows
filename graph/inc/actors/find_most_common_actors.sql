/* Finds the actors a given actor has played together in the most shows.
 * Tags:
 *  actor_id: The actor ID
 */

SELECT name AS similar_actor_name,
  actor_id AS similar_actor_id,
  COUNT(1) AS similar_actor_count
FROM played_in
INNER JOIN actors
  ON actors.id = played_in.actor_id
WHERE show_id IN (
  SELECT show_id
  FROM played_in
  WHERE actor_id = :actor_id
)
AND actor_id <> :actor_id
GROUP BY actor_id
HAVING similar_actor_count > 1
ORDER BY similar_actor_count DESC
LIMIT 10
