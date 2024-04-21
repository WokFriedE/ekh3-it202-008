<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>

<?php

// Ethan - ekh3 - 4/22/24
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
                $opts = ["addAll" => true, "addPlat" => false, "addGenre" => false, "api" => true];
                insertGame($result, $opts);
            }

            // create game // Ethan - ekh3 - 4/22/24
        } else if ($action === "create") {
            // PHP validation
            $hasError = false;
            if (isset($_POST["id"]) && isset($_POST["name"]) && isset($_POST["developer"]) && isset($_POST["description"]) && isset($_POST["topCriticScore"]) && isset($_POST["firstReleaseDate"])) {
                $idTemp = se($_POST, "id", "", false);
                $nameTemp = se($_POST, "name", "", false);
                $developerTemp = se($_POST, "developer", "", false);
                $descriptionTemp = se($_POST, "description", "", false);
                $topCriticScoreTemp = se($_POST, "topCriticScore", "", false);
                $firstReleaseDateTemp = se($_POST, "firstReleaseDate", "", false);
                $ssURL = se($_POST, "screenshotImgURL", "", false);
                $squareURL = se($_POST, "sqrImgURL", "", false);
                $url = se($_POST, "url", "", false);

                if (!(is_int($idTemp) && (int) ($idTemp) >= 0)) {
                    flash("ID must be a postive int", "danger");
                    $hasError = true;
                }
                if (empty($nameTemp)) {
                    flash("Name cannot be empty", "danger");
                    $hasError = true;
                }
                if (empty($developerTemp)) {
                    flash("Developer cannot be empty", "danger");
                    $hasError = true;
                }
                if (empty($descriptionTemp)) {
                    flash("Description cannot be empty", "danger");
                    $hasError = true;
                }
                if (!(is_numeric($topCriticScoreTemp) && (float) ($topCriticScoreTemp) >= 0 && (float) ($topCriticScoreTemp) <= 100)) {
                    flash("Score must be a number and between 0 to 100 inclusive", "danger");
                    $hasError = true;
                }
                if (!empty($ssURL) && !is_valid_url($ssURL)) {
                    flash("Screenshot image URL invalid", "danger");
                    $hasError = true;
                }
                if (!empty($squareURL) && !is_valid_url($squareURL)) {
                    flash("Square image URL invalid", "danger");
                    $hasError = true;
                }
                if (!empty($url) && !is_valid_url($url)) {
                    flash("Game URL invalid", "danger");
                    $hasError = true;
                }

                if (!$hasError) {
                    foreach ($_POST as $k => $v) {
                        if (!in_array($k, ["id", "name", "publisher", "developer", "description", "topCriticScore", "firstReleaseDate", "platforms", "genres"])) {
                            unset($_POST[$k]);
                        } else if ($k === "platforms") {
                            $platforms = $_POST["platforms"];
                            unset($_POST["platforms"]);
                        } else if ($k === "genres") {
                            $genres = $_POST["genres"];
                            unset($_POST["genres"]);
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
                        //attempt to apply genre / platforms
                        if (isset($genres)) {
                            $db = getDB();
                            $stmt = $db->prepare("INSERT INTO `GameGenre` (genreID, gameId, is_active) VALUES (:genreID, :gameId, 1) 
                                            ON DUPLICATE KEY UPDATE is_active = !is_active");
                            foreach ($genres as $index => $genreID) {
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
                            unset($_POST["genres"]);
                        }

                        if (isset($platforms)) {
                            $db = getDB();
                            // TODO use the insert function
                            $stmt = $db->prepare("INSERT INTO `PlatformGame` (platformId, gameId, is_active) VALUES (:platformId, :gameId, 1) 
                                            ON DUPLICATE KEY UPDATE is_active = !is_active");
                            foreach ($platforms as $index => $platformId) {
                                try {
                                    $stmt->execute([":platformId" => $platformId, ":gameId" => $id]);
                                    flash("Updated role", "success");
                                } catch (PDOException $e) {
                                    flash(var_export($e->errorInfo, true), "danger");
                                }
                            }
                            unset($_POST["platforms"]);
                        }

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
                            flash("An entry for this game ID already exists", "warning");
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
                flash("Missing valid fields", "danger");
            }
        }
    } else {
        flash("You must provide a id", "warning");
    }
}


// Get active platforms
$platformForm = getRelation("Platforms", []);
// Get active Genres
$genreForm = getRelation("Genres", []);

//TODO handle manual create game // Ethan - ekh3 - 4/22/24
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


            <div class="row">
                <div class="col">
                    <h3>Platforms</h3>
                    <?php foreach ($platformForm as $k => $v) {
                        render_input($v);
                    }
                    ?>
                </div>
                <div class="col">
                    <h3>Genres</h3>
                    <?php foreach ($genreForm as $k => $v) {
                        render_input($v);
                    }
                    ?>
                </div>
            </div>

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
        return true;
        let sc = form.topCriticScore.value;
        let valid = true;
        let idValidation = /^\d{1,9}$/;
        if (!verifyScore(sc)) {
            valid = false;
        }
        if (form.name.value == "") {
            valid = false;
            flash("[Client] Developer is required", "warning");
        }
        if (idValidation.test(form.id.value) && parseInt(form.id.value) > 2147483647) {
            valid = false;
            flash("[Client] ID is required and needs to be positive", "warning");
        }
        if (form.developer.value == "") {
            valid = false;
            flash("[Client] Developer is required", "warning");
        }
        if (form.description.value == "") {
            valid = false;
            flash("[Client] Description is required", "warning");
        }
        if (!verifyDate(form.firstReleaseDate.value)) {
            valid = false;
        }
        if (ssURL != "" && !verifyImageURL(ssURL)) {
            flash("[Client] Screenshot URL is not an image link", "warning");
            valid = false;
        }
        if (squareURL != "" && !verifyImageURL(squareURL)) {
            flash("[Client] Square URL is not an image link", "warning");
            valid = false;
        }
        if (url != "" && !verifyURL(url)) {
            flash("[Client] Game URL is not a link", "warning");
            valid = false;
        }
        return valid;
    }
</script>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>