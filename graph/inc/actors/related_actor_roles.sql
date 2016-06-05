/*
 * Returns the roles the actors have had in which an actor has played in.
 * Tags:
 *  actor_list: List of actor ids to consider
 *  main_actor: id of the main actor (to infer the shows to consider)
 */

select actor_id, show_id, role, episodes
from played_in
where actor_id in (:actor_list)
and show_id in (
  select show_id
  from played_in
  where actor_id = :main_actor
)