<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php

// $id = se($_GET, "gameId", "", false);
// $result = fetch_game($id);
// // $result = fetch_json("hifi");
// $result = map_game_data($result);
// $result = insertGame($result);

//TODO handle game fetch
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    $id =  strtoupper(se($_POST, "id", "", false));
    $quote = [];
    if ($id) {
        if ($action === "fetch") {
            $result = fetch_game($id);
            $result = map_game_data($result);

            error_log("Data from API" . var_export($result, true));
            if ($result) {
                $quote = $result;
                $quote["is_api"] = 1;
                $opts = ["addAll" => true, "addPlat" => false, "addGenre" => false];
                insertGame($result, $opts);
            }
        } else if ($action === "create") {
            foreach ($_POST as $k => $v) {
                if (!in_array($k, ["id", "name", "publisher", "developer", "description", "topCriticScore", "firstReleaseDate"])) {
                    unset($_POST[$k]);
                }
                $quote = $_POST;
                error_log("Cleaned up POST: " . var_export($quote, true));
            }
            try {
                //insert data
                //optional options for debugging and duplicate handling
                $opts =
                    ["debug" => true, "update_duplicate" => false, "columns_to_update" => []];
                $result = insert("Games", $quote, $opts);
                if (!$result) {
                    flash("Unhandled error", "warning");
                } else {
                    flash("Created record with id " . var_export($result, true), "success");
                }
            } catch (InvalidArgumentException $e1) {
                error_log("Invalid arg" . var_export($e1, true));
                flash("Invalid data passed", "danger");
            } catch (PDOException $e2) {
                if ($e2->errorInfo[1] == 1062) {
                    flash("An entry for this game already exists for today", "warning");
                } else {
                    error_log("Database error" . var_export($e2, true));
                    flash("Database error", "danger");
                }
            } catch (Exception $e3) {
                error_log("Invalid data records" . var_export($e3, true));
                flash("Invalid data records", "danger");
            }
        }
    } else {
        flash("You must provide a id", "warning");
    }
}

// TODO remove if not used
// dump($_POST);

//attempt to apply
if (isset($_POST["genres"])) {
    $db = getDB();
    $genreIDs = $_POST["genres"];
    $stmt = $db->prepare("INSERT INTO `GameGenre` (genreID, gameId, is_active) VALUES (:genreID, :gameId, 1) 
    ON DUPLICATE KEY UPDATE is_active = !is_active");
    foreach ($genreIDs as $genreID) {
        try {
            $stmt->execute([":genreID" => $genreID, ":gameId" => $id]);
            flash("Updated role", "success");
        } catch (PDOException $e) {
            if ($e[1] == 1062) {
                flash("Game key already exists", "danger");
            } else {
                flash(var_export($e->errorInfo, true), "danger");
            }
        }
    }
}

if (isset($_POST["platforms"])) {
    $db = getDB();
    // TODO use the insert function
    $platformIDs = $_POST["platforms"];
    $stmt = $db->prepare("INSERT INTO `PlatformGame` (platformId, gameId, is_active) VALUES (:platformId, :gameId, 1) 
    ON DUPLICATE KEY UPDATE is_active = !is_active");
    foreach ($platformIDs as $platformId) {
        try {
            $stmt->execute([":platformId" => $platformId, ":gameId" => $id]);
            flash("Updated role", "success");
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
}

// Get active platforms
$platformForm = getRelation("Platforms", []);
// Get active Genres
$genreForm = getRelation("Genres", []);

//TODO handle manual create game
?>
<div class="container-fluid">
    <h3>Create or Fetch Game</h3>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link bg-primary text-white mx-1" href="#" onclick="switchTab('create')">Fetch</a>
        </li>
        <li class="nav-item">
            <a class="nav-link bg-primary text-white mx-1" href="#" onclick="switchTab('fetch')">Create</a>
        </li>
    </ul>
    <div id="fetch" class="tab-target">
        <form method="POST">
            <?php render_input(["type" => "search", "name" => "id", "placeholder" => "ID", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "hidden", "name" => "action", "value" => "fetch"]); ?>
            <?php render_button(["text" => "Search", "type" => "submit",]); ?>
        </form>
    </div>
    <div id="create" style="display: none;" class="tab-target">
        <form method="POST" onsubmit="return validate(this)">
            <?php render_input(["type" => "number", "name" => "id", "placeholder" => "Game ID", "label" => "Game ID", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "name", "placeholder" => "Name", "label" => "Name", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "publisher", "placeholder" => "Publisher (optional)", "label" => "Publisher"]); ?>
            <?php render_input(["type" => "text", "name" => "developer", "placeholder" => "Developer", "label" => "Developer", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "description", "placeholder" => "Description", "label" => "Description", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "text", "name" => "topCriticScore", "placeholder" => "Critic Score", "label" => "Critic Score", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "date", "name" => "firstReleaseDate", "placeholder" => "Release Date", "label" => "Release Date", "rules" => ["required" => "required"]]); ?>
            <?php render_input(["type" => "url", "name" => "sqrImgURL", "placeholder" => "Square Image URL (optional)", "label" => "Square Image"]); ?>
            <?php render_input(["type" => "url", "name" => "screenshotImgURL", "placeholder" => "Screenshot Image URL (optional)", "label" => "Screenshot Image"]); ?>
            <?php render_input(["type" => "url", "name" => "url", "placeholder" => "Game Page URL (optional)", "label" => "Game Page URL"]); ?>

            <?php render_input(["type" => "hidden", "name" => "action", "value" => "create"]); ?>

            <?php //foreach ($platformForm as $k => $v) {
            //    render_input($v);
            // } 
            ?>

            <?php // foreach ($genreForm as $k => $v) {
            //render_input($v);
            //} 
            ?>

            <?php render_button(["text" => "Search", "type" => "submit", "text" => "Create"]); ?>
        </form>
    </div>
</div>


<script>
    function switchTab(tab) {
        let target = document.getElementById(tab);
        if (target) {
            let eles = document.getElementsByClassName("tab-target");
            for (let ele of eles) {
                ele.style.display = (ele.id === tab) ? "none" : "block";
            }
        }
    }

    function validate(form) {
        let sc = form.topCriticScore.value;
        let valid = true;
        if (!verifyScore(sc)) {
            valid = false;
        }
        if (form.developer.value == "") {

            valid = false
            flash("[Client] Developer is required", "warning")
        }
        if (form.description.value == "") {
            valid = false
            flash("[Client] Description is required", "warning")
        }
        if (!verifyDate(form.firstReleaseDate.value)) {
            valid = false
        }
        return valid;
    }
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>