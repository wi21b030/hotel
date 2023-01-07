<?php
$uploadDir = "uploads/profilepics/";
$errors = [];
$errors["firstname"] = false;
$errors["secondname"] = false;
$errors["useremail"] = false;
$errors["username"] = false;
$errors["password"] = false;
$errors["file"] = false;
$errors["update"] = false;
$errors["connection"] = false;
$updated = false;

if (!file_exists($uploadDir)) {
    mkdir($uploadDir);
}

// function that we found via W3School that validates input
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

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
    if (empty($_POST["password"]) || !isset($_POST["password"])  || strlen(trim($_POST["password"])) == 0 || strlen(trim($_POST["password"])) < 8) {
        $errors["password"] = true;
    }
    if (!isset($_POST["file"])) {
        $errors["file"] = true;
    }
    if (
        !$errors["firstname"]
        && !$errors["secondname"]
        && !$errors["useremail"]
        && !$errors["username"]
        && !$errors["password"]
    ) {
        $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
            require_once('config/dbaccess.php');
            $db_obj = new mysqli($host, $user, $password, $database);
            if ($db_obj->connect_error) {
                $errors["connection"] = true;
            }
            $id = $_POST["id"];
            $active = $_POST["active"];
            // here we make sure we are protected from JavaScript Injections
            $_POST["password"] = htmlspecialchars(password_hash($_POST["password"], PASSWORD_DEFAULT), ENT_QUOTES);
            $uname = htmlspecialchars($_POST["username"], ENT_QUOTES);
            $pass = htmlspecialchars($_POST["password"], ENT_QUOTES);
            $mail = htmlspecialchars($_POST["useremail"], ENT_QUOTES);
            $fod = htmlspecialchars($_POST["formofadress"], ENT_QUOTES);
            $fname = htmlspecialchars($_POST["firstname"], ENT_QUOTES);
            $sname = htmlspecialchars($_POST["secondname"], ENT_QUOTES);
            $profilepic = $_FILES["file"]["tmp_name"];
            $path = $uploadDir . $uname . ".jpg";

            // prepared update statement
            $sql = "UPDATE `users` SET `active`=?, `username`=?, `password`=?, `useremail`=?, `formofadress`=?, `firstname`=?, `secondname`=?, `path`=? WHERE `id`=?";
            $stmt = $db_obj->prepare($sql);
            $stmt->bind_param("isssssssi", $active, $uname, $pass, $mail, $fod, $fname, $sname, $path, $id);

            $sql = "SELECT * FROM `users` WHERE `username`=?";
            $check = $db_obj->prepare($sql);
            $check->bind_param("s", $uname);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows > 0 && $result->fetch_assoc()["id"] != $id) {
                $errors["update"] = true;
            } else {
                if ($stmt->execute() && move_uploaded_file($profilepic, $path)) {
                    $updated = true;
                } else {
                    $errors["update"] = true;
                }
            }
            $stmt->close();
            $db_obj->close();
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
    <title>User-Verwaltung</title>
</head>

