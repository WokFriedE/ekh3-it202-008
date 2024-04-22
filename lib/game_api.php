<?php

function fetch_popular()
{
    $data = [];
    $endpoint = "https://opencritic-api.p.rapidapi.com/game/popular";
    $isRapidAPI = true;
    $rapidAPIHost = "opencritic-api.p.rapidapi.com";
    $result = get($endpoint, "GAME_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
    return $result;
}

function fetch_genres()
{
    $data = [];
    $endpoint = "https://opencritic-api.p.rapidapi.com/genre";
    $isRapidAPI = true;
    $rapidAPIHost = "opencritic-api.p.rapidapi.com";
    $result = get($endpoint, "GAME_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        error_log("Fetch_genres failed pull");
        $result = [];
    }
    return $result;
}

function fetch_platforms()
{
    $data = [];
    $endpoint = "https://opencritic-api.p.rapidapi.com/platform";
    $isRapidAPI = true;
    $rapidAPIHost = "opencritic-api.p.rapidapi.com";
    $result = get($endpoint, "GAME_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        error_log("Fetch_platforms failed pull");
        $result = [];
    }
    return $result;
}


function fetch_game($gameID)
{
    $data = [];
    $endpoint = "https://opencritic-api.p.rapidapi.com/game/" . $gameID;
    $isRapidAPI = true;
    $rapidAPIHost = "opencritic-api.p.rapidapi.com";
    $result = get($endpoint, "GAME_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
        if (!(isset($result["Platforms"]) && count($result["Platforms"]) > 0)) {
            unset($result["Platforms"]);
        }
        if (!(isset($result["Genres"]) && count($result["Genres"]) > 0)) {
            unset($result["Genres"]);
        }
    } else {
        error_log("Fetch_game failed pull");
        $result = [];
    }
    return $result;
}

function fetch_json($jsonName)
{
    $json = file_get_contents(__DIR__ . "/../_other/Milestone2/json/" . $jsonName . ".json");
    $json_data = json_decode($json, true);
    return $json_data;
}
