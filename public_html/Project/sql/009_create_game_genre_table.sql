CREATE TABLE IF NOT EXISTS `GameGenre`(
    `id` INT AUTO INCREMENT NOT NULL,
    `genreId` INT,
    `gameId` INT,
    `is_active` TINYINT(1) default 1,
    `created` timestamp default current_timestamp,
    `modified` timestamp default current_timestamp on update current_timestamp,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`genreId`) REFERENCES Genres (`id`),
    FOREIGN KEY (`gameId`) REFERENCES Games (`id`),
    UNIQUE KEY (`genreId`, `gameId`)
)