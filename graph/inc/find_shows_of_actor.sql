/* Returns data about the roles an actor has had.
 * Tags:
 *  {actor_id}: Id of the actor
 */

select title, show_id, role, played_in.episodes
from played_in
left join shows
  on shows.id = played_in.show_id
where actor_id = {actor_id}
order by episodes desc