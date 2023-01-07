<?php
$errors = [];
$errors["username"] = false;
$errors["password"] = false;
$errors["connection"] = false;
$errors["nosuchuser"] = false;
$logged = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST["username"]) 
        && !empty($_POST["password"])
        && strlen(trim($_POST["username"])) != 0
        && strlen(trim($_POST["password"])) != 0) {
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $uname = $_POST["username"];
        $pass = $_POST["password"];

        $sql = "SELECT * FROM `users` WHERE `username` = ? AND `active` = TRUE";
        $stmt = $db_obj->prepare($sql);
        $stmt->bind_param("s", $uname);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $errors["nosuchuser"] = true;
            } else {
                $row = $result->fetch_assoc();
                if (password_verify($pass, $row["password"])) {
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
    <?php if ($logged) {
        $logged = false;
        header("Refresh: 1, url=mein_profil.php");
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
    <?php if (!isset($_SESSION["username"])) { ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <?php if ($errors["nosuchuser"]) {
                        $errors["nosuchuser"] = false;
                        header("Refresh: 2, url=login.php");
                    ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Login nicht möglich, dieser User existiert nicht oder ist inaktiv!
                        </div>
                    <?php } elseif ($errors["connection"]) {
                        $errors["connection"] = false;
                        header("Refresh: 2, url=login.php");
                    ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Login nicht möglich aufgrund eines Fehlers mit der Datenbank!
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
                        <button type="submit" class="btn btn-primary mt-3">Login</button>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>
</body>

</html>