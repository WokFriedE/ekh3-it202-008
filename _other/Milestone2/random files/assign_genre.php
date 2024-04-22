<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: $BASE_PATH" . "/home.php"));
}
//attempt to apply
if (isset($_POST["games"]) && isset($_POST["genres"])) {
    $user_ids = $_POST["games"]; //se() doesn't like arrays so we'll just do this
    $role_ids = $_POST["genres"]; //se() doesn't like arrays so we'll just do this
    if (empty($user_ids) || empty($role_ids)) {
        flash("Both games and genres need to be selected", "warning");
    } else {
        //for sake of simplicity, this will be a tad inefficient
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO GameGenre (genreId, gameId, is_active) VALUES (:uid, :rid, 1) 
        ON DUPLICATE KEY UPDATE is_active = !is_active");
        foreach ($user_ids as $uid) {
            foreach ($role_ids as $rid) {
                try {
                    $stmt->execute([":uid" => $uid, ":rid" => $rid]);
                    flash("Updated role", "success");
                } catch (PDOException $e) {
                    flash(var_export($e->errorInfo, true), "danger");
                }
            }
        }
    }
}

//get active roles
$active_roles = [];
$db = getDB();
$stmt = $db->prepare("SELECT id, name FROM Genres WHERE is_active = 1 LIMIT 10");
try {
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $active_roles = $results;
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}

//search for user by gameName
$games = [];
$gameName = "";
if (isset($_POST["gameName"])) {
    $gameName = se($_POST, "gameName", "", false);
    if (!empty($gameName)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT Games.id, Games.name, 
        (SELECT GROUP_CONCAT(name, ' (' , IF(ur.is_active = 1,'active','inactive') , ')') from 
        GameGenre ur JOIN Genres on ur.genreId = Genres.id WHERE ur.gameId = Games.id) as genres
        from Games WHERE Games.name like :gameName");
        try {
            $stmt->execute([":gameName" => "%$gameName%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($results) {
                $games = $results;
            }
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    } else {
        flash("gameName must not be empty", "warning");
    }
}


?>
<div class="container-fluid">
    <h1>Assign Roles</h1>
    <form method="POST">
        <?php render_input(["type" => "search", "name" => "gameName", "placeholder" => "Game Search", "value" => $gameName]);/*lazy value to check if form submitted, not ideal*/ ?>
        <?php render_button(["text" => "Search", "type" => "submit"]); ?>
    </form>
    <form method="POST">
        <?php if (isset($gameName) && !empty($gameName)) : ?>
            <input type="hidden" name="gameName" value="<?php se($gameName, false); ?>" />
        <?php endif; ?>
        <table class="table">
            <thead>
                <th>games</th>
                <th>Roles to Assign</th>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <table class="table">
                            <?php foreach ($games as $user) : ?>
                                <tr>
                                    <td>
                                        <label for="user_<?php se($user, 'id'); ?>"><?php se($user, "gameName"); ?></label>
                                        <input id="user_<?php se($user, 'id'); ?>" type="checkbox" name="games[]" value="<?php se($user, 'id'); ?>" />
                                    </td>
                                    <td><?php se($user, "roles", "No Roles"); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td>
                        <?php foreach ($active_roles as $role) : ?>
                            <div>
                                <label for="role_<?php se($role, 'id'); ?>"><?php se($role, "name"); ?></label>
                                <input id="role_<?php se($role, 'id'); ?>" type="checkbox" name="roles[]" value="<?php se($role, 'id'); ?>" />
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php render_button(["text" => "Toggle Roles", "type" => "submit", "color" => "secondary"]); ?>
    </form>
</div>
<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>