select id, title, shows.episodes, count(pi.actor_id) as actors
from shows
inner join played_in pi
  on shows.id = pi.show_id
group by shows.id
order by title;
