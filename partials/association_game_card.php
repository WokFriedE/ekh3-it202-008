<?php
if (!isset($game)) {
    error_log("Using game partial without data");
    flash("Dev Alert: game called without data", "danger");
}
?>

<?php
$is_admin = false;
if (has_role("Admin")) {
    $is_admin = true;
}

$solved = false;
if (isset($game["Completed"]) && $game["Completed"] == 1) {
    $solved = true;
}

$is_admin_view = false;
if (isset($game["adminView"]) && $game["adminView"] == "true") {
    $is_admin_view = true;
    $solved = true;
}

$is_active = false;
if (isset($game["is_active"]) && $game["is_active"] == 1) {
    $is_active = true;
}

if (isset($game)) :
?>
    <div class="card-group">
        <div class="card mx-auto" style="width: 18rem;">
            <img src="<?php echo se($game, "sqrImgURL", missingURL()); ?>" class="card-img-top" alt="..." height="285">
            <div class="card-body">
                <?php if (!$is_active) echo "<s>"; ?>
                <h5 class="card-title">Challenge <?php se($game, "id", "Unknown"); ?></h5>
                <?php if (!$is_active) echo "</s>"; ?>
                <div class="card-text">
                    <ul class="list-group">
                        <li class="list-group-item">For Date: <?php echo se($game, "date", "Unknown") ?></li>
                        <li class="list-group-item">Name: <?php echo (se($game, "name", "Unknown")); ?></li>
                        <!-- <li class="list-group-item">Attempts: <?php echo ($solved ? se($game, "attempts", "Unknown") : "Unsolved"); ?></li>
                        <li class="list-group-item">Time: <?php echo ($solved ? se($game, "timeTaken", "Unknown") : "Unsolved"); ?></li> -->
                    </ul>
                </div>
                <div class="card-body">
                    <?php if ($is_active) : ?>
                        <div class="row">
                            <a href="<?php echo get_url('play_challenge.php?id=' . $game["id"]); ?>" class="card-link mx-1">Try Challenge <?php $solved ? " again" : "" ?></a>
                        </div>
                        <div class="row">
                            <a href="<?php echo get_url('admin/view_game.php?id=' . $game["gameId"]); ?>&card" class="card-link mx-1">Game Info</a>
                        </div>
                    <?php endif; ?>
                    <?php if ($is_admin) : ?>
                        <div class="row">
                            <a href="<?php echo get_url('admin/delete_daily.php?id=' . $game["id"]); ?>" class="card-link mx-1"><?php echo $is_active ? "Disable" : "Enable"; ?> Challenge</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="card-body">
                    <div class="bg-warning text-dark text-center">game not available</div>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <?php if (isset($game["UserDone"])) : ?>
                <h5 class="mx-1">Successful Users (<?php echo count($game["UserDone"]); ?>)</h5>
                <!-- The overflow was used to add scrollable -->
                <ul class="list-group overflow-auto">
                    <?php foreach ($game["UserDone"] as $uid => $username) : ?>
                        <li class="list-group-item">
                            <a class="btn custBtn" href="<?php echo get_url("admin/delete_user_associations.php?id=" . $uid . "&dailyGame=" . $game["id"]) ?>">Remove</a>
                            <a class="card-link" style="font-size: 1.25em;" href="<?php echo get_url("profile.php?id=" . $uid) ?>"><?php echo $username ?> </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>