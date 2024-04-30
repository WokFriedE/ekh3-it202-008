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
    echo "<script>history.back()</script>";
}

$db = getDB();
$query = "UPDATE `Completed_Games` SET is_active = 1  WHERE userId = :id";
try {
    $stmt = $db->prepare($query);
    $stmt->execute([":id" => $id]);
    flash("Toggled record with id $id", "success");
} catch (Exception $e) {
    error_log("Error deleting game $id" . var_export($e, true));
    flash("Error deleting record", "danger");
}

// die(header("Location: " . get_url("admin/list_games.php")));
echo "<script>history.back()</script>";


// ekh3 - 4/29/24