CREATE TABLE IF NOT EXISTS `DailyGame`(
    `id` INT AUTO_INCREMENT NOT NULL,
    `gameId` INT,
    `is_active` TINYINT(1) default 1,
    `dailyDate` DATE,
    `created` timestamp default current_timestamp,
    `modified` timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`gameId`) REFERENCES Games (`id`) ON DELETE CASCADE,
    UNIQUE(`dailyDate`, `gameId`)
)