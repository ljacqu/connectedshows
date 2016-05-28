/* DEBUG: List shows with an episode count that doesn't match MAX(played_in.episodes) */

SELECT *
FROM shows
INNER JOIN (
    SELECT show_id, MAX(played_in.episodes) AS max_episodes
    FROM played_in
    GROUP BY show_id
) sub ON sub.show_id = shows.id
WHERE sub.max_episodes <> shows.episodes

/* DEBUG: Update episode count. */
/*UPDATE shows
INNER JOIN (
    SELECT show_id, MAX(played_in.episodes) AS max_episodes
    FROM played_in
    GROUP BY show_id
) AS sub ON shows.id = sub.show_id
SET shows.episodes = max_episodes
WHERE episodes <= 0*/