<!-- TODO passing the post seems to be broken -->

<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    redirect("home.php");
}
?>



<?php
$id = se($_GET, "id", -1, false);
if (isset($_POST["name"])) {
    foreach ($_POST as $k => $v) {
        if (!in_array($k, ["id", "name", "shortName"])) {
            unset($_POST[$k]);
        }
        $temps = $_POST;
        error_log("Cleaned up POST: " . var_export($temps, true));
    }

    //insert data
    $db = getDB();
    $query = "UPDATE `Platforms` SET ";

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

// Get game information
$game = [];
if ($id > -1) {
    $r = selectInfo("Platforms", $id, ["id", "name", "shortName"], ["active_only" => false, "debug" => true]);
    if ($r) {
        $game = $r;
    } else {
        flash("Invalid Platform passed", "danger");
        redirect("/admin/list_platforms.php");
    }
} else {
    flash("Invalid id passed", "danger");
    redirect("/admin/list_platforms.php");
}



if ($game) {
    $form = [
        ["type" => "text", "name" => "name", "placeholder" => "Name...", "label" => "Name", "rules" => ["required" => "required"]],
        ["type" => "text", "name" => "shortName", "placeholder" => "Short Name...", "label" => "Short Name", "rules" => ["required" => "required"]]
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
        <a href="<?php echo get_url("admin/list_platforms.php"); ?>" class="btn btn-secondary">Back</a>
    </div>
    <form method="POST" onsubmit="return validate(this)">
        <?php foreach ($form as $k => $v) {
            render_input($v);
        } ?>

        <?php render_button(["text" => "Search", "type" => "submit", "text" => "Update Info"]); ?>
    </form>

</div>


<?php

//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>

<script>
    function validate(form) {
        let valid = true;

        if (form.name.value == "") {
            valid = false
            flash("[Client] Name is required", "warning")
        }
        if (form.shortName.value == "") {
            valid = false
            flash("[Client] Short name is required", "warning")
        }
        return valid;
    }
</script>