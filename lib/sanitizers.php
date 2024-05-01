
<?php
// <!-- // ekh3 - 4/1/24 -->
// <!-- last modify // ekh3 - 4/21/24 -->

function sanitize_email($email = "")
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}
function is_valid_email($email = "")
{
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}
function is_valid_username($username)
{
    return preg_match('/^[a-z0-9_-]{3,16}$/', $username);
}
function is_valid_password($password)
{
    return strlen($password) >= 8;
}
function is_valid_date($date = "")
{
    return preg_match('/^[0-9]{4}-(1[0-2]|0[1-9])-(3[01]|[12][0-9]|0[1-9])$/', $date);
}
function is_valid_url($url = "")
{
    return filter_var(trim($url), FILTER_VALIDATE_URL);
}
