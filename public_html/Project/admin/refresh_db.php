<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

$result = "hello";

if (isset($_GET['popular'])) {
    // $result = fetch_popular();
    $result = fetch_popularJSON();
}

if (isset($_GET['gameId'])) {
    $id = se($_GET, "", "", false);
    // $result = fetch_game($id);
}


?>

<!-- HTML -->
<h1>Simple Refresh / test</h1>
<div class="container-fluid">
    <form onsubmit="return attempt(this)" method="GET">
        <?php render_input(["type" => "number", "id" => "gameId", "name" => "gameId", "label" => "gameId", "rules" => ["required" => true]]); ?>
        <?php render_button(["text" => "Get Game", "type" => "submit"]); ?>
    </form>
    <form onsubmit="return getQuery(this)" method="GET">
        <a href="?popular" class="btn btn-primary">Popular</a>
    </form>
    <h2>Response</h2>
    <?php echo var_dump($result); ?>
</div>