<?php
require(__DIR__ . "/../../partials/nav.php");
$result = get("https://opencritic-api.p.rapidapi.com/game/popular", "GAME_API_KEY", [], false);
error_log("Response: " . var_export($result, true));
if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
    $result = json_decode($result["response"], true);
} else {
    $result = [];
}
?>
<div class="container-fluid">
    <h1>Fun Games</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    <div class="row ">
        <?php var_dump($result) ?>;
    </div>
</div>