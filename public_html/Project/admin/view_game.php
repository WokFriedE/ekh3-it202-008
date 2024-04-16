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
$fetch = se($_GET, "fetch", -1, false);

// $diff = abs(round(strtotime(date("Y-m-d")) - strtotime($game["modified"]) / (60 * 60 * 24)));
// echo $diff;

$game = [];
if ($id > -1) {
    //fetch
    $db = getDB();
    $query = "SELECT name, publisher, developer, description, topCriticScore, sqrImgURL, firstReleaseDate, created, url, modified FROM `Games` WHERE id = :id";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":id" => $id]);
        $r = $stmt->fetch();
        if ($r) {
            $game = $r;
        }
    } catch (PDOException $e) {
        error_log("Error fetching record: " . var_export($e, true));
        flash("Error fetching record", "danger");
    }
} else {
    flash("Invalid id passed", "danger");
    die(header("Location:" . get_url("admin/list_games.php")));
}

try {
    if (is_null($game["firstReleaseDate"])) {
        $res = fetch_game($id);
        insertGame(map_game_data($res));
        flash("Please reload to see all data", "success");
    }
} catch (Exception $e) {
    error_log("Error adding record: " . var_export($e, true));
    flash("Error adding data", "danger");
}


foreach ($game as $key => $value) {
    if (is_null($value)) {
        $game[$key] = "N/A";
    }
}

//TODO handle manual create stock
?>
<div class="container-fluid">
    <h3>game: <?php se($game, "name", "Unknown"); ?></h3>
    <div>
        <a href="<?php echo get_url("admin/list_games.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    <div class="card mx-auto" style="width: 18rem;">
        <img src=<?php se($game, "sqrImgURL", "Unknown"); ?> class="card-img-top" alt="Image of Game">
        <div class="card-body">
            <h5 class="card-title"><?php se($game, "name", "Unknown"); ?></h5>
            <div class="card-text">
                <ul class="list-group">
                    <li class="list-group-item">Publisher: <?php se($game, "publisher", "Unknown"); ?></li>
                    <li class="list-group-item">Developer: <?php se($game, "developer", "Unknown"); ?></li>
                    <li class="list-group-item">Top Critic Score: <?php se($game, "topCriticScore", "Unknown"); ?></li>
                    <li class="list-group-item">Release Date: <?php se($game, "firstReleaseDate", "Unknown"); ?></li>
                </ul>

            </div>
        </div>
    </div>

</div>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>