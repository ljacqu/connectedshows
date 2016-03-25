/* Returns the actors with the most roles they've had, ignoring any
 * roles that don't meet the minimum number of episodes he has played in.
 * Tags:
 * - :min_episodes Minimum number of episodes for the role
 *                 to be considered
 */

select actor_id, name actor_name, count(show_id) show_count
from played_in
inner join actors
  on actors.id = actor_id
where episodes > :min_episodes
group by actor_id
order by show_count desc
limit 50