<body>
    <!-- multiple alerts for different errors or success notifications -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 offset-sm-3 text-center">
                <?php if ($errors["connection"]) {
                    // we always set the booleans back to false so that it is not always true if the connection did#
                    // not work, otherwise the website will keep refreshing endlessly
                    $errors["connection"] = false;
                    header("Refresh: 2, url=admin_userverwaltung.php"); ?>
                    <div class="alert alert-success text-center" role="alert">
                        Fehler bei der Datenbankverbindung!
                    </div>

                <?php } ?>
                <?php if ($updated) {
                    $updated = false;
                    header("Refresh: 2, url=admin_userverwaltung.php");
                ?>
                    <div class="alert alert-success text-center" role="alert">
                        User wurde geupdated!
                    </div>
                <?php } ?>
                <?php if ($errors["update"]) {
                    $errors["update"] = false;
                    header("Refresh: 2, url=admin_userverwaltung.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        User wurde nicht geupdated, aufgrund fehlerhafter Daten!
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
    // dropdown list with all users who are not admins, sorted by username alphabetically
    if (!isset($_POST["edit"]) && !isset($_POST["view"])) {
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $sql = "SELECT * FROM `users` WHERE `admin` = FALSE ORDER BY `username`";
        $result = $db_obj->query($sql);
        if ($result->num_rows > 0) { ?>
            <div class="container-fluid">
                <form method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <label for="username" class="form-label">User</label>
                            <select name="id" class="form-select" name="username" aria-label="Default select example" required>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <!-- for each user we set the value as their id so we can pass the information to the next form to be used -->
                                    <option value="<?php echo $row["id"] ?>"><?php echo $row["username"] ?></option>
                                <?php endwhile ?>
                            </select>
                        </div>
                        <div class="col-sm-10 offset-sm-1 text-center">
                            <input type="hidden" name="edit" value="edit">
                            <button class="btn btn-primary mt-3">Bearbeiten</button>
                        </div>
                    </div>
                </form>
            </div>
    <?php }
        $db_obj->close();
    } ?>
    <?php
    if (
        $_SERVER["REQUEST_METHOD"] === "POST"
        && isset($_POST["edit"])
    ) {
        $id = $_POST["id"];
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $sql = "SELECT * FROM `users` WHERE `id` = '$id'";
        $result = $db_obj->query($sql);
        if ($result->num_rows == 0) {
            $errors["exists"] = true;
        } else {
            $row = $result->fetch_assoc(); ?>
            <div class="container-fluid">
                <form enctype="multipart/form-data" method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <label for="profilepic" class="form-label">Profilbild</label>
                            <div class="mb-3">
                                <img src="<?php echo $row["path"] . "?" . time() ?>" class="rounded-3" style="width: 150px;" alt="Avatar" />
                            </div>
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
                                <label for="password" class="form-label">Passwort</label>
                                <input type="password" class="form-control <?php if ($errors['password']) echo 'is-invalid'; ?>" name="password" id="password" minlength="8" required>
                            </div>
                            <div class="mb-3">
                                <label for="formFile" class="form-label">Profilbild</label>
                                <input class="form-control <?php if ($errors['file']) echo 'is-invalid'; ?>" name="file" type="file" id="formFile" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label for="active" class="form-label">Account-Validität</label>
                                <select class="form-select" name="active" aria-label="Default select example" required>
                                    <option value="0" <?php if (!$row['active']) { ?> selected <?php } ?>>Nicht Aktiv</option>
                                    <option value="1" <?php if ($row['active']) { ?> selected <?php } ?>>Aktiv</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                                <input type="hidden" name="update">
                                <button class="btn btn-primary">Aktualisieren</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <?php
            $sql = "SELECT * FROM `reservation` WHERE `user_id`='$id' ORDER BY `checkin`, `checkout`";
            $result = $db_obj->query($sql); ?>
            <div class="container-fluid">
                <form method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <label for="username" class="form-label">Reservierungen</label>
                            <select name="id" class="form-select" aria-label="Default select example" required>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <option value="<?php echo $row["id"] ?>"><?php echo $row['checkin'] . " bis " . $row["checkout"]; ?></option>
                                <?php endwhile ?>
                            </select>
                        </div>
                        <div class="col-sm-10 offset-sm-1 text-center">
                            <input type="hidden" name="view">
                            <button class="btn btn-primary mt-3">Details einsehen</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php
        }
        $db_obj->close();
        ?>
    <?php } ?>
    <?php
    // form for updating reservation
    if (
        $_SERVER["REQUEST_METHOD"] === "POST"
        && isset($_POST["view"])
    ) {
        $id = $_POST["id"];
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $sql = "SELECT * FROM `reservation` WHERE `id` = '$id'";
        $result = $db_obj->query($sql);
        $row = $result->fetch_assoc(); ?>
        <div class="container-fluid">
            <form method="POST">
                <div class="row">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <div class="mb-3">
                            <label for="checkin" class="form-label">Check-In</label>
                            <input type="date" value="<?php echo $row["checkin"] ?>" class="form-control " name="checkin" id="checkin" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="checkout" class="form-label">Check-Out</label>
                            <input type="date" value="<?php echo $row["checkout"] ?>" class="form-control " name="checkout" id="checkout" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="breakfast" class="form-label">Frühstück</label>
                            <select class="form-select" name="breakfast" aria-label="Default select example" disabled>
                                <option value="1" <?php if ($row['breakfast'] == 1) { ?> selected <?php } ?>>Ja</option>
                                <option value="0" <?php if ($row['breakfast'] == 0) { ?> selected <?php } ?>>Nein</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="parking" class="form-label">Parkplatz</label>
                            <select class="form-select" name="parking" aria-label="Default select example" disabled>
                                <option value="1" <?php if ($row['parking'] == 1) { ?> selected <?php } ?>>Ja</option>
                                <option value="0" <?php if ($row['parking'] == 0) { ?> selected <?php } ?>>Nein</option>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pet" class="form-label">Haustier</label>
                            <select class="form-select" name="pet" aria-label="Default select example" disabled>
                                <option value="1" <?php if ($row['pet'] == 1) { ?> selected <?php } ?>>Ja</option>
                                <option value="0" <?php if ($row['pet'] == 0) { ?> selected <?php } ?>>Nein</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <!-- Preis einnfügen sobald verfügbar -->
                            <label for="price" class="form-label">Preis</label>
                            <input type="text" class="form-control " name="price" id="checkin" placeholder="1234€" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" aria-label="Default select example" disabled>
                                <option value="0" <?php if ($row['status'] == 0) { ?> selected <?php } ?>>Neu</option>
                                <option value="1" <?php if ($row['status'] == 1) { ?> selected <?php } ?>>Bestätigt</option>
                                <option value="2" <?php if ($row['status'] == 2) { ?> selected <?php } ?>>Storniert</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php $db_obj->close();
    }
    ?>
</body>

</html>