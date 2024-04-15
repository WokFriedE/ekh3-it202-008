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
    $endpoint = "https://opencritic-api.p.rapidapi.com/game/genre";
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

function fetch_platforms()
{
    $data = [];
    $endpoint = "https://opencritic-api.p.rapidapi.com/game/platform";
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


function fetch_game($gameID)
{
    $data = [];
    $endpoint = "https://opencritic-api.p.rapidapi.com/game/" . $gameID;
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

function fetch_json($jsonName)
{
    $json = file_get_contents(__DIR__ . "/../_other/Milestone2/json/" . $jsonName . ".json");
    $json_data = json_decode($json, true);
    return $json_data;
}
// TODO implement a manage game data --> prob in refresh db and use that to makke request to db and such