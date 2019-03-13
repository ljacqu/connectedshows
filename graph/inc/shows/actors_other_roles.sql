# select actor_id, actors.name, count(show_id)-1 other_shows
# from played_in
# left join actors
#   on actors.id = actor_id
# where actor_id in (
#   select actor_id
#   from played_in
#   where show_id = :show_id
# )
# group by actor_id
# order by other_shows desc
# limit 20;


select played_in.actor_id, name, role, episodes, other_shows
from played_in
inner join actors
on actors.id = actor_id
left join (
  select actor_id, count(1) - 1 as other_shows
  from played_in
  group by actor_id
) other_shows_by_actor on other_shows_by_actor.actor_id = played_in.actor_id
where show_id = :show_id
order by other_shows desc
limit 20;