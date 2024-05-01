<?php
// This is an internal API endpoint to receive data and do something with it
// this is not a standalone page
//Note: no nav.php here because this is a temporary stop, it's not a user page
require(__DIR__ . "/../../../lib/functions.php");

if (isset($_GET["query"])) {
    //TODO implement purchase logic (for now it's all free)
    $name = $_GET["query"];
    $db = getDB();
    $query = "SELECT DISTINCT name FROM `Games` WHERE name like :name AND is_active=1 LIMIT 5";
    try {
        $stmt = $db->prepare($query);
        $stmt->execute([":name" => "%$name%"]);
        $r = $stmt->fetchAll();

        if ($r) {
            $ret = [];
            foreach ($r as $key => $value) {
                array_push($ret, $value["name"]);
            }
            $json["response"] = $ret;
            echo json_encode($json);
        } else {
            echo json_encode([]);
        }
    } catch (PDOException $e) {
        error_log("Error purchasing broker: " . var_export($e, true));
    }
}

// Ekh3- 4/30/24