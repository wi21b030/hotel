<?php
$uploadDir = "uploads/profilepics/";
$errors = [];
$errors["firstname"] = false;
$errors["secondname"] = false;
$errors["useremail"] = false;
$errors["username"] = false;
$errors["password"] = false;
$errors["password2"] = false;
$errors["file"] = false;
$errors["exists"] = false;
$errors["insert"] = false;
$registered = false;

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
    if (empty($_POST["firstname"]) || strlen(trim($_POST["firstname"])) == 0) {
        $errors["firstname"] = true;
    }
    if (empty($_POST["secondname"]) || strlen(trim($_POST["secondname"])) == 0) {
        $errors["secondname"] = true;
    }
    if (empty($_POST["useremail"]) || strlen(trim($_POST["useremail"])) == 0) {
        $errors["useremail"] = true;
    }
    if (!empty($_POST["useremail"]) || strlen(trim($_POST["useremail"])) == 0) {
        $check = test_input($_POST["useremail"]);
        if (!filter_var($check, FILTER_VALIDATE_EMAIL)) {
            $errors["useremail"] = true;
        }
    }
    if (empty($_POST["username"]) || strlen(trim($_POST["username"])) == 0) {
        $errors["username"] = true;
    }
    if (empty($_POST["password"]) || strlen(trim($_POST["password"])) == 0) {
        $errors["password"] = true;
    }
    if (empty($_POST["password2"]) || strlen(trim($_POST["password2"])) == 0) {
        $errors["password2"] = true;
    }
    if ($_POST["password"] !== $_POST["password2"]) {
        $errors["password"] = true;
        $errors["password2"] = true;
    }
}

