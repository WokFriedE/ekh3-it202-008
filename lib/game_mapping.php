<?php

function map_popular_data($api_data, $is_api = 0)
{
    foreach ($api_data as $i => $ent) {
        foreach ($ent as $k => $v) {
            if (!in_array($k, ["topCriticScore", "name", "id"])) {
                unset($api_data[$i][$k]);
            }
        }
        $api_data[$i]["is_api"] = $is_api;
    }
    return $api_data;
}

function map_game_data($api_data)
{
    $imgURI = "https://img.opencritic.com/";
    foreach ($api_data as $k => $v) {
        if (!in_array($k, ["description", "Companies", "Platforms", "url", "firstReleaseDate", "images", "Genres", "id"])) {
            unset($api_data[$k]);
        } elseif ($k === "Companies") {
            // get the publisher and developer
            foreach ($v as $item => $vals)
                if (isset($vals["type"]) && $vals["type"] === "PUBLISHER") {
                    $api_data["publisher"] = $vals["name"];
                } elseif (isset($vals["type"]) && $vals["type"] === "DEVELOPER")
                    $api_data["developer"] = $vals["name"];
            unset($api_data[$k]);
        } elseif ($k === "images") {
            // get a square image and screenshot
            if (isset($v["square"])) {
                $api_data["sqrImgURL"] = $imgURI . $v["square"]["og"];
                unset($api_data[$k]);
            }
            if (isset($v["screenshots"])) {
                $api_data["screenshotImgURL"] = $imgURI . $v["screenshots"][0]["og"];
                unset($api_data[$k]);
            }
        } elseif ($k === "Platforms") {
            // parse platforms for linking
            $api_data[$k] = map_platform_data($api_data[$k]);
        } elseif ($k === "Genres") {
            // parse genres for linking
            $api_data[$k] = map_genre_data($api_data[$k]);
        } elseif ($k === "firstReleaseDate") {
            // update date format for sql
            $api_data[$k] = substr($api_data[$k], 0, 10);
        }
    }
    return $api_data;
}


function map_platform_data($api_data)
{
    foreach ($api_data as $item => $vals) {
        foreach ($vals as $key => $value) {
            if (!in_array($key, ["id", "name", "shortName"])) {
                unset($api_data[$item][$key]);
            }
        }
    }
    return $api_data;
}

function map_genre_data($api_data)
{
    foreach ($api_data as $item => $vals) {
        foreach ($vals as $key => $value) {
            if (!in_array($key, ["id", "name"])) {
                unset($api_data[$item][$key]);
            }
        }
    }
    return $api_data;
}
