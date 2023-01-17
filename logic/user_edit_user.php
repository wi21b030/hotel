<?php
// Declare variables for storing errors and status of the update
$uploadDir = "uploads/profilepics/";
$errors = [];
$errors["firstname"] = false;
$errors["secondname"] = false;
$errors["useremail"] = false;
$errors["username"] = false;
$errors["password"] = false;
$errors["passwordold"] = false;
$errors["file"] = false;
$errors["connection"] = false;
$errors["update"] = false;
$updated = false;

// Check if the uploads/profilepics/ directory exists, and if not create it
if (!file_exists($uploadDir)) {
    mkdir($uploadDir);
}
// function to sanitize input
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
// check if form was submitted and update button was pressed
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update"])
) {
    if (empty($_POST["firstname"]) || !isset($_POST["firstname"]) || strlen(trim($_POST["firstname"])) == 0) {
        $errors["firstname"] = true;
    }
    if (empty($_POST["secondname"]) || !isset($_POST["secondname"]) || strlen(trim($_POST["secondname"])) == 0) {
        $errors["secondname"] = true;
    }
    if (empty($_POST["useremail"]) || !isset($_POST["useremail"]) || strlen(trim($_POST["useremail"])) == 0) {
        $errors["useremail"] = true;
    }
    if (!empty($_POST["useremail"])) {
        $check = test_input($_POST["useremail"]);
        if (!filter_var($check, FILTER_VALIDATE_EMAIL)) {
            $errors["useremail"] = true;
        }
    }
    if (empty($_POST["username"]) || !isset($_POST["username"])  || strlen(trim($_POST["username"])) == 0) {
        $errors["username"] = true;
    }
    if (empty($_POST["passwordold"]) || !isset($_POST["passwordold"])  || strlen(trim($_POST["passwordold"])) == 0 || strlen(trim($_POST["passwordold"])) < 8) {
        $errors["passwordold"] = true;
    }
    if (empty($_POST["password"]) || !isset($_POST["password"])  || strlen(trim($_POST["password"])) == 0 || strlen(trim($_POST["password"])) < 8) {
        $errors["password"] = true;
    }
    if (!isset($_POST["file"])) {
        $errors["file"] = true;
    }
    // if no errors have occurred
    if (
        !$errors["firstname"]
        && !$errors["secondname"]
        && !$errors["useremail"]
        && !$errors["username"]
        && !$errors["password"]
        && !$errors["passwordold"]
    ) {
        // check if file is of an accepted type (jpg, jpeg, png, gif)
        $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
            require_once('config/dbaccess.php');
            $db_obj = new mysqli($host, $user, $password, $database);
            if ($db_obj->connect_error) {
                $errors["connection"] = true;
            }
            $id = $_SESSION["id"];
            $_POST["password"] = htmlspecialchars(password_hash($_POST["password"], PASSWORD_DEFAULT), ENT_QUOTES);
            // sanitize input
            $uname = htmlspecialchars($_POST["username"], ENT_QUOTES);
            $pass = htmlspecialchars($_POST["password"], ENT_QUOTES);
            $oldpass = htmlspecialchars($_POST["passwordold"], ENT_QUOTES);
            $mail = htmlspecialchars($_POST["useremail"], ENT_QUOTES);
            $fod = htmlspecialchars($_POST["formofadress"], ENT_QUOTES);
            $fname = htmlspecialchars($_POST["firstname"], ENT_QUOTES);
            $sname = htmlspecialchars($_POST["secondname"], ENT_QUOTES);
            $profilepic = $_FILES["file"]["tmp_name"];
            $path = $uploadDir . $uname . ".jpg";
            //update the user's information in database
            $sql = "UPDATE `users` SET  `username`=?, `password`=?, `useremail`=?, `formofadress`=?, `firstname`=?, `secondname`=?, `path`=? WHERE `id`=$id";
            $stmt = $db_obj->prepare($sql);
            $stmt->bind_param("sssssss", $uname, $pass, $mail, $fod, $fname, $sname, $path);

            //Check if username exists
            $sql = "SELECT * FROM `users` WHERE `username` = ?";
            $check = $db_obj->prepare($sql);
            $check->bind_param("s", $uname);

            if ($check->execute()) {
                $result = $check->get_result();
                //if username exist, check if it is my id, if yes update my data and session
                if ($result->num_rows > 0 && $result->fetch_assoc()["id"] != $id) {
                    $errors["update"] = true;
                } else {
                    $sql = "SELECT `password` FROM `users` WHERE `id`=?";
                    $get_password = $db_obj->prepare($sql);
                    $get_password->bind_param("i", $id);
                    if ($get_password->execute()) {
                        $result = $get_password->get_result();
                        $real_password = $result->fetch_assoc()["password"];
                        if (password_verify($oldpass, $real_password)) {
                            //update the session data with the new updated data
                            if ($stmt->execute() && move_uploaded_file($profilepic, $path)) {
                                $sql = "SELECT * FROM `users` WHERE `id`=?";
                                $get_newdata = $db_obj->prepare($sql);
                                $get_newdata->bind_param("i", $id);
                                if ($get_newdata->execute()) {
                                    $result = $get_newdata->get_result();
                                    $row = $result->fetch_assoc();
                                    $_SESSION["id"] = $row["id"];
                                    $_SESSION["admin"] = $row["admin"];
                                    $_SESSION["username"] = $row["username"];
                                    $_SESSION["useremail"] = $row["useremail"];
                                    $_SESSION["formofadress"] = $row["formofadress"];
                                    $_SESSION["firstname"] = $row["firstname"];
                                    $_SESSION["secondname"] = $row["secondname"];
                                    $_SESSION["profilepic"] = $row["path"];
                                    $updated = true;
                                } else {
                                    $errors["update"] = true;
                                }
                                $get_newdata->close();
                            } else {
                                $errors["update"] = true;
                            }
                        } else {
                            $errors["update"] = true;
                        }
                    } else {
                        $errors["update"] = true;
                    }
                    $get_password->close();
                }
            }
            $check->close();
            $stmt->close();
            $db_obj->close();
        } else {
            $errors["update"] = true;
        }
    } else {
        $errors["update"] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Profil</title>
</head>

<body>
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/user_edit_user.php')) {
        header("Location: ../index.php");
    } ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 offset-sm-3 text-center">
                <?php if ($errors["connection"] || $errors["update"]) {
                    $errors["connection"] = false;
                    $errors["update"] = false;
                    header("Refresh: 2, url=mein_profil.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        User konnte nicht geupdated werden!
                    </div>
                <?php } ?>
                <?php if ($updated) {
                    $updated = false;
                    header("Refresh: 2, url=mein_profil.php");
                ?>
                    <div class="alert alert-success text-center" role="alert">
                        User wurde geupdated!
                    </div>
                <?php } ?>
                <!-- form for getting updated data -->
                <form enctype="multipart/form-data" method="POST">
                    <label for="profilepic" class="form-label">Profilbild</label>
                    <div class="mb-3">
                        <img src="<?php echo $_SESSION["profilepic"] . "?" . time() ?>" class="rounded-3" style="width: 150px;" alt="Avatar" />
                    </div>
                    <div class="mb-3">
                        <label for="formofadress" class="form-label">Anrede</label>
                        <select class="form-select" name="formofadress" aria-label="Default select example" required>
                            <option value="1" <?php if ($_SESSION['formofadress'] == 1) { ?> selected <?php } ?>>Herr</option>
                            <option value="2" <?php if ($_SESSION['formofadress'] == 2) { ?> selected <?php } ?>>Frau</option>
                            <option value="3" <?php if ($_SESSION['formofadress'] == 3) { ?> selected <?php } ?>>Keine Angabe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="firstname" class="form-label">Vorname</label>
                        <input type="text" value="<?php echo $_SESSION["firstname"] ?>" class="form-control <?php if ($errors['firstname']) echo 'is-invalid'; ?>" name="firstname" id="firstname" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label">Nachname</label>
                        <input type="text" value="<?php echo $_SESSION["secondname"] ?>" class="form-control <?php if ($errors['secondname']) echo 'is-invalid'; ?>" name="secondname" id="exampleInputPassword1" required>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Email</label>
                        <input type="email" value="<?php echo $_SESSION["useremail"] ?>" class="form-control <?php if ($errors['useremail']) echo 'is-invalid'; ?>" name="useremail" id="exampleInputEmail1" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" value="<?php echo $_SESSION["username"] ?>" class="form-control <?php if ($errors['username']) echo 'is-invalid'; ?>" name="username" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="passwordold" class="form-label">Altes Passwort</label>
                        <input type="password" class="form-control <?php if ($errors['passwordold']) echo 'is-invalid'; ?>" name="passwordold" id="passwordold" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Neues Passwort</label>
                        <input type="password" class="form-control <?php if ($errors['password']) echo 'is-invalid'; ?>" name="password" id="password" minlength="8" required>
                    </div>
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Profilbild</label>
                        <input class="form-control <?php if ($errors['file']) echo 'is-invalid'; ?>" name="file" type="file" id="formFile" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <input type="hidden" name="id" value="<?php echo $_SESSION["id"] ?>">
                        <input type="hidden" name="update">
                        <button class="btn btn-primary">Aktualisieren</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>