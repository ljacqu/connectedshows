SELECT *
FROM `played_in`
WHERE `actor_id` IN (
    SELECT `actor_id` 
    FROM `played_in`
    WHERE {show_filter}
    GROUP BY `actor_id`
    HAVING COUNT(*) = {total_shows}
)
AND ({show_filter})