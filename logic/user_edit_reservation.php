<?php
$errors = [];
$errors["checkin"] = false;
$errors["checkout"] = false;
$errors["connection"] = false;
$updated = false;

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['checkin'], $_POST['checkout'])
    && isset($_POST["updaten"])
) {
    $checkin = $_POST["checkin"];
    $checkout = $_POST["checkout"];
    if ($checkin >= $checkout) {
        $errors["checkin"] = true;
        $errors["checkout"] = true;
    } else {
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $breakfast = $_POST["breakfast"];
        $parking = $_POST["parking"];
        $pet = $_POST["pet"];
        $id = $_POST["id"];
        $uname = $_SESSION["username"];
        $user_id = $_SESSION["id"];
        $price = 50;
        if ($breakfast) {
            $price += 10;
        }
        if ($parking) {
            $price += 3;
        }
        if ($pet) {
            $price += 5;
        }

        $sql = "UPDATE `reservation` SET `checkin`=?, `checkout`=?, `breakfast`=?, `parking`=?, `pet`=?, `users_username`=?, `time`=?, `user_id`=? WHERE `id` = $id";
        $stmt = $db_obj->prepare($sql);
        $stmt->bind_param("ssiiissi", $checkin, $checkout, $breakfast, $parking, $pet, $uname, $datenow, $user_id);
        if ($stmt->execute()) {
            $updated = true;
        } else {
            $errors["connection"] = true;
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
    <title>Meine Reservierungen</title>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 offset-sm-3 text-center">
                <?php if ($errors["checkin"] || $errors["checkout"]) {
                    $errors["checkin"] = true;
                    $errors["checkout"] = true;
                    header("Refresh: 2, url=meine_reservierungen.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Geben Sie bitte gültige Daten ein!
                    </div>
                <?php } elseif ($errors["connection"]) {
                    $errors["connection"] = false;
                    header("Refresh: 2, url=meine_reservierungen.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Reservierung konnte nicht geändert werden!
                    </div>
                <?php } elseif ($updated) {
                    $updated = false;
                    header("Refresh: 2, url=meine_reservierungen.php");
                ?>
                    <div class="alert alert-success text-center" role="alert">
                        Reservierung wurde geändert!
                    </div>
                <?php } ?>
                <?php
                // dropdown list with reservations of logged in user
                require_once('config/dbaccess.php');
                $db_obj = new mysqli($host, $user, $password, $database);
                if ($db_obj->connect_error) {
                    $errors["connection"] = true;
                }
                $user_id = $_SESSION["id"];
                $sql = "SELECT * FROM `reservation` WHERE `user_id` = '$user_id'";
                $result = $db_obj->query($sql);
                if ($result->num_rows > 0) { ?>
                    <form method="POST">
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
                        <div class="col-sm-10 offset-sm-1 text-center">
                            <input type="hidden" name="edit" value="edit">
                            <button style="display:<?php if (isset($_POST["edit"]) && $_POST["edit"] === "edit") {
                                                        echo "none";
                                                    } ?>;" class="btn btn-primary mt-3">Bearbeiten</button>
                        </div>
                    </form>
                <?php $db_obj->close();
                } ?>
                <?php
                // form to change own reservations
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
                    }
                    $sql = "SELECT * FROM `reservation` WHERE `id` = '$id'";
                    $result = $db_obj->query($sql);
                    $row = $result->fetch_assoc(); ?>
                    <form method="POST">
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
                    </form>
                    <?php $db_obj->close(); ?>
                <?php } ?>
            </div>
        </div>
    </div>
</body>

</html>