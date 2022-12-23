<?php
$errors = [];
$errors["username"] = false;
$errors["password"] = false;
$errors["connection"] = false;
$errors["nosuchuser"] = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST["username"]) && !empty($_POST["password"])) {

        require_once ('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
            $db_obj->close();
            exit();
        }
        $uname = $_POST["username"];
        $pass = $_POST["password"];

        $sql = "SELECT * FROM `users` WHERE `username` = '$uname' AND `active` = TRUE";
        $result = $db_obj->query($sql);
        if ($result->num_rows == 0) {
            $errors["nosuchuser"] = true;
            header("Refresh: 2, url=login.php");
            $db_obj->close();
            exit();
        } else {
            $row = $result->fetch_assoc(); 
            if (password_verify($pass,$row["password"])){
                $_SESSION["id"] = $row["id"];
                $_SESSION["username"] = $uname;
                $_SESSION["admin"] = $row["admin"];
                header("Location: login.php");
            } else {
                $errors["password"] = true;
                $db_obj->close();
                exit();
            }
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
    <?php if (!isset($_SESSION["username"])) { ?>
        <div class="container-fluid">
        <?php if($errors["nosuchuser"]) { 
            $errors["nosuchuser"] = false;
            header("Refresh: 2, url=login.php");    
        ?>
            <div class="alert alert-danger text-center" role="alert">
                Login nicht möglich, dieser User existiert nicht oder ist inaktiv!
            </div>
        <?php } elseif ($errors["connection"]){ 
            $errors["connection"] = false;
            header("Refresh: 2, url=login.php"); 
        ?>
             <div class="alert alert-danger text-center" role="alert">
                Login nicht möglich aufgrund eines Fehlers mit der Datenbank!
            </div>
        <?php }?>
            <form action="login.php" method="POST">
                <div class="row">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="exampleInputEmail1" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control <?php if ($errors['username']) echo 'is-invalid'; ?>" id="exampleInputEmail1">
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="password" class="form-label">Passwort</label>
                        <input type="password" name="password" class="form-control <?php if ($errors['password']) echo 'is-invalid'; ?>" id="password">
                    </div>
                    <div class="col-sm-10 offset-sm-1 text-center">
                        <button type="submit" class="btn btn-primary mt-3">Login</button>
                    </div>
                </div>
            </form>
        </div>
    <?php } ?>
</body>

</html>