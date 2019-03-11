/* Returns the connections among all shows in the selection.
 *
 * {show_list}: list of shows to restrict the query to
 * {least_threshold}: minimum number of episodes in connection must be at least this value
 * {greatest_threshold}: maximum number of episodes in connection must be at least this value
 */

select a.show_id show_a, b.show_id show_b, count(1) as actors
from played_in a
cross join played_in b
  on a.show_id > b.show_id
    and a.show_id in ({show_list})
    and b.show_id in ({show_list})
    and a.actor_id = b.actor_id
    and least(a.episodes, b.episodes) >= {least_threshold}
    and greatest(a.episodes, b.episodes) >= {greatest_threshold}
group by a.show_id, b.show_id;
