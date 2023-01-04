<?php
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

if (!file_exists($uploadDir)) {
    mkdir($uploadDir);
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["updaten"])
    && $_POST["updaten"] === "updaten"
) {
    if (empty($_POST["firstname"]) || !isset($_POST["firstname"])) {
        $errors["firstname"] = true;
    }
    if (empty($_POST["secondname"]) || !isset($_POST["secondname"])) {
        $errors["secondname"] = true;
    }
    if (empty($_POST["useremail"]) || !isset($_POST["useremail"])) {
        $errors["useremail"] = true;
    }
    if (!empty($_POST["useremail"])) {
        $check = test_input($_POST["useremail"]);
        if (!filter_var($check, FILTER_VALIDATE_EMAIL)) {
            $errors["useremail"] = true;
        }
    }
    if (empty($_POST["username"]) || !isset($_POST["username"])) {
        $errors["username"] = true;
    }
    if (empty($_POST["password"]) || !isset($_POST["password"])) {
        $errors["password"] = true;
    }
    if (empty($_POST["passwordold"]) || !isset($_POST["passwordold"])) {
        $errors["passwordold"] = true;
    }
    if(empty($_POST["file"]) || !isset($_POST["file"])){
        $errors["file"] = true;
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["updaten"])
    && $_POST["updaten"] === "updaten"
) {
    $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
    if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
            $db_obj->close();
            exit();
        }
        $id = $_SESSION["id"];
        $_POST["password"] = htmlspecialchars(password_hash($_POST["password"], PASSWORD_DEFAULT), ENT_QUOTES);
        $uname = htmlspecialchars($_POST["username"], ENT_QUOTES);
        $pass = htmlspecialchars($_POST["password"], ENT_QUOTES);
        $oldpass = htmlspecialchars($_POST["passwordold"], ENT_QUOTES);
        $mail = htmlspecialchars($_POST["useremail"], ENT_QUOTES);
        $fod = $_POST["formofadress"];
        $fname = htmlspecialchars($_POST["firstname"], ENT_QUOTES);
        $sname = htmlspecialchars($_POST["secondname"], ENT_QUOTES);
        $profilepic = $_FILES["file"]["tmp_name"];
        $path = $uploadDir . $uname . ".jpg";

        $sql = "UPDATE `users` SET  `username`=?, `password`=?, `useremail`=?, `formofadress`=?, `firstname`=?, `secondname`=?, `path`=? WHERE `id`=$id";
        $stmt = $db_obj->prepare($sql);
        $stmt->bind_param("sssssss", $uname, $pass, $mail, $fod, $fname, $sname, $path);
        
        $sql = "SELECT * FROM `users` WHERE `username` = '$uname'";
        $result = $db_obj->query($sql);
        $row = $result->fetch_assoc();
        if ($result->num_rows > 0 && $row["id"] != $id) {
            $errors["update"] = true;
        } else {
            if (password_verify($oldpass, $row["password"])) {
                if($stmt->execute()){
                    move_uploaded_file($profilepic, $path);
                    $updated = true;
                } else {
                    $errors["connection"] = true;
                }
            } else {
                $errors["update"] = true;
            }   
        }
        $stmt->close();
        $db_obj->close();
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
    <?php if ($errors["connection"] || $errors["update"]) { 
        $errors["connection"] = false;
        $errors["update"] = false;
        header("Refresh: 2, url=mein_profil.php");
    ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <div class="alert alert-danger text-center" role="alert">
                        User konnte nicht geupdated werden!
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if ($updated) { 
            $updated = false;
            header("Refresh: 2, url=mein_profil.php");
    ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <div class="alert alert-success text-center" role="alert">
                        User wurde geupdated!
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
            exit();
        }
        $id = $_SESSION["id"];
        $sql = "SELECT * FROM `users` WHERE `id` = '$id'";
        $result = $db_obj->query($sql);
        if ($result->num_rows == 0) {
            $errors["exists"] = true;
        } else {
            $row = $result->fetch_assoc(); ?>
            <div class="container-fluid">
                <div class="row mb-2 mt-1">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <img src="<?php echo $row["path"] ?>" class="rounded-3" style="width: 150px;"
                    alt="Avatar" />
                </div>
                </div>
                <form enctype="multipart/form-data" method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <div class="mb-3">
                                <label for="formofadress" class="form-label">Anrede</label>
                                <select class="form-select" name="formofadress" aria-label="Default select example" required>
                                    <option value="1" <?php if ($row['formofadress'] == 1) { ?> selected <?php } ?>>Herr</option>
                                    <option value="2" <?php if ($row['formofadress'] == 2) { ?> selected <?php } ?>>Frau</option>
                                    <option value="3" <?php if ($row['formofadress'] == 3) { ?> selected <?php } ?>>Keine Angabe</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="firstname" class="form-label">Vorname</label>
                                <input type="text" value="<?php echo $row["firstname"] ?>" class="form-control <?php if ($errors['firstname']) echo 'is-invalid'; ?>" name="firstname" id="firstname" required>
                            </div>
                            <div class="mb-3">
                                <label for="exampleInputPassword1" class="form-label">Nachname</label>
                                <input type="text" value="<?php echo $row["secondname"] ?>" class="form-control <?php if ($errors['secondname']) echo 'is-invalid'; ?>" name="secondname" id="exampleInputPassword1" required>
                            </div>
                            <div class="mb-3">
                                <label for="exampleInputEmail1" class="form-label">Email</label>
                                <input type="email" value="<?php echo $row["useremail"] ?>" class="form-control <?php if ($errors['useremail']) echo 'is-invalid'; ?>" name="useremail" id="exampleInputEmail1" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" value="<?php echo $row["username"] ?>" class="form-control <?php if ($errors['username']) echo 'is-invalid'; ?>" name="username" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="passwordold" class="form-label">Altes Passwort</label>
                                <input type="password" class="form-control <?php if ($errors['passwordold']) echo 'is-invalid'; ?>" name="passwordold" id="passwordold" minlength="8"  required>
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
                                <input type="hidden" name="updaten" value="updaten">
                                <button class="btn btn-primary">Updaten</button>
                            </div>
                            <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                        </div>
                    </div>
                </form>
            </div>
        <?php $db_obj->close(); ?>
    <?php } ?>
</body>

</html>