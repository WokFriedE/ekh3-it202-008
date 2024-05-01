SELECT
    d.id,
    dailyDate as `date`,
    g.name,
    c.attempts,
    c.timeTaken,
    g.sqrImgURL,
    v.Completed
FROM
    (
        (
            `DailyGame` d
            JOIN `Games` g on d.`gameId` = g.id
        )
        LEFT JOIN `Completed_Games` c on c.`DailyGameID` = d.id
    )
    LEFT JOIN (
        SELECT
            cv.`userId`,
            IF(
                cv.`userId` = :currUser
                AND cv.is_active = 1,
                1,
                0
            ) AS `Completed`
        FROM
            `Completed_Games` cv
    ) v on c.`userId` = v.`userId`
WHERE
    1 = 1
SELECT
    cv.`userId`,
    IF(
        cv.`userId` = :currUser
        AND cv.is_active = 1,
        1,
        0
    ) AS `Completed`
FROM
    `Completed_Games` cv
SELECT
    d.id,
    dailyDate as `date`,
    g.name,
    IF(c.`userId` = :currUser, 1, 0) AS `Completed`,
    c.attempts,
    c.timeTaken,
    g.sqrImgURL
FROM
    (
        (
            `DailyGame` d
            JOIN `Games` g on d.`gameId` = g.id
        )
        LEFT JOIN `Completed_Games` c on c.`DailyGameID` = d.id
    )
WHERE
    1 = 1;

SELECT
    Users.id,
    Users.username,
    (
        SELECT
            GROUP_CONCAT(
                "Challenge ",
                ur.id,
                ' (',
                IF(ur.is_active = 1, 'active', 'inactive'),
                ')'
            )
        from
            Completed_Games ur
            JOIN DailyGame on ur.DailyGameID = DailyGame.id
        WHERE
            ur.userId = Users.id
    ) as challenges
from
    Users
UPDATE
    `Completed_Games`
SET
    is_active = ! is_active
WHERE
    userId = :uid
    AND DailyGameID = :challengeID -- 4, 2
    -- date, squareImg, name, attempts, time, dailyGame id
SELECT
    d.id,
    dailyDate as `date`,
    g.name,
    g.`sqrImgURL`,
    IF(cg.`userId` is not NULL, 1, 0) AS `Completed`
FROM
    (
        (
            `DailyGame` d
            LEFT JOIN (
                SELECT
                    *
                FROM
                    `Completed_Games`
                WHERE
                    `userId` = :uid
                    and is_active = 1
            ) cg ON d.id = cg.DailyGameID
        )
        LEFT JOIN `Games` g on d.gameId = g.id
    ) -- Get us the total count
SELECT
    count(1)
FROM
    `DailyGame`
WHERE
    id not in (
        SELECT
            DISTINCT DailyGameID
        FROM
            Completed_Games
        WHERE
            is_active = 1
    ) (
        SELECT
            DISTINCT DailyGameID
        FROM
            Completed_Games
        WHERE
            is_active = 1
    );

SELECT
    DISTINCT d.id,
    d.gameId,
    dailyDate as `date`,
    g.name,
    g.`sqrImgURL`,
    d.is_active,
    (
        SELECT
            GROUP_CONCAT(u.username, "#", u.id)
        FROM
            Users u
            JOIN `Completed_Games` cgt ON u.id = cgt.userId
        WHERE
            cgt.`DailyGameID` = d.id
            AND cgt.is_active = 1
    ) as Users
FROM
    (
        (
            `DailyGame` d
            LEFT JOIN (
                SELECT
                    *
                FROM
                    `Completed_Games`
                WHERE
                    is_active = 1
            ) cg ON d.id = cg.DailyGameID
        )
        LEFT JOIN `Games` g on d.gameId = g.id
    )
WHERE
    1 = 1
SELECT
    GROUP_CONCAT(u.username)
FROM
    Users u
    JOIN `Completed_Games` cgt ON u.id = cgt.userId
WHERE
    `DailyGameID` = 3 -- used for getting totalCount
SELECT
    count(1) as `totalCount`
FROM
    `DailyGame`
WHERE
    `id` in (
        SELECT
            `DailyGameID`
        FROM
            `Completed_Games`
        WHERE
            is_active = 1
    );

SELECT
    dg.id,
    g.name,
    g.`firstReleaseDate`,
    g.`screenshotImgURL`,
    g.publisher,
    g.developer
FROM
    `DailyGame` dg
    LEFT JOIN `Games` g on GameID = g.id
WHERE
    dg.id = :gid
    AND dg.is_active = 1
    AND g.is_active = 1
INSERT INTO
    `Completed_Games`(
        userId,
        DailyGameID,
        attempts,
        timeTaken,
        completed
    )
VALUES
    (1, 1, 5, 10, 1) ON DUPLICATE
UPDATE
    userId = 1,
    DailyGameID = 1,
    attempts = 5,
    timeTaken = 10,
    completed = 1