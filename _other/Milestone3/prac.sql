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
    1 = 1