if (!file_exists($uploadDir)) {
    mkdir($uploadDir);
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["register"])
) {
    if (isset($_FILES["file"]) && !empty($_FILES["file"])) {
        $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
            if (
                !$errors["firstname"]
                && !$errors["secondname"]
                && !$errors["useremail"]
                && !$errors["username"]
                && !$errors["password"]
                && !$errors["password2"]
            ) {
                require_once('config/dbaccess.php');
                $db_obj = new mysqli($host, $user, $password, $database);
                if ($db_obj->connect_error) {
                    $errors["insert"] = true;
                    $db_obj->close();
                    exit();
                }
                $uname = htmlspecialchars($_POST["username"], ENT_QUOTES);
                $pass = htmlspecialchars(password_hash($_POST["password"], PASSWORD_DEFAULT), ENT_QUOTES);
                $mail = htmlspecialchars($_POST["useremail"], ENT_QUOTES);
                $fod = $_POST["formofadress"];
                $fname = htmlspecialchars($_POST["firstname"], ENT_QUOTES);
                $sname = htmlspecialchars($_POST["secondname"], ENT_QUOTES);
                $profilepic = $_FILES["file"]["tmp_name"];
                $path = $uploadDir . $uname . ".jpg";

                $sql = "INSERT INTO `users` (`username`, `password`, `useremail`, `formofadress`, `firstname`, `secondname`, `path`) VALUES (?,?,?,?,?,?,?)";
                $stmt = $db_obj->prepare($sql);
                $stmt->bind_param("sssssss", $uname, $pass, $mail, $fod, $fname, $sname, $path);

                $sql = "SELECT * FROM `users` WHERE `username` = '$uname'";
                $result = $db_obj->query($sql);
                if ($result->num_rows > 0) {
                    $errors["exists"] = true;
                } else {
                    if (
                        $uname === $_POST["username"]
                        && password_verify($_POST["password"], $pass)
                        && password_verify($_POST["password2"], $pass)
                        && $mail === $_POST["useremail"]
                        && $fname === $_POST["firstname"]
                        && $sname === $_POST["secondname"]
                    ) {
                        if ($stmt->execute()) {
                            $sql = "SELECT * FROM `users` WHERE `username` = '$uname'";
                            $result = $db_obj->query($sql);
                            if ($result->num_rows == 1) {
                                $row = $result->fetch_assoc();
                                if (move_uploaded_file($profilepic, $path)) {
                                    $_SESSION["id"] = $row["id"];
                                    $_SESSION["admin"] = $row["admin"];
                                    $_SESSION["username"] = $row["username"];
                                    $_SESSION["useremail"] = $row["useremail"];
                                    $_SESSION["formofadress"] = $row["formofadress"];
                                    $_SESSION["firstname"] = $row["firstname"];
                                    $_SESSION["secondname"] = $row["secondname"];
                                    $_SESSION["profilepic"] = $row["path"];
                                    $registered = true;
                                } else {
                                    $errors["insert"] = true;
                                }
                            } else {
                                $errors["insert"] = true;
                            }
                        } else {
                            $errors["insert"] = true;
                        }
                    } else {
                        $errors["insert"] = true;
                    }
                }
                $stmt->close();
                $db_obj->close();
            }
        } else {
            $errors["file"] = true;
        }
    } else {
        $errors["file"] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung</title>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 offset-sm-3 text-center">
                <?php if ($errors["exists"]) {
                    $errors["exists"] = false;
                    header("Refresh: 2, url=registrierung.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Registrierung nicht möglich, Username bereits vergeben!
                    </div>
                <?php } elseif ($errors["insert"]) {
                    $errors["insert"] = false;
                    header("Refresh: 2, url=registrierung.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Registrierung nicht möglich!
                    </div>
                <?php } elseif ($registered) {
                    $registered = false;
                    header("Refresh: 1, url=mein_profil.php");
                ?>
                    <div class="alert alert-primary text-center" role="alert">
                        Registrierung erfolgreich, willkommen <?php echo $_SESSION["firstname"] ?>!
                    </div>
                <?php } ?>
                <?php if (!isset($_SESSION["username"])) { ?>
                    <form enctype="multipart/form-data" method="POST">
                        <div class="mb-3">
                            <label for="formofadress" class="form-label">Anrede</label>
                            <select class="form-select" name="formofadress" aria-label="Default select example" required>
                                <option value="1">Herr</option>
                                <option value="2">Frau</option>
                                <option value="3">Keine Angabe</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="firstname" class="form-label">Vorname</label>
                            <input type="text" class="form-control <?php if ($errors['firstname']) echo 'is-invalid'; ?>" name="firstname" id="firstname">
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputPassword1" class="form-label">Nachname</label>
                            <input type="text" class="form-control <?php if ($errors['secondname']) echo 'is-invalid'; ?>" name="secondname" id="exampleInputPassword1">
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Email</label>
                            <input type="email" class="form-control <?php if ($errors['useremail']) echo 'is-invalid'; ?>" name="useremail" id="exampleInputEmail1">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control <?php if ($errors['username']) echo 'is-invalid'; ?>" name="username" id="username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort</label>
                            <input type="password" class="form-control <?php if ($errors['password']) echo 'is-invalid'; ?>" name="password" id="password" minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Passwort erneut eingeben</label>
                            <input type="password" class="form-control <?php if ($errors['password2']) echo 'is-invalid'; ?>" name="password2" id="password" minlength="8">
                        </div>
                        <div class="mb-3">
                            <label for="formFile" class="form-label">Profilbild</label>
                            <input class="form-control <?php if ($errors['file']) echo 'is-invalid'; ?>" name="file" type="file" id="formFile" accept="image/*">
                        </div>
                        <div class="mb-3 form-check ">
                            <input type="checkbox" class="form-check-input" id="exampleCheck1" required>
                            <label class="form-check-label" name="agree" for="exampleCheck1">Ich akzeptiere die Nutzungsbedingungen!</label>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="register" value="register">
                            <button type="submit" class="btn btn-primary">Registrieren</button>
                        </div>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</body>

</html>