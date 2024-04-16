CREATE TABLE IF NOT EXISTS `PlatformGame`(
    `id` INT AUTO_INCREMENT NOT NULL,
    `platformId` INT,
    `gameId` INT,
    `is_active` TINYINT(1) default 1,
    `created` timestamp default current_timestamp,
    `modified` timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`platformId`) REFERENCES Platforms (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gameId`) REFERENCES Games (`id`) ON DELETE CASCADE,
    UNIQUE KEY (`platformId`, `gameId`)
)