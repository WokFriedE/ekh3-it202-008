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

$is_active = false;
if (isset($game["is_active"]) && $game["is_active"] == 1) {
    $is_active = true;
}

if (isset($game)) :
    $solved = false;
    if ($game["Completed"] == 1) {
        $solved = true;
    }
?>
    <div class="card mx-auto" style="width: 18rem;">
        <img src="<?php echo ($solved ? se($game, "sqrImgURL", missingURL()) : missingURL()); ?>" class="card-img-top" alt="..." height="285">
        <div class="card-body">
            <?php if (!$is_active) echo "<s>"; ?>
            <h5 class="card-title">Challenge <?php se($game, "id", "Unknown"); ?></h5>
            <?php if (!$is_active) echo "</s>"; ?>
            <div class="card-text">
                <ul class="list-group">
                    <li class="list-group-item">For Date: <?php echo se($game, "date", "Unknown") ?></li>
                    <li class="list-group-item">Name: <?php echo ($solved ? se($game, "name", "Unknown") : "Unsolved"); ?></li>
                    <li class="list-group-item">Attempts: <?php echo ($solved ? se($game, "attempts", "Unknown") : "Unsolved"); ?></li>
                    <li class="list-group-item">Time: <?php echo ($solved ? se($game, "timeTaken", "Unknown") : "Unsolved"); ?></li>
                </ul>

            </div>

            <?php if (!isset($game["user_id"]) || $game["user_id"] === "N/A") : ?>
                <div class="card-body">
                    <?php if ($is_active) : ?>
                        <div class="row">
                            <a href="<?php echo get_url('play_challenge.php?id=' . $game["id"]); ?>" class="card-link mx-1">Try Challenge <?php $solved ? " again" : "" ?></a>
                        </div>
                        <?php if ($solved) : ?>
                            <div class="row">
                                <a href="<?php echo get_url('game_details.php?id=' . $game["gameId"]); ?>" class="card-link mx-1">Game Info</a>
                            </div>
                        <?php endif; ?>
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
<?php endif; ?>