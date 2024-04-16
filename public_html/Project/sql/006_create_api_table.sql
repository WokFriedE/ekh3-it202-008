CREATE TABLE IF NOT EXISTS `Games` (
    `id` INT NOT NULL PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `publisher` VARCHAR(30),
    `developer` VARCHAR(30),
    `description` TEXT,
    `topCriticScore` FLOAT,
    `sqrImgURL` VARCHAR(200),
    `screenshotImgURL` VARCHAR(200),
    `url` VARCHAR(200),
    -- ISO-8601 date 
    `firstReleaseDate` DATE,
    `is_api` tinyint(1) not null default 0,
    `is_active` TINYINT(1) default 1,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)