/* Returns, based on a list of show IDs, which shows have a common actor.
 * Tags:
 *  {show_list}: List of the show IDs to consider
 *  {total_shows}: Total number of shows
 */

select show_id, count(1) as actors
from played_in
where actor_id in (
  select distinct actor_id
  from played_in
  where show_id in ({show_list})
  group by actor_id
  having count(1) = {total_shows}
)
and show_id not in ({show_list})
group by show_id;