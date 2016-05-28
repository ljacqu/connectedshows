-- Show actors sorted by number of roles in other shows
-- var = :show

SELECT name, COUNT(show_id) AS other_roles
FROM (
    SELECT actor_id, show_id
    FROM played_in
    WHERE actor_id IN (
        SELECT actor_id
        FROM played_in
        WHERE show_id = :show
    )
    AND played_in.show_id <> :show
) AS p
INNER JOIN actors
ON actors.id = p.actor_id
GROUP BY name
ORDER BY other_roles DESC
