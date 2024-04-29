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
    )