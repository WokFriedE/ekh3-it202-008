CREATE TABLE IF NOT EXISTS `Completed_Games`(
    `id` INT AUTO_INCREMENT NOT NULL,
    `userId` INT,
    `DailyGameID` INT,
    `attempts` INT,
    `timeTaken` INT,
    `completed` TINYINT(1) default 0,
    `is_active` TINYINT(1) default 1,
    `created` timestamp default current_timestamp,
    `modified` timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`DailyGameID`) REFERENCES DailyGame (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`userId`) REFERENCES Users (`id`) ON DELETE CASCADE,
    UNIQUE KEY (`userId`, `DailyGameID`)
)