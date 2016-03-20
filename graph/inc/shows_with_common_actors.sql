/* Finds the shows with the most common actors with a given show.
 * {show_id}: The ID of the show to investigate
 */

SELECT title similar_show_title,
  show_id similar_show_id,
  COUNT(actor_id) similar_show_actors
FROM played_in
INNER JOIN shows
  ON shows.id = show_id
WHERE actor_id IN (
  SELECT actor_id
  FROM `played_in`
  WHERE show_id = {show_id}
)
AND show_id <> {show_id}
GROUP BY show_id
ORDER BY similar_show_actors DESC
