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

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
    if (empty($_POST["firstname"])) {
        $errors["firstname"] = true;
    }
    if (empty($_POST["secondname"])) {
        $errors["secondname"] = true;
    }
    if (empty($_POST["useremail"])) {
        $errors["useremail"] = true;
    }
    if(!empty($_POST["useremail"])){
        $check = test_input($_POST["useremail"]);
        if(!filter_var($check, FILTER_VALIDATE_EMAIL)){
            $errors["useremail"] = true;
        }
    }
    if (empty($_POST["username"])) {
        $errors["username"] = true;
    }
    if (empty($_POST["password"])) {
        $errors["password"] = true;
    }
    if (empty($_POST["password2"])) {
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
        if(isset($_FILES["file"]) && !empty($_FILES["file"])){
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
                    require_once ('config/dbaccess.php');
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

                    // prepared insert statement
                    $sql = "INSERT INTO `users` (`username`, `password`, `useremail`, `formofadress`, `firstname`, `secondname`, `path`) VALUES (?,?,?,?,?,?,?)";
                    $stmt = $db_obj -> prepare ($sql);
                    $stmt -> bind_param("sssssss", $uname, $pass, $mail, $fod, $fname, $sname, $path);
                    
                    // prepared select statement to check if username exists already or not
                    // used prepare statement for select to further protect against sql injection
                    $uname_taken = "SELECT * FROM `users` WHERE `username` = ?";
                    $stmt_uname_taken = $db_obj -> prepare ($uname_taken);
                    $stmt_uname_taken -> bind_param("s", $uname);
                    if($stmt_uname_taken->execute()){
                        $result = $stmt_uname_taken->get_result();
                        if ($result->num_rows > 0) {
                            $errors["exists"] = true;
                        } else {
                            if($uname === $_POST["username"]
                                && password_verify($_POST["password"],$pass)
                                && password_verify($_POST["password2"],$pass)
                                && $mail === $_POST["useremail"]
                                && $fname === $_POST["firstname"]
                                && $sname === $_POST["secondname"]){
                                if ($stmt -> execute()){
                                    $sql = "SELECT * FROM `users` WHERE `username` = '$uname'";
                                    $result = $db_obj->query($sql);
                                    if($result->num_rows == 1){
                                        $row = $result->fetch_assoc();
                                        move_uploaded_file($profilepic, $path);
                                        $_SESSION["id"] = $row["id"];
                                        $_SESSION["username"] = $uname;
                                        $_SESSION["admin"] = $row["admin"];
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
                        }
                    } else {
                        $errors["insert"] = true;
                    }
                    $stmt -> close();
                    $stmt_uname_taken-> close();
                    $db_obj-> close();
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
        <?php if($errors["exists"]) { 
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
        <?php } elseif($registered) { 
                    $registered = false;
                    header("Refresh: 2, url=mein_profil.php");           
        ?>
            <div class="alert alert-success text-center" role="alert">
                Registrierung erfolgreich!
            </div>  
        <?php } ?>
        <form action="registrierung.php" enctype="multipart/form-data" method="POST">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
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
                    <input type="hidden" name="register" value="register">
                    <button type="submit" class="btn btn-primary">Registrieren</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>