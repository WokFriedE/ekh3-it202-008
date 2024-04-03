<!-- 
    // allows u to get the info for specific email + any other info and can store in cookie
    //unset the password hash so it cannot be used outside of the context
    // use the same salt -> hash the current password and test it to the hash, true if get it works
    //where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1"); // makes sure the roles are active and alive
-->

<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<!-- ekh3 - 4/1/24 -->
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email/Username</label>
        <input type="email" name="email" required />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <input type="submit" value="Login" />
</form>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success

        //ekh3 - 4/1/24
        let cred = form.email.value;
        let isValid = true;

        if ((/^.*@.*$/.test(cred))) {
            if (!verifyEmail(cred))
                isValid = false;
        } else {
            if (!verifyUsername(cred))
                isValid = false;
        }
        //TODO update clientside validation to check if it should
        //valid email or username
        if (!verifyPassword(form.password.value)) {
            isValid = false;
        }

        return isValid;
    }

    // end JS validation
</script>
<?php
//TODO 2: add PHP Code
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);

    //ekh3 - 

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty");
        $hasError = true;
    }
    if (str_contains($email, "@")) {
        //sanitize
        //$email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $email = sanitize_email($email);
        //validate
        /*if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash("Invalid email address");
            $hasError = true;
        }*/
        if (!is_valid_email($email)) {
            flash("Invalid email address"); //edit
            $hasError = true;
        }
    } else {
        if (!is_valid_username($email)) {
            flash("Invalid username"); //edit
            $hasError = true;
        }
    }
    if (empty($password)) {
        flash("password must not be empty");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short");
        $hasError = true;
    }
    if (!$hasError) {
        //flash("Welcome, $email");
        //TODO 4
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password from Users 
        where email = :email or username = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        //flash("Weclome $email");
                        $_SESSION["user"] = $user; //sets our session data from db
                        //lookup potential roles
                        $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                        JOIN UserRoles on Roles.id = UserRoles.role_id 
                        where UserRoles.user_id = :user_id and Roles.is_active = 1 
                        and UserRoles.is_active = 1");
                        $stmt->execute([":user_id" => $user["id"]]);
                        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); //fetch all since we'll want multiple
                        //save roles or empty array
                        if ($roles) {
                            $_SESSION["user"]["roles"] = $roles; //at least 1 role
                        } else {
                            $_SESSION["user"]["roles"] = []; //no roles
                        }
                        flash("Welcome, " . get_username());
                        die(header("Location: home.php"));
                    } else {
                        flash("Invalid Username/Email or Password");
                    }
                } else {
                    flash("Invalid Username/Email or Password");
                }
            }
        } catch (Exception $e) {
            flash("<pre>An error has occured, please try again.</pre>");
            error_log("login: " . var_export($e, true));
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
