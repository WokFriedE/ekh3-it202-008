<?php
require(__DIR__ . "/../../partials/nav.php");

if (isset($_GET["symbol"])) {
    // TODO update to proper get pull
    $result = get("https://opencritic-api.p.rapidapi.com/game/popular", "GAME_API_KEY", [], false);
    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
}

if (isset($_GET["symbol"])) {
    $quote = $result["Global Quote"];

    $db = getDB();
    $query = "INSERT INTO ``";
    $columns = [];
    $params = [];

    foreach ($quote as $k => $v) {
        array_push($columns, "`$k`"); // <-- add col
        $params[":$k"] = $v; // adds the : so it acknowledges that they are params to be passed
    }
    $query .= "(" . join(",", $columns) . ")";
    $query .= "VALUES (" . join(",", array_keys($columns)) . ")"; // this should bind the values of each column into the fields


}


?>
<div class="container-fluid">
    <h1>Fun Games</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    <div class="row">
        <pre>

        </pre>
    </div>


</div>