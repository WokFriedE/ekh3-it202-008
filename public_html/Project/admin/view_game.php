<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}
?>

<?php
// Ethan Ho - ekh3 - 4/21/24
// alerts the user if the URL does not exist 
if (isset($_GET["NoURL"])) {
    flash("No URL Exists", "warning");
}

$id = se($_GET, "id", -1, false);
$fetch = se($_GET, "fetch", -1, false);


$game = [];
if ($id > -1) {
    $tempGame = selectGameInfo($id);
    if (empty($tempGame)) {
        flash("Error: getting game", "danger");
        error_log("ERROR VIEW_GAME: Game is empty");
        redirect("/admin/list_games.php");
    } else {
        $game = $tempGame;
    }
} else {
    flash("Invalid id passed", "danger");
    redirect("/admin/list_games.php");
}

if (is_null($game["firstReleaseDate"]) && $game["is_api"] == 1) {
    redirect("lazy_load_game.php?id=" . $id);
}


foreach ($game as $key => $value) {
    if (is_null($value)) {
        $game[$key] = "N/A";
    }
    // Sets a URL for a null URL
    if ($key === "url") {
        $game[$key] = get_url("admin/view_game.php?id=") . $id . "&NoURL";
    }
}

//TODO handle manual create stock
?>
<div class="container-fluid center">
    <h3>Game: <?php se($game, "name", "Unknown"); ?> </h3>
    <div>
        <a href="javascript:history.go(-1)" class="btn btn-secondary mb-3">Back</a>
        <a href=" <?php echo get_url("admin/edit_game.php?id=") . $id; ?>" class="btn btn-secondary mb-3">Edit</a>
        <a href=" <?php echo get_url("admin/delete_game.php?id=") . $id; ?>" class="btn btn-secondary mb-3">Delete</a>
    </div>
    <div class="row justify-content-md-center">
        <div class="card mx-3" style="width: 18rem;">
            <img src=<?php se($game, "sqrImgURL", "Unknown"); ?> class="card-img-top" alt="Image of Game">
            <div class="card-body">
                <h5 class="card-title"><a class="custLink" href=<?php se($game, "url", ""); ?> target="_blank"> <?php se($game, "name", "Unknown"); ?> </a> </h5>
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
        <div class="card mx-3" style="width: 18rem;">
            <div class="card-body">
                <?php if (isset($game["Genres"]) && !empty($game["Genres"])) : ?>
                    <h5 class="card-title">Genres</h5>
                    <div class="card-text">
                        <ul class="list-group">
                            <?php
                            foreach ($game["Genres"] as $key => $value) {
                                echo "<li class=\"list-group-item\">" . $value . "</li>";
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (isset($game["Platforms"]) && !empty($game["Platforms"])) : ?>
                    <h5 class="card-title">Platforms</h5>
                    <div class="card-text">
                        <ul class="list-group">
                            <?php
                            foreach ($game["Platforms"] as $key => $value) {
                                echo "<li class=\"list-group-item\">" . $value . "</li>";
                            }
                            ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row justify-content-md-center">
        <?php if (isset($game["screenshotImgURL"]) && !empty($game["screenshotImgURL"])) : ?>
            <div class="card m-3" style="width: 30rem;">
                <div class="card-body">
                    <h5 class="card-title">Screenshot</h5>
                    <img src=<?php se($game, "screenshotImgURL", "Unknown"); ?> class="card-img-top" alt="Image of Game">
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="row justify-content-md-center">
        <?php if (isset($game["description"]) && !empty($game["description"])) : ?>
            <div class="card mx-10">
                <div class=" card-body">
                    <h5 class="card-title">Description</h5>
                    <li class="list-group-item"><?php se($game, "description", "Unknown"); ?></li>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>