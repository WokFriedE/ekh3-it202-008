ALTER TABLE Users
ADD COLUMN username varchar(30) not null unique default(
    substring_index(email, '@', 1)
) COMMENT 'Username field that defaults to the name of the email given';

-- allows usernames of 0 to 30 chars, cannot be null and has to be unique
-- usernames will use default to beginning part of the email