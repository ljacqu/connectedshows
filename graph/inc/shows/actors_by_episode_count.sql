select played_in.actor_id, name, role, episodes, other_shows
from played_in
inner join actors
  on actors.id = actor_id
left join (
  select actor_id, count(1)-1 as other_shows
  from played_in
  group by actor_id
) other_shows_by_actor on other_shows_by_actor.actor_id = played_in.actor_id
where show_id = :show_id
order by episodes desc
limit 20;