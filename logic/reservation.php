<?php
// Declare variables for storing errors
$errors = [];
$errors["checkin"] = false;
$errors["checkout"] = false;
$errors["connection"] = false;
$confirmed = false;
$noroom = false;

// check if form was submitted and book button was pressed
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['book'])
) {
    //create db connection
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["connection"] = true;
    }
    $checkin = $_POST["checkin"];
    $checkout = $_POST["checkout"];
    if ($checkin >= $checkout || date("dd.mm.yyyy", strtotime($checkin)) < date("dd.mm.yyyy", time()) || date("dd.mm.yyyy", strtotime($checkout)) <= date("dd.mm.yyyy", time())) {
        $errors["checkin"] = true;
        $errors["checkout"] = true;
    }
    $type = $_POST["type"];
    //checking availability of rooms with checkin, checkout and type
    $sql = "SELECT *
            FROM rooms
            WHERE room_number NOT IN (
            SELECT DISTINCT room
            FROM reservation
            WHERE (? <= `checkin` AND ? >= `checkout`) AND `status`<>'Storniert') 
            AND `type`=? 
            LIMIT 1";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("sss", $checkin, $checkout, $type);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            // save the data submitted by the form into variables and set the price
            $row = $result->fetch_assoc();
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
        } else {
            $noroom = true;
        }
    } else {
        $errors["connection"] = true;
    }
    $stmt->close();
    $db_obj->close();
}
// check if form was submitted and confirm button was pressed
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
    //prepared statement to insert all the reservation data
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
//if reservation gets canceled refresh to starting point
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
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/reservation.php')) {
        header("Location: ../index.php");
    } ?>
    <div class="container-fluid">
        <div class="row">
            <!-- header display handling -->
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
                <?php } elseif ($confirmed) {
                    $confirmed = false;
                    header("Refresh: 2, url=reservierung.php");
                ?>
                    <div class="alert alert-success text-center" role="alert">
                        Reservierung wurde gebucht!
                    </div>
                <?php } elseif ($noroom) {
                    header("Refresh: 2, url=reservierung.php");
                ?>
                    <div class="alert alert-danger text-center" role="alert">
                        Es gibt leider kein Zimmer mit Ihren gewünschten Daten! (oder noch immer Bug)
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <?php if (!isset($_POST["book"])) { ?>
                <!-- form for registration data submission -->
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
            <!-- if book button is pressed and rooms are available show preview of booking for confirmation -->
            <?php if (!$noroom && !in_array(true, $errors) && isset($_POST["book"])) { ?>
                <form method="POST">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <div class="mb-3">
                            <label for="checkin" class="form-label">Check-In</label>
                            <input type="date" value="<?php echo $checkin ?>" class="form-control " id="checkin" disabled>
                            <input type="hidden" value="<?php echo $checkin ?>" name="checkin">
                        </div>

                        <div class="mb-3">
                            <label for="checkout" class="form-label">Check-Out</label>
                            <input type="date" value="<?php echo $checkout ?>" class="form-control " id="checkout" disabled>
                            <input type="hidden" value="<?php echo $checkout ?>" name="checkout">
                        </div>

                        <div class="mb-3">
                            <label for="roomtype" class="form-label">Zimmer-Art</label>
                            <input type="text" value="<?php echo $type ?>-Zimmer" class="form-control " id="roomtype" disabled>
                            <input type="hidden" value="<?php echo $type ?>" name="type">
                        </div>
                        <div class="mb-3">
                            <label for="roomnumber" class="form-label">Zimmer-Nummer</label>
                            <input type="text" value="<?php echo $room_no ?>" class="form-control " id="roomnumber" disabled>
                            <input type="hidden" value="<?php echo $room_no ?>" name="roomnumber">
                        </div>
                        <div class="mb-3">
                            <label for="breakfast" class="form-label">Frühstück</label>
                            <input type="text" value="<?php echo $breakfast ?>" class="form-control " id="breakfast" disabled>
                            <input type="hidden" value="<?php echo $breakfast ?>" name="breakfast">
                        </div>
                        <div class="mb-3">
                            <label for="parkin" class="form-label">Parkplatz</label>
                            <input type="text" value="<?php echo $parking ?>" class="form-control " id="parkin" disabled>
                            <input type="hidden" value="<?php echo $parking ?>" name="parking">
                        </div>

                        <div class="mb-3">
                            <label for="pet" class="form-label">Haustier</label>
                            <input type="text" value="<?php echo $pet ?>" class="form-control " id="pet" disabled>
                            <input type="hidden" value="<?php echo $pet ?>" name="pet">
                        </div>
                        <div class="mb-3">
                            <label for="nights" class="form-label">Nächte</label>
                            <input type="text" value="<?php echo $nights ?>" class="form-control " id="nights" disabled>
                            <input type="hidden" value="<?php echo $nights ?>" name="nights">
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Preis p.N.</label>
                            <input type="text" value="<?php echo $price ?>€" class="form-control " id="price" disabled>
                            <input type="hidden" value="<?php echo $price ?>" name="price">
                        </div>
                        <div class="mb-3">
                            <label for="total" class="form-label">Preis insg.</label>
                            <input type="text" value="<?php echo $total ?>€" class="form-control " id="total" disabled>
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