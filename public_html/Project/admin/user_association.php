<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

// Function used to process the challenge ID's from a UID

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}

$db = getDB();

//search for user by username
$users = [];
$username = "";
if (isset($_POST["username"])) {
    $username = se($_POST, "username", "", false);
    if (!empty($username)) {
        $stmt = $db->prepare(" SELECT Users.id, Users.username, 
        (SELECT GROUP_CONCAT('Challenge ', ur.DailyGameID, ' (' , IF(ur.is_active = 1,'active','inactive') , ')') from 
        Completed_Games ur JOIN DailyGame on ur.DailyGameID = DailyGame.id WHERE ur.userId = Users.id) as challenges
        from Users WHERE username like :username LIMIT 25");

        try {
            $stmt->execute([":username" => "%$username%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $users = $results;
            }
        } catch (PDOException $e) {
            error_log("Username load: " . $e);
            flash("An error has occured", "danger");
        }
    } else {
        flash("Username must not be empty", "warning");
    }
}

//get active games
$active_games = [];
$params = [];
$query = "SELECT DailyGame.id, Games.name FROM (DailyGame LEFT JOIN Games on Games.id=DailyGame.gameId) WHERE DailyGame.is_active = 1 and Games.is_active = 1";
$gameSearch = se($_POST, "games", "", false);
if (!empty($gameSearch)) {
    $query .= " AND (Games.name like :gameQ OR DailyGame.id like :gameQ)";
    $params[":gameQ"] = "%$gameSearch%";
}
$query .= " LIMIT 25";
$stmt = $db->prepare($query);
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $active_games = $results;
    }
} catch (PDOException $e) {
    error_log("DailyGames load: " . $e);
    flash("An error has occured", "danger");
}


// Processing the selected data
if (isset($_POST["users"]) && isset($_POST["challenges"])) {
    $user_ids = $_POST["users"]; //se() doesn't like arrays so we'll just do this
    $challenge_ids = $_POST["challenges"]; //se() doesn't like arrays so we'll just do this
    $attempts = (int) se($_POST, "attempts", "", false);
    $timeTaken = (int) se($_POST, "timeTaken", "", false);
    $toggleCheck = (bool) se($_POST, "toggle", false, false);

    if (empty($user_ids) || empty($challenge_ids)) {
        flash("Both users and challenges need to be selected", "warning");
    } elseif (!$toggleCheck && (empty($attempts) || empty($timeTaken))) {
        flash("Attempts and Time Taken needs to filled", "danger");
    } elseif ($toggleCheck) {
        $stmt = $db->prepare("UPDATE `Completed_Games` SET is_active = !is_active WHERE userId=:uid AND DailyGameID=:challengeID");
        foreach ($user_ids as $uid) {
            foreach ($challenge_ids as $challengeID) {
                try {
                    $stmt->execute([":uid" => $uid, ":challengeID" => $challengeID]);
                    flash("Toggled challenge", "success");
                } catch (PDOException $e) {
                    if ($e->errorInfo[1] == 1) {
                    }
                    error_log("Update: " . $e);
                    flash("An error has occured", "danger");
                }
            }
        }
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $stmt = $db->prepare("INSERT INTO Completed_Games (userId, DailyGameID, is_active,attempts,timeTaken) VALUES (:uid, :challengeID, 1, :attempts, :timeTaken) 
        ON DUPLICATE KEY UPDATE is_active = !is_active");
        foreach ($user_ids as $uid) {
            foreach ($challenge_ids as $challengeID) {
                try {
                    $stmt->execute([":uid" => $uid, ":challengeID" => $challengeID, ":attempts" => $attempts, ":timeTaken" => $timeTaken]);
                    flash("Updated user", "success");
                } catch (PDOException $e) {
                    error_log("Update Fail: " . $e);
                    flash("An error has occured", "danger");
                }
            }
        }
    }
}

?>
<div class="container-fluid">
    <h3>Associate Challenges</h3>
    <form method="POST">
        <?php render_input(["label" => "Username Search", "type" => "search", "name" => "username", "placeholder" => "Username Search", "value" => $username]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_input(["label" => "Game Name/ID Search", "type" => "search", "name" => "games", "placeholder" => "Challenge Search (optional)", "value" => $gameSearch]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST" onsubmit="return validate(this)">
        <?php render_button(["text" => "submit", "type" => "submit", "text" => "Update"]); ?>
        <div class="row">
            <?php render_input(["type" => "checkbox", "name" => "toggle", "label" => "Toggle Relations", "value" => "true"]); ?>
            <?php render_input(["type" => "number", "name" => "attempts", "placeholder" => "Attempts Taken...", "label" => "Attempts (1-5)", "include_margin" => true]); ?>
            <?php render_input(["type" => "number", "name" => "timeTaken", "placeholder" => "Time Taken...", "label" => "Time taken in secs", "include_margin" => true]); ?>
        </div>
        <div class="row">
            <div class="col">
                <?php foreach ($users as $user) : ?>
                    <?php render_input(["type" => "checkbox", "id" => "user_" . se($user, 'id', "", false), "name" => "users[]", "label" => se($user, "username", "", false), "value" => se($user, 'id', "", false)]); ?>
                    <?php se($user, "challenges", "No Complete"); ?>
                <?php endforeach; ?>
            </div>
            <div class="col">
                <?php foreach ($active_games as $game) : ?>
                    <?php render_input(["type" => "checkbox", "id" => "challenge_" . se($game, 'id', "", false), "name" => "challenges[]", "label" => "Challenge " . se($game, "id", "", false) . " - " . se($game, "name", "", false), "value" => se($game, 'id', "", false)]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </form>
</div>
<!-- complete validation  -->
<script>
    function validate(form) {
        let attempts = form.attempts.value;
        let timeTaken = form.timeTaken.value;
        let valid = true;
        if (form.toggle.checked) {
            return true;
        }
        if (attempts == "" || (parseInt(attempts) < 1 || parseInt(attempts) > 5)) {
            valid = false;
            flash("[Client] Attempts is required and between 1-5", "warning");
        }
        if (timeTaken == "" || (parseInt(timeTaken) < 0)) {
            valid = false;
            flash("[Client] Time Taken is required and needs to be positive", "warning");
        }
        return valid;
    }
</script>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>