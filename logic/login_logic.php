<?php
$errors = [];
$errors["username"] = false;
$errors["password"] = false;
$errors["connection"] = false;
$errors["nosuchuser"] = false;
$logged = false;

// if user clicks on login-button we execute this code
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["login"])
) {
    if (
        // errror handling to check if input not empty and if password fulfills required length
        !empty($_POST["username"])
        && !empty($_POST["password"])
        && strlen(trim($_POST["username"])) != 0
        && strlen(trim($_POST["password"])) >= 8
    ) {
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $uname = htmlspecialchars($_POST["username"], ENT_QUOTES);
        $pass = htmlspecialchars($_POST["password"], ENT_QUOTES);

        // prepared select-query to ensure protection against SQL-Injections
        // we put the AND-constraint to make sure the user is an active one
        $sql = "SELECT * FROM `users` WHERE `username` = ? AND `active` = TRUE";
        $stmt = $db_obj->prepare($sql);
        $stmt->bind_param("s", $uname);
        if ($stmt->execute()) {
            // if query is executed we fetch the result
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $errors["nosuchuser"] = true;
            } else {
                // and fetch the row of the result
                $row = $result->fetch_assoc();
                // we verify if given password matches the hashed password in the database
                if (password_verify($pass, $row["password"])) {
                    // if password matches then we set the session with the users information from the database
                    $_SESSION["id"] = $row["id"];
                    $_SESSION["admin"] = $row["admin"];
                    $_SESSION["username"] = $row["username"];
                    $_SESSION["useremail"] = $row["useremail"];
                    $_SESSION["formofadress"] = $row["formofadress"];
                    $_SESSION["firstname"] = $row["firstname"];
                    $_SESSION["secondname"] = $row["secondname"];
                    $_SESSION["profilepic"] = $row["path"];
                    $logged = true;
                } else {
                    $errors["password"] = true;
                }
            }
        } else {
            $errors["connection"] = true;
        }
        $stmt->close();
        $db_obj->close();
    } else {
        $errors["username"] = true;
        $errors["password"] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/login_logic.php')) {
        header("Location: ../index.php");
    } ?>
    <?php if ($logged && isset($_SESSION["admin"])) {
        $logged = false;
        if ($_SESSION["admin"]) {
            header("Refresh: 1, url=admin_dashboard.php");
        } elseif (!$_SESSION["admin"]) {
            header("Refresh: 1, url=mein_profil.php");
        }
    ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <div class="alert alert-primary text-center" role="alert">
                        Willkommen <?php echo $_SESSION["firstname"] ?>!
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php
    if (!isset($_SESSION["username"])) { ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <?php if ($errors["nosuchuser"]) {
                        $errors["nosuchuser"] = false;
                        header("Refresh: 3, url=login.php");
                    ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Login nicht möglich, dieser User existiert nicht oder ist inaktiv, kontaktieren Sie das Support-Team!
                        </div>
                    <?php } elseif ($errors["connection"]) {
                        $errors["connection"] = false;
                        header("Refresh: 2, url=login.php");
                    ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Login nicht möglich, versuchen Sie es später nocheinmal!
                        </div>
                    <?php } ?>
                </div>
                <form method="POST">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="exampleInputEmail1" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control <?php if ($errors['username']) echo 'is-invalid'; ?>" id="exampleInputEmail1">
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="password" class="form-label">Passwort</label>
                        <input type="password" name="password" class="form-control <?php if ($errors['password']) echo 'is-invalid'; ?>" id="password">
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        Noch nicht registriert? Klicken Sie <a href="registrierung.php">hier!</a>
                    </div>
                    <div class="col-sm-10 offset-sm-1 text-center">
                        <button type="submit" name="login" class="btn btn-primary mt-3">Login</button>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>
</body>

</html>