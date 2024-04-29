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
    $query = "UPDATE `Genres` SET ";

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

// Get genre information
$genre = [];
if ($id > -1) {
    $r = selectInfo("Genres", $id, ["id", "name"], ["active_only" => false, "debug" => true]);
    if ($r) {
        $genre = $r;
    } else {
        flash("Invalid genre passed", "danger");
        redirect("/admin/list_genres.php");
    }
} else {
    flash("Invalid id passed", "danger");
    redirect("/admin/list_genres.php");
}



if ($genre) {
    $form = [
        ["type" => "text", "name" => "name", "placeholder" => "Name...", "label" => "Name", "rules" => ["required" => "required"]],
    ];
    $keys = array_keys($genre);

    foreach ($form as $k => $v) {
        if (in_array($v["name"], $keys)) {
            $form[$k]["value"] = $genre[$v["name"]];
        }
    }
}

?>
<div class="container-fluid">
    <h3>Edit genre</h3>
    <div>
        <a href="<?php echo get_url("admin/list_genres.php"); ?>" class="btn btn-secondary">Back</a>
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
        return valid;
    }
</script>