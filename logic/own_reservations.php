<?php

require_once('config/dbaccess.php');
$db_obj = new mysqli($host, $user, $password, $database);
if ($db_obj->connect_error) {
    $errors["connection"] = true;
    exit();
}
$user_id = $_SESSION["id"];
$sql = "SELECT * FROM `reservation` WHERE `user_id` = '$user_id'";
$result = $db_obj->query($sql);

if (isset($_POST['checkin'], $_POST['checkout'], $_POST['breakfast'], $_POST['parking'], $_POST['pet']) && isset($_POST["reserve"])) {
    // Process form data
    $datenow = date('Y-m-d H:i:s', time());
    $checkin = $_POST["checkin"];
    $checkout = $_POST["checkout"];
    $breakfast = $_POST["breakfast"];
    $parking = $_POST["parking"];
    $pet = $_POST["pet"];
    $id = $_POST["id"];
    $user = $_SESSION["username"];
    $iduser = $_SESSION["id"];
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
    $stmt->bind_param("ssiiissi", $checkin, $checkout, $breakfast, $parking, $pet, $user, $datenow, $iduser);
    $stmt->execute();
    $stmt->close();
} else {
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
                                <input type="hidden" name="reserve" value="reserve">
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