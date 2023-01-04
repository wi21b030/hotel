<?php 
$errors = [];
$errors["nosuchbooking"] = false;
$errors["date"] = false;
$errors["connection"] = false;
$errors["update"] = false;
$updated = false;

// check if date valid
if($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["updaten"])
    && $_POST["updaten"] === "updaten") {
    if (isset($_POST['checkin'], $_POST['checkout'])) {
        $a = $_POST["checkin"];
        $b = $_POST["checkout"];
        if ($a >= $b){
            $errors["date"] = true;
        }
    }
}

// execution of update statement
if($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["updaten"])
    && $_POST["updaten"] === "updaten") {
    if(!$errors["date"]){
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $db_obj->close();
            $errors["connection"] = true;
        }
        $id = $_POST["id"];
        $checkin = $_POST["checkin"];
        $checkout = $_POST["checkout"];
        $breakfast = $_POST["breakfast"];
        $parking = $_POST["parking"];
        $pet = $_POST["pet"];

        $sql = "UPDATE `reservation` SET `checkin`=?, `checkout`=?, `breakfast`=?, `parking`=?, `pet`=? WHERE `id` = '$id'";
        $stmt = $db_obj->prepare($sql);
        $stmt->bind_param("ssiii", $checkin, $checkout, $breakfast, $parking, $pet);
        if($stmt->execute()){
            $updated = true;
        } else {
            $errors["update"] = true;
        }
        $stmt->close();
        $db_obj->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservierungs-Verwaltung</title>
</head>

<body>
    <!-- alerts for different edge cases  -->
    <?php if($errors["date"]) { 
                $errors["date"] = false;
                header("Refresh: 2, url=admin_reservierungsverwaltung.php");
    ?>
        <div class="alert alert-danger text-center" role="alert">
            Reservierung konnte nicht geändert werden, wählen Sie gültige Daten!
        </div>
    <?php } elseif ($errors["connection"] || $errors["update"]) { 
                $errors["connection"] = false;
                $errors["update"] = false;
                header("Refresh: 2, url=admin_reservierungsverwaltung.php");
    ?>
        <div class="alert alert-danger text-center" role="alert">
            Reservierung konnte nicht geändert werden!
        </div>
    <?php } elseif ($updated) { 
                $updated = false;
                header("Refresh: 2, url=admin_reservierungsverwaltung.php");          
    ?>
       <div class="alert alert-success text-center" role="alert">
            Reservierung wurde geändert!
        </div> 
    <?php } ?>
    <?php
    // dropdown list with all distinct users with at least one reservation
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["connection"] = true;
        exit();
    }
    $sql = "SELECT DISTINCT user_id, users_username FROM `reservation` ORDER BY `users_username`";
    $result = $db_obj->query($sql); ?>
    <?php if ($result->num_rows > 0) { ?>
        <form method="POST">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label style="display:<?php if ((isset($_POST["choose"]) && $_POST["choose"] === "choose") || (isset($_POST["edit"]) && $_POST["edit"] === "edit")) {
                                                echo "none";
                                            } ?>;" for="username" class="form-label">User</label>
                    <select name="id" style="display:<?php if ((isset($_POST["choose"]) && $_POST["choose"] === "choose") || (isset($_POST["edit"]) && $_POST["edit"] === "edit")) {
                                                            echo "none";
                                                        } ?>;" class="form-select" name="username" aria-label="Default select example" required>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <option value="<?php echo $row["user_id"] ?>"><?php echo $row["users_username"] ?></option>
                        <?php endwhile ?>
                    </select>
                </div>
                <div class="col-sm-10 offset-sm-1 text-center">
                    <input type="hidden" name="choose" value="choose">
                    <button style="display:<?php if ((isset($_POST["choose"]) && $_POST["choose"] === "choose") || (isset($_POST["edit"]) && $_POST["edit"] === "edit")) {
                                                echo "none";
                                            } ?>;" class="btn btn-primary mt-3">Wählen</button>
                </div>
            </div>
        </form>
    <?php } ?>
    <?php $db_obj->close(); ?>
    <?php
    if (
        $_SERVER["REQUEST_METHOD"] === "POST"
        && isset($_POST["choose"])
        && $_POST["choose"] === "choose"
    ) {
        // dropdown list with all reservations of chosen user
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
            exit();
        }
        $user_id = $_POST["id"];
        $sql = "SELECT * FROM `reservation` WHERE `user_id` = '$user_id'";
        $result = $db_obj->query($sql); ?>
        <?php if ($result->num_rows > 0) { ?>
            <form method="POST">
                <div class="row">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label style="display:<?php if (isset($_POST["edit"]) && $_POST["edit"] === "edit") {
                                                    echo "none";
                                                } ?>;" for="username" class="form-label">Reservierungen</label>
                        <select name="id" style="display:<?php if (isset($_POST["edit"]) && $_POST["edit"] === "edit") {
                                                                echo "none";
                                                            } ?>;" class="form-select" aria-label="Default select example" required>
                            <?php while ($row = $result->fetch_assoc()) : ?>
                                <option value="<?php echo $row["id"] ?>"><?php echo $row["checkin"] . " bis " . $row["checkout"] ?></option>
                            <?php endwhile ?>
                        </select>
                    </div>
                    <div class="col-sm-10 offset-sm-1 text-center">
                        <input type="hidden" name="edit" value="edit">
                        <button style="display:<?php if (isset($_POST["edit"]) && $_POST["edit"] === "edit") {
                                                    echo "none";
                                                } ?>;" class="btn btn-primary mt-3">Bearbeiten</button>
                    </div>
                </div>
            </form>
        <?php } ?>
        <?php $db_obj->close(); ?>
    <?php } ?>
    <?php
    // form for updating reservation
    if (
        $_SERVER["REQUEST_METHOD"] === "POST"
        && isset($_POST["edit"])
        && $_POST["edit"] === "edit"
    ) {
        $id = $_POST["id"];
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
            exit();
        }
        $sql = "SELECT * FROM `reservation` WHERE `id` = '$id'";
        $result = $db_obj->query($sql);
        if ($result->num_rows == 0) {
            echo "no reservation with this id";
        } else {
            $row = $result->fetch_assoc(); ?>
            <div class="container-fluid">
                <form method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <div class="mb-3">
                                <label for="checkin" class="form-label">Check-In</label>
                                <input type="date" value="<?php echo $row["checkin"] ?>" class="form-control " name="checkin" id="checkin" required>
                            </div>

                            <div class="mb-3">
                                <label for="checkout" class="form-label">Check-Out</label>
                                <input type="date" value="<?php echo $row["checkout"] ?>" class="form-control " name="checkout" id="checkout" required>
                            </div>

                            <div class="mb-3">
                                <label for="breakfast" class="form-label">Frühstück</label>
                                <select class="form-select" name="breakfast" aria-label="Default select example" required>
                                    <option value="1" <?php if ($row['breakfast'] == 1) { ?> selected <?php } ?>>Ja</option>
                                    <option value="0" <?php if ($row['breakfast'] == 0) { ?> selected <?php } ?>>Nein</option>

                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="parking" class="form-label">Parkplatz</label>
                                <select class="form-select" name="parking" aria-label="Default select example" required>
                                    <option value="1" <?php if ($row['parking'] == 1) { ?> selected <?php } ?>>Ja</option>
                                    <option value="0" <?php if ($row['parking'] == 0) { ?> selected <?php } ?>>Nein</option>

                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="pet" class="form-label">Haustier</label>
                                <select class="form-select" name="pet" aria-label="Default select example" required>
                                    <option value="1" <?php if ($row['pet'] == 1) { ?> selected <?php } ?>>Ja</option>
                                    <option value="0" <?php if ($row['pet'] == 0) { ?> selected <?php } ?>>Nein</option>

                                </select>
                            </div>

                            <div class="mb-3">
                                <input type="hidden" value="<?php echo $row["id"] ?>" class="form-control " name="id" id="id">
                            </div>
                            <div class="mb-3">
                                <input type="hidden" name="updaten" value="updaten">
                                <button class="btn btn-primary">Updaten</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php
        }
        $db_obj->close();
        ?>
    <?php } ?>
</body>

</html>