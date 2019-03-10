/* Finds the actors which have played in all of the given shows
 * Tags:
 *  {total_shows}: Total number of shows
 *  {show_list}: List of the show IDs to consider
 */

SELECT name, actor_id, show_id, role, episodes
FROM played_in
LEFT JOIN actors ON actors.id = actor_id
WHERE actor_id IN (
    SELECT actor_id 
    FROM played_in
    WHERE show_id IN ({show_list})
    GROUP BY actor_id
    HAVING COUNT(1) = {total_shows}
)
AND show_id IN ({show_list})