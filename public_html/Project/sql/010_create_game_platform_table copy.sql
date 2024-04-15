CREATE TABLE IF NOT EXIST `GameGenre`(
    `id` INT AUTO INCREMENT NOT NULL,
    `platformId` INT,
    `gameId` INT,
    `is_active` TINYINT(1) default 1,
    `created` timestamp default current_timestamp,
    `modified` timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`platformId`) REFERENCES Platforms (`id`),
    FOREIGN KEY (`gameId`) REFERENCES Games (`id`),
    UNIQUE KEY (`platformId`, `gameId`)
)