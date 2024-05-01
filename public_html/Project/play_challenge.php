<?php
require(__DIR__ . "/../../partials/nav.php");

$id = (int)se($_GET, "id", -1, false);
if ($id < 0) {
    flash("Invalid Challenge ID", "danger");
    redirect("daily_game.php");
}

$is_start = isset($_GET["start"]);
$session_key = $_SERVER["SCRIPT_NAME"];
$max_attempts = 5;
$is_guess = isset($_POST["guess"]);
$guess = "";

if ($is_start && !$is_guess) {
    session_delete($session_key);
    unset($_GET["start"]);
    unset($_POST);

    // check if the user attempts are empty, save data into the cookie for the attempt and pull it, according the attempt give the item
    $opts = ["active_only" => true, "debug" => true];
    $daily_info = selectInfo("DailyGame", $id, $cols = ["id", "gameId", "dailyDate"], $opts);
    if (empty($daily_info)) {
        flash("Invalid Challenge ID", "danger");
        redirect("daily_game.php");
    }
    $game_info = selectGameInfo($daily_info["gameId"], true, true);
    $_POST["attempts"] = 1;
    $_POST["startTime"] = time();
    $_POST["challengeID"] = $daily_info["id"];
    $_POST["challengeDate"] = $daily_info["dailyDate"];
    $_POST["gameID"] = $game_info["id"];
    $_POST["platforms"] = $game_info["Platforms"];
    $_POST["genres"] = $game_info["Genres"];
    $_POST["gameName"] = se($game_info, "name", "N/A", false);
    $_POST["publisher"] = se($game_info, "publisher", "None", false);
    $_POST["developer"] = se($game_info, "developer", "None", false);
    $_POST["ssURL"] = se($game_info, "screenshotImgURL", missingURL(), false);
    $_POST["score"] = round(se($game_info, "topCriticScore", -10, false));
    if ($_POST["score"] < 0) {
        $_POST["score"] = "None";
    }
    session_save($session_key, $_POST);
} else {
    $session_data = session_load($session_key);
    $guess = se($_POST, "guess", "", false);
    $_POST = $session_data;
}


// Parse through post to remove hostile post
foreach ($_POST as $k => $v) {
    if (!in_array($k, ["platforms", "genres", "gameName", "publisher", "developer", "ssURL", "score", "startTime", "attempts", "challengeID", "challengeDate", "guess", "gameID"])) {
        unset($_POST[$k]);
    }
}

$gid = $_POST["gameID"];
$platforms = $_POST["platforms"];
$genres = $_POST["genres"];
$gameName = se($_POST, "gameName", "N/A", false);
$publisher = se($_POST, "publisher", "None", false);
$developer = se($_POST, "developer", "None", false);
$ssURL = se($_POST, "ssURL", missingURL(), false);
$score = round(se($_POST, "score", -10, false));
$attempts = (int) se($_POST, "attempts", 5, false);
$challengeID = se($_POST, "challengeID", -1, false);
$is_complete = false;


if ($is_guess) {
    if ($guess == $gameName) {
        $is_complete = true;
        $db = getDB();
        $query = "INSERT INTO `Completed_Games`(userId, DailyGameID, attempts,timeTaken,completed) VALUES (:uid, :cid, :attempts,:timetaken, 1) 
        ON DUPLICATE KEY UPDATE userId = :uid, DailyGameID= :cid,attempts = :attempts, timeTaken = :timetaken,completed = 1";
        $params = [];

        $params[":uid"] = get_user_id();
        $params[":cid"] = $challengeID;
        $params[":attempts"] = $attempts;
        $params[":timetaken"] = $_POST["startTime"] - time();
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            flash("Congrats! Correct Guess", "Success");
        } catch (Exception $e) {
            error_log("Error Adding game $id" . var_export($e, true));
            flash("Error Adding record", "danger");
        }
    } elseif ($attempts >= $max_attempts) {
        flash("Max Attempts reached, better luck next time :(", "info");
        $is_complete = true;
    } else {
        flash("Incorrect Guess " . ($max_attempts - $attempts) . " attempts remain.", "info");
        $_POST["attempts"] += 1;
        unset($_POST["guess"]);
        session_delete($session_key);
        session_save($session_key, $_POST);
        redirect("play_challenge.php?id=" . $challengeID);
    }
}

if (isset($_GET["a"])) {
    $attempts = (int) $_GET["a"];
}

/* Hints
1. top critic score 
2. genres
3. platforms
4. publisher
5. developer
*/
?>


<div class="container-fluid">
    <h1 class=" text-center">Challenge <?php echo $challengeID; ?> </h1>
    <h5 class="text-center">released: <?php se($_POST, "challengeDate"); ?></h5>
    <div class="card-group">
        <!-- Card for genres -->
        <div class="card <?php echo ($attempts < 3) ? "bg-secondary" : ""; ?>">
            <div class="card-body">
                <?php if ($attempts > 2) : ?>
                    <h5 class="card-title">Genres</h5>
                    <div class="card-body">
                        <?php foreach ($genres as $k => $v) : ?>
                            <p class="card-text"><?php echo $v ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Card for main items -->
        <div class="card mx-1">
            <div style="overflow: hidden;">
                <img class="card-img-top img-fluid" src="<?php echo $ssURL ?>" alt="Screenshot for game" style="filter: blur(<?php echo (5 - $attempts) ?>rem)">
            </div>
            <div class="card-body">
                <p class="card-text">Score: <?php echo $score ?> / 100</p>
                <p class="card-text"><?php echo ($attempts > 0) ? "Publisher: " . $publisher : "" ?></p>
                <p class="card-text"><?php echo ($attempts > 1) ? "Developer: " . $developer : "" ?></p>
            </div>
            <?php if (!$is_complete) : ?>
                <p class="card-footer text-muted text-center">Attempts Done: <?php echo $attempts ?></p>
            <?php elseif ($attempts == 4) : ?>
                <p class="card-footer text-muted text-center">Last Guess</p>
            <?php else : ?>
                <p class="card-footer text-muted text-center">Thats all folks :)</p>
                <a class="btn custBtn" href="<?php echo get_url('game_details.php?id=' . $gid); ?>">View Game</a>
            <?php endif; ?>
        </div>
        <!-- Card for platforms -->
        <div class="card <?php echo ($attempts < 4) ? "bg-secondary" : ""; ?>">
            <div class="card-body">
                <?php if ($attempts > 3) : ?>
                    <h5 class="card-title">Platforms</h5>
                    <div class="card-body">
                        <?php foreach ($platforms as $k => $v) : ?>
                            <p class="card-text"><?php echo $v ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (!$is_complete) : ?>
        <form method="POST" onsubmit="return true">
            <div class="row mb-3" style="align-items: flex-end;">
                <input id="mainGuess" type="text" name="guess" oninput="liveFetch(this)" />
            </div>
            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Guess"]); ?>
        </form>
        <div class="row w-100 g-4">
            <ul class="replies"></ul>
        </div>
    <?php endif; ?>

</div>

<script>
    let timeout = null;

    function liveFetch(ele) {

        //debounce logic
        if (timeout) {
            clearTimeout(timeout);
            timeout = null;
        }
        timeout = setTimeout(() => {
            let target = document.getElementsByClassName("replies")[0];
            try {
                fetch(`/Project/api/live_search.php?query=${ele.value}`).then(resp => resp.json())
                    .then(json => {
                        let rep = json.response;
                        if (!rep) {
                            target.innerHTML = `Unknown Name`;
                            return;
                        }
                        array = rep.values();
                        target.innerHTML = `Did you mean?`;
                        array.forEach(element => {
                            target.innerHTML += `<li><button type="button" class="btn" onclick='changeValue(this)'>${element}</button></li>`;
                        });
                    })
            } catch (error) {
                target.innerHTML = `Unknown name`;
            }
        }, 500)
    }

    function changeValue(btn) {
        document.getElementById("mainGuess").value = btn.innerHTML;
    }
</script>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>

<!-- TODO on own time: make it so the attempts are saved seperately, make unique query for the call -->
<!-- TODO generate should choose games that have not been chosen for a while -->
<!-- TODO have a retry in a day or reveal answer -->