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
    $result = map_popular_data($result, 1);

    try {
        $opts = ["debug" => true, "update_duplicate" => true,  "columns_to_update" => []];
        $result = insert("Games", $result, $opts);

        if (!$result) {
            flash("Unhandled Error", "warning");
        } else {
            flash("Created record with id " . var_export($result, true), "success");
        }
    } catch (InvalidArgumentException $e1) {
        error_log("Invalid arg" . var_export($e1, true));
        flash("Invalid data passed", "danger");
    } catch (PDOException $e2) {
        if ($e2->errorInfo[1] == 1062) {
            flash("An entry for this game already exists for today", "warning");
        } else {
            error_log("Database error" . var_export($e2, true));
            flash("Database error", "danger");
        }
    } catch (Exception $e3) {
        error_log("Invalid data records" . var_export($e3, true));
        flash("Invalid data records", "danger");
    }
}

if (isset($_GET['gameId'])) {
    // $id = se($_GET, "gameId", "", false);
    // $result = fetch_game($id);
    $result = fetch_gameJSON();
    $result = map_game_data($result);
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
    <pre>
        <?php echo var_dump($result);
        ?>
    </pre>
</div>