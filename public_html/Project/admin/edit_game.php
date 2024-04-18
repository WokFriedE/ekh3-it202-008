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
        if (!in_array($k, ["id", "name", "publisher", "developer", "description", "topCriticScore", "firstReleaseDate", "Platforms", "Genres"])) {
            unset($_POST[$k]);
        }
        $temps = $_POST;
        error_log("Cleaned up POST: " . var_export($temps, true));
    }

    //insert data
    $db = getDB();
    $query = "UPDATE `Games` SET ";

    $params = [];
    //per record
    foreach ($temps as $k => $v) {

        if ($params) {
            $query .= ",";
        }
        //be sure $k is trusted as this is a source of sql injection
        $query .= "$k=:$k";
        $params[":$k"] = $v;
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
            flash(var_export($e->errorInfo, true), "danger");
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
        ["type" => "text", "name" => "publisher", "placeholder" => "Publisher...", "label" => "Publisher", "rules" => ["required" => "required"]],
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

        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update Info"]); ?>
    </form>

    <div class="row">
        <div class="col">
            <form method="POST" onsubmit="return validate(this)">
                <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update platforms"]); ?>
                <?php foreach ($platformForm as $k => $v) {
                    render_input($v);
                } ?>
            </form>
        </div>
        <div class="col">
            <form method="POST" onsubmit="return validate(this)">
                <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update genres"]); ?>
                <?php foreach ($genreForm as $k => $v) {
                    render_input($v);
                } ?>
            </form>
        </div>
    </div>

</div>


<?php

//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>

<script>
    function validate(form) {
        let score = form.topCriticScore.value;
        let valid = true;
        if (!verifyScore(score))
            valid = false;
        if (!form.developer.value)
            valid = false
        if (!form.description.value)
            valid = false
        if (!verifyDate(form.firstReleaseDate.value))
            valid = false
        return valid;
    }
</script>