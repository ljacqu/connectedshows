select a.show_id show_a, b.show_id show_b, count(1) as actors
from played_in a
cross join played_in b
  on a.show_id > b.show_id
    and a.actor_id = b.actor_id
group by a.show_id, b.show_id;
