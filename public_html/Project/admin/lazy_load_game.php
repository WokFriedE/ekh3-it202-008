<?php

session_start();
require(__DIR__ . "/../../../lib/functions.php");
if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}

$id = se($_GET, "id", -1, false);
if ($id < 1) {
    flash("Invalid id passed to delete", "danger");
    redirect("/admin/list_games.php");
}

try {
    $res = fetch_game($id);
    insertGame(map_game_data($res));
    flash("Lazy Loaded", "success");
} catch (Exception $e) {
    error_log("Error adding record: " . var_export($e, true));
    flash("Error adding data", "danger");
}

redirect("/admin/view_game.php?id=" . $id);
