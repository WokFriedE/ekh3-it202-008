SELECT
    Games.*,
    Platforms.id as `PlatID`,
    Platforms.name as `Platform`,
    Genres.id as `GenreID`,
    Genres.name as `GenreID`
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
    Games.id = 12226
ORDER BY
    Games.name
    AND Games.is_active = 1