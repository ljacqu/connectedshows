/* Returns data about the roles an actor has had.
 * Tags:
 *  actor_id: ID of the actor
 */

SELECT title, show_id, role, played_in.episodes
FROM played_in
LEFT JOIN shows
  ON shows.id = played_in.show_id
WHERE actor_id = :actor_id
ORDER BY episodes DESC
