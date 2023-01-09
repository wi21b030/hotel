<?php
$errors = [];
$errors["checkin"] = false;
$errors["checkout"] = false;
$errors["connection"] = false;
$confirmed = false;
$noroom = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['book'])) {
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["connection"] = true;
    }
    $checkin = $_POST["checkin"];
    $checkout = $_POST["checkout"];
    if ($checkin >= $checkout) {
        $errors["checkin"] = true;
        $errors["checkout"] = true;
    }
    $type = $_POST["type"];
    // changed sql-query cause it was still possible to book rooms 
    //where i.e check-in date or check.out was outside of socpe of already booked rooms
    $sql = "SELECT * FROM `rooms`
        WHERE `room_number` NOT IN (
        SELECT DISTINCT `room`
        FROM `reservation`
        WHERE ($checkin NOT BETWEEN `checkin` AND `checkout`) AND ($checkout NOT BETWEEN `checkin` AND `checkout`) AND `status`<>'Storniert') AND `type`='$type' LIMIT 1 ";
    $result = $db_obj->query($sql);
    if ($result->num_rows == 0) {
        $noroom = true;
    } else {
        $row = $result->fetch_assoc();
        $datenow = date('Y-m-d H:i:s', time());
        $breakfast = $_POST["breakfast"];
        $parking = $_POST["parking"];
        $pet = $_POST["pet"];
        $iduser = $_SESSION["id"];
        $room_no = $row["room_number"];
        $price = $row["rate"];
        if ($breakfast == "Ja") {
            $price += 10;
        }
        if ($parking == "Ja") {
            $price += 3;
        }
        if ($pet != "Kein") {
            $price += 5;
        }
        $date1 = new DateTime($checkin);
        $date2 = new DateTime($checkout);
        $interval = $date1->diff($date2);
        $nights = ($interval->days);
        $total = $price * $nights;
    }
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['confirm'])
) {
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["connection"] = true;
    }
    $datenow = time();
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $pet = $_POST['pet'];
    $parking = $_POST['parking'];
    $breakfast = $_POST['breakfast'];
    $room = $_POST['roomnumber'];
    $iduser = $_SESSION["id"];
    $nights =  $_POST['nights'];
    $total = $_POST["total"];

    $sql = "INSERT INTO `reservation` (`checkin`, `checkout`, `breakfast`, `parking`, `pet`, `time`, `user_id`, `total`, `nights`, `room`) VALUES (?,?,?,?,?,?,?,?,?,?)";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("sssssiiiii", $checkin, $checkout, $breakfast, $parking, $pet, $datenow, $iduser, $total, $nights, $room);
    if ($stmt->execute()) {
        $confirmed = true;
    } else {
        $errors["connection"] = true;
    }
    $stmt->close();
    $db_obj->close();
}

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['cancel'])
) {
    header("Location: reservierung.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservierung</title>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 offset-sm-3 text-center">
                <?php if ($errors["checkin"] || $errors["checkout"]) {
                    $errors["checkin"] = true;
                    $errors["checkout"] = true;
                    header("Refresh: 2, url=reservierung.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Geben Sie bitte gültige Daten ein!
                    </div>
                <?php } elseif ($errors["connection"]) {
                    $errors["connection"] = false;
                    header("Refresh: 2, url=reservierung.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Reservierung konnte nicht gebucht werden! Versuchen Sie es später.
                    </div>
                <?php } elseif ($noroom) {
                    header("Refresh: 2, url=reservierung.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Es gibt leider kein Zimmer zu Ihren gewünschten Daten!
                    </div>
                <?php } elseif ($confirmed) {
                    $confirmed = false;
                    header("Refresh: 2, url=reservierung.php");
                ?>
                    <div class="alert alert-success text-center" role="alert">
                        Reservierung wurde gebucht!
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <?php if (!isset($_POST["book"])) { ?>
                <form method="POST">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="checkin" class="form-label">Check-In</label>
                        <input type="date" name="checkin" class="form-control" aria-label="Check-In" required>
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="checkout" class="form-label">Check-Out</label>
                        <input type="date" name="checkout" class="form-control" aria-label="Check-Out" required>
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="type" class="form-label">Zimmer-Art</label>
                        <select class="form-select" name="type" aria-label="Default select example" required>
                            <option value="Single">Single-Zimmer (50€ p.N.)</option>
                            <option value="Double">Double-Zimmer (80€ p.N.)</option>
                        </select>
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="breakfast" class="form-label">Frühstück (+10€ p.N.)</label>
                        <select class="form-select" name="breakfast" aria-label="Default select example" required>
                            <option value="Nein">Nein</option>
                            <option value="Ja">Ja</option>
                        </select>
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="parking" class="form-label">Parkplatz (+3€ p.N.)</label>
                        <select class="form-select" name="parking" aria-label="Default select example" required>
                            <option value="Nein">Nein</option>
                            <option value="Ja">Ja</option>
                        </select>
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="pet" class="form-label">Haustier (+5€ p.N.)</label>
                        <select class="form-select" name="pet" aria-label="Default select example" required>
                            <option value="Kein">Kein Haustier</option>
                            <option value="Hund">Hund</option>
                            <option value="Katze">Katze</option>
                            <option value="Kleintier">Kleintier</option>
                            <option value="Vogel">Vogel</option>
                        </select>
                    </div>
                    <div class="col-sm-10 offset-sm-1 text-center">
                        <button name="book" class="btn btn-primary mt-3">Buchen</button>
                    </div>
                </form>
            <?php } ?>

            <?php if (!$noroom && isset($_POST["book"])) { ?>
                <form method="POST">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <div class="mb-3">
                            <label for="checkin" class="form-label">Check-In</label>
                            <input type="date" value="<?php echo $checkin ?>" class="form-control " name="checkin" id="checkin" disabled>
                            <label for="checkin" class="form-label"></label>
                            <input type="hidden" value="<?php echo $checkin ?>" name="checkin">
                        </div>

                        <div class="mb-3">
                            <label for="checkout" class="form-label">Check-Out</label>
                            <input type="date" value="<?php echo $checkout ?>" class="form-control " name="checkout" id="checkout" disabled>
                            <label for="checkout" class="form-label"></label>
                            <input type="hidden" value="<?php echo $checkout ?>" name="checkout">
                        </div>

                        <div class="mb-3">
                            <label for="roomtype" class="form-label">Zimmer-Art</label>
                            <input type="text" value="<?php echo $type ?>-Zimmer" class="form-control " name="type" id="roomtype" disabled>
                            <input type="hidden" value="<?php echo $type ?>" name="type">
                        </div>
                        <div class="mb-3">
                            <label for="roomnumber" class="form-label">Zimmer-Nummer</label>
                            <input type="text" value="<?php echo $room_no ?>" class="form-control " name="roomnumber" id="roomnumber" disabled>
                            <input type="hidden" value="<?php echo $room_no ?>" name="roomnumber">
                        </div>
                        <div class="mb-3">
                            <label for="breakfast" class="form-label">Frühstuck</label>
                            <input type="text" value="<?php echo $breakfast ?>" class="form-control " name="breakfast" id="breakfast" disabled>
                            <input type="hidden" value="<?php echo $breakfast ?>" name="breakfast">
                        </div>
                        <div class="mb-3">
                            <label for="parkin" class="form-label">Parkplatz</label>
                            <input type="text" value="<?php echo $parking ?>" class="form-control " name="parking" id="parkin" disabled>
                            <input type="hidden" value="<?php echo $parking ?>" name="parking">
                        </div>

                        <div class="mb-3">
                            <label for="pet" class="form-label">Haustier</label>
                            <input type="text" value="<?php echo $pet ?>" class="form-control " name="pet" id="pet" disabled>
                            <input type="hidden" value="<?php echo $pet ?>" name="pet">
                        </div>
                        <div class="mb-3">
                            <label for="nights" class="form-label">Nächte</label>
                            <input type="text" value="<?php echo $nights ?>" class="form-control " name="nights" id="nights" disabled>
                            <input type="hidden" value="<?php echo $nights ?>" name="nights">
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Preis p.N.</label>
                            <input type="text" value="<?php echo $price ?>€" class="form-control " name="price" id="price" disabled>
                            <input type="hidden" value="<?php echo $rpice ?>" name="price">
                        </div>
                        <div class="mb-3">
                            <label for="total" class="form-label">Preis insg.</label>
                            <input type="text" value="<?php echo $total ?>€" class="form-control " name="total" id="total" disabled>
                            <input type="hidden" value="<?php echo $total ?>" name="total">
                        </div>
                        <div class="mb-6">
                            <button type="submit" name="confirm" class="btn btn-success mt-3">Bestätigen</button>
                            <button type="submit" name="cancel" class="btn btn-danger mt-3">Abbrechen</button>
                        </div>
                </form>
            <?php } ?>
        </div>
    </div>
</body>

</html>