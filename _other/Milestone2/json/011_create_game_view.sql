(
    SELECT
        Games.*,
        Platforms.id as `PlatformID`,
        Platforms.name as `Platform`,
        Genres.id as `GenreID`,
        Genres.name as `Genre`
    FROM
        (
            (
                (
                    (
                        `Games`
                        INNER JOIN `PlatformGame` p ON Games.`id` = p.`gameId`
                    )
                    INNER JOIN `Platforms` ON `platformId` = Platforms.id
                )
                INNER JOIN `GameGenre` g ON Games.id = g.`gameId`
            )
            INNER JOIN `Genres` ON `genreId` = Genres.id
        )
    WHERE
        Games.id = :gameId
        AND Games.is_active = 1
    ORDER BY
        Games.name ASC
)
UNION
(
    SELECT
        Games.*,
        null as `PlatformID`,
        null as `Platform`,
        null as `GenreID`,
        null as `Genre`
    FROM
        Games
    WHERE
        Games.id = :gameId
        AND Games.is_active = 1
);

-- // // checks if there is a valid value, removes last as last is the union (unless sorted)
-- // if (!is_null($res["Platforms"][array_key_first($res["Platforms"])]) && is_null(end($res["Platforms"]))) {
-- //     array_pop($res["Platforms"]);
-- // }
-- // if (!is_null($res["Genres"][array_key_first($res["Genres"])]) && is_null(end($res["Genres"]))) {
-- //     array_pop($res["Genres"]);
-- // }
(
    SELECT
        Games.*,
        Platforms.id as `PlatformID`,
        Platforms.name as `Platform`,
        Genres.id as `GenreID`,
        Genres.name as `Genre`
    FROM
        (
            (
                (
                    (
                        `Games`
                        INNER JOIN `PlatformGame` p ON Games.`id` = p.`gameId`
                    )
                    INNER JOIN `Platforms` ON `platformId` = Platforms.id
                )
                INNER JOIN `GameGenre` g ON Games.id = g.`gameId`
            )
            INNER JOIN `Genres` ON `genreId` = Genres.id
        )
    WHERE
        Games.id = :gameID
    ORDER BY
        Games.name ASC
)
UNION
(
    SELECT
        Games.*,
        null as `PlatformID`,
        null as `Platform`,
        null as `GenreID`,
        null as `Genre`
    FROM
        Games
    WHERE
        Games.id = :gameID
);

-- SELECT
--     Games.*,
--     Platforms.id as `PlatID`,
--     Platforms.name as `Platform`,
--     Genres.id as `GenreID`,
--     Genres.name as `GenreID`
-- FROM
--     (
--         (
--             (
--                 (
--                     `Games`
--                     INNER JOIN `PlatformGame` p ON Games.`id` = p.`gameId`
--                 )
--                 INNER JOIN `Platforms` ON `platformId` = Platforms.id
--             )
--             INNER JOIN `GameGenre` g ON Games.id = g.`gameId`
--         )
--         INNER JOIN `Genres` ON `genreId` = Genres.id
--     )
-- WHERE
--     Games.id = 12226
-- ORDER BY
--     Games.name
--     AND Games.is_active = 1
-- better query for select info
SELECT
    Games.*,
    Platforms.id as `PlatformID`,
    Platforms.name as `Platform`,
    Genres.id as `GenreID`,
    Genres.name as `Genre`
FROM
    (
        (
            (
                (
                    `Games`
                    LEFT JOIN `PlatformGame` p ON Games.`id` = p.`gameId`
                )
                LEFT JOIN `Platforms` ON `platformId` = Platforms.id
            )
            LEFT JOIN `GameGenre` g ON Games.id = g.`gameId`
        )
        LEFT JOIN `Genres` ON `genreId` = Genres.id
    )
WHERE
    Games.id = :gameId
    AND Games.is_active = 1
ORDER BY
    Games.name ASC;

SELECT
    Games.*,
    Platforms.id as `PlatformID`,
    Platforms.name as `Platform`,
    Genres.id as `GenreID`,
    Genres.name as `Genre`
FROM
    (
        (
            (
                (
                    `Games`
                    LEFT JOIN `PlatformGame` p ON Games.`id` = p.`gameID`
                )
                LEFT JOIN `Platforms` ON `platformId` = Platforms.id
            )
            LEFT JOIN `GameGenre` g ON Games.id = g.`gameID`
        )
        LEFT JOIN `Genres` ON `genreId` = Genres.id
    )
WHERE
    Games.id = :gameID
    AND Games.is_active = 1;