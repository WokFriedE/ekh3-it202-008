<!-- TODO passing the post seems to be broken -->

<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
?>



<?php
$id = se($_GET, "id", -1, false);
if (isset($_POST["name"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["id", "name", "publisher", "developer", "description", "topCriticScore", "firstReleaseDate", "platforms", "genres"])) {
            unset($_POST[$k]);
        }
        $temps = $_POST;
        error_log("Cleaned up POST: " . var_export($temps, true));
    }
    $hasError = false;
    if (isset($_POST["name"]) && isset($_POST["developer"]) && isset($_POST["description"]) && isset($_POST["topCriticScore"]) && isset($_POST["firstReleaseDate"])) {
        $nameTemp = se($_POST, "name", "", false);
        $developerTemp = se($_POST, "developer", "", false);
        $descriptionTemp = se($_POST, "description", "", false);
        $topCriticScoreTemp = se($_POST, "topCriticScore", "", false);
        $firstReleaseDateTemp = se($_POST, "firstReleaseDate", "", false);
        $ssURL = se($_POST, "screenshotImgURL", "", false);
        $squareURL = se($_POST, "sqrImgURL", "", false);
        $url = se($_POST, "url", "", false);

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
            //insert data
            $db = getDB();
            $query = "UPDATE `Games` SET ";

            $params = [];
            //per record
            foreach ($temps as $k => $v) {
                if ($k === "platforms" || $k === "genres")
                    continue;
                if ($params) {
                    $query .= ",";
                }
                //be sure $k is trusted as this is a source of sql injection
                $query .= "$k=:$k";
                $params[":$k"] = $v;
            }


            //attempt to apply
            if (isset($_POST["genres"])) {
                $db = getDB();
                $genreIDs = $_POST["genres"];
                $stmt = $db->prepare("INSERT INTO `GameGenre` (genreID, gameId, is_active) VALUES (:genreID, :gameId, 1) 
    ON DUPLICATE KEY UPDATE is_active = !is_active");
                foreach ($genreIDs as $index => $genreID) {
                    try {
                        $stmt->execute([":genreID" => $genreID, ":gameId" => $id]);
                        flash("Updated role", "success");
                    } catch (PDOException $e) {
                        flash(var_export($e->errorInfo, true), "danger");
                    }
                }
                unset($_POST["genres"]);
            }

            if (isset($_POST["platforms"])) {
                $db = getDB();
                // TODO use the insert function
                $platformIDs = $_POST["platforms"];
                $stmt = $db->prepare("INSERT INTO `PlatformGame` (platformId, gameId, is_active) VALUES (:platformId, :gameId, 1) 
    ON DUPLICATE KEY UPDATE is_active = !is_active");
                foreach ($platformIDs as $index => $platformId) {
                    try {
                        $stmt->execute([":platformId" => $platformId, ":gameId" => $id]);
                        flash("Updated role", "success");
                    } catch (PDOException $e) {
                        flash(var_export($e->errorInfo, true), "danger");
                    }
                }
                unset($_POST["platforms"]);
            }


            $query .= " WHERE id = :id";
            $params[":id"] = $id;
            error_log("Query: " . $query);
            error_log("Params: " . var_export($params, true));
            try {
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                flash("Updated record ", "success");
            } catch (PDOException $e) {
                error_log("Something broke with the query" . var_export($e, true));
                flash("An error occurred", "danger");
            }
        }
    }
}

// Get game information
$game = [];
if ($id > -1) {
    $r = selectGameInfo($id, true);

    if ($r) {
        $game = $r;
    } else {
        flash("Invalid Game passed", "danger");
        die(header("Location:" . get_url("admin/list_games.php")));
    }
} else {
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_games.php")));
}


if ($game) {
    $form = [
        ["type" => "text", "name" => "name", "placeholder" => "Name...", "label" => "Name", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "publisher", "placeholder" => "Publisher (optional)", "label" => "Publisher"],
        ["type" => "text", "name" => "developer", "placeholder" => "Developer...", "label" => "Developer", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "description", "placeholder" => "Description...", "label" => "Description", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "topCriticScore", "placeholder" => "Critic Score...", "label" => "Critic Score", "rules" => ["required" => "required"]],
        ["type" => "date", "name" => "firstReleaseDate", "placeholder" => "Release Date...", "label" => "Release Date", "rules" => ["required" => "required"]],
        ["type" => "url", "name" => "sqrImgURL", "placeholder" => "Square Image URL (optional)", "label" => "Square Image"],
        ["type" => "url", "name" => "screenshotImgURL", "placeholder" => "Screenshot Image URL (optional)", "label" => "Screenshot Image"],
        ["type" => "url", "name" => "url", "placeholder" => "Game Page URL (optional)", "label" => "Game Page URL"]
    ];
    $keys = array_keys($game);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $game[$v["name"]];
        }
    }
}

// Get active platforms
$platformForm = getRelation("Platforms", $game);
// Get active Genres
$genreForm = getRelation("Genres", $game);

?>
<div class="container-fluid">
    <h3>Edit Game</h3>
    <div>
        <a href="<?php echo get_url("admin/list_games.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    <form method="POST" onsubmit="return validate(this)">
        <?php foreach ($form as $k => $v) {
            render_input($v);
        } ?>


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
        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update Info"]); ?>
    </form>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>

<script>
    function validate(form) {
        let sc = form.topCriticScore.value;
        let ssURL = form.screenshotImgURL.value;
        let squareURL = form.sqrImgURL.value;
        let url = form.url.value;

        let valid = true;
        if (!verifyScore(sc)) {
            valid = false;
        }
        if (form.name.value == "") {
            valid = false;
            flash("[Client] Name is required", "warning");
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