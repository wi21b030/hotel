<?php
// Declare variables for storing errors
$errors = [];
$errors["connection"] = false;
$updated = false;

// check if form was submitted and storno button was pressed
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["storno"])
) {
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["connection"] = true;
    }
    $id = $_POST["id"];
    //prepared statement to update status to "storniert"
    $sql = "UPDATE `reservation` SET `status`='Storniert' WHERE `id`=?";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $updated = true;
    } else {
        $errors["connection"] = true;
    }
    $stmt->close();
    $db_obj->close();
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
            <!-- header display handling -->
            <div class="col-sm-6 offset-sm-3 text-center">
                <?php if ($errors["connection"]) {
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
                if (!isset($_POST["edit"])) {
                    // dropdown list with reservations of logged in user
                    require_once('config/dbaccess.php');
                    $db_obj = new mysqli($host, $user, $password, $database);
                    if ($db_obj->connect_error) {
                        $errors["connection"] = true;
                    }
                    $user_id = $_SESSION["id"];
                    //Prepared statement to select all the reservations wiht just checkin chekout displayed
                    $sql = "SELECT * FROM `reservation` WHERE `user_id`=?";
                    $stmt = $db_obj->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) { ?>
                            <form method="POST">
                                <label for="username" class="form-label">Reservierungen</label>
                                <select name="id" class="form-select" aria-label="Default select example" required>
                                    <?php while ($row = $result->fetch_assoc()) : ?>
                                        <option value="<?php echo $row["id"] ?>"><?php echo $row["checkin"] . " bis " . $row["checkout"] ?></option>
                                    <?php endwhile ?>
                                </select>
                                <div class="col-sm-10 offset-sm-1 text-center">
                                    <input type="hidden" name="edit" value="edit">
                                    <button class="btn btn-primary mt-3">Details ansehen</button>
                                </div>
                            </form>
                        <?php
                        } else { ?>
                            <div class="alert alert-primary text-center" role="alert">
                                Sie haben momentan keine Reservierungen!
                            </div>
                        <?php
                            header("Refresh: 2, url=reservierung.php");
                        }
                    } else { ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Probieren Sie es später nocheinmal!
                        </div>
                <?php
                        header("Refresh: 2, url=reservierung.php");
                    }
                    $stmt->close();
                    $db_obj->close();
                }
                ?>
            </div>
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
                //Inner Join with table rooms to get the whole data regarding reservations
                $sql = "SELECT * FROM `reservation` INNER JOIN `rooms`ON reservation.room=rooms.room_number WHERE reservation.id=?";
                $stmt = $db_obj->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc(); ?>
                    <!-- form used to display all the reservation data -->
                    <form method="POST">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <div class="mb-3">
                                <label for="bookingtime" class="form-label">Reservierungs-Datum</label>
                                <input type="text" value="<?php echo date("d.m.y - h:m", $row["time"]) ?>" class="form-control " name="bookingtime" id="bookingtime" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="checkin" class="form-label">Check-In</label>
                                <input type="date" value="<?php echo $row["checkin"] ?>" class="form-control " name="checkin" id="checkin" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="checkout" class="form-label">Check-Out</label>
                                <input type="date" value="<?php echo $row["checkout"] ?>" class="form-control " name="checkout" id="checkout" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="roomtype" class="form-label">Zimmer-Art</label>
                                <input type="text" value="<?php echo $row["type"] ?>-Zimmer" class="form-control " name="type" id="type" aria-label="Zimmer-Art" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="roomnumber" class="form-label">Zimmer-Nummer</label>
                                <input type="text" value="<?php echo $row["room_number"] ?>" class="form-control " name="roomnumber" id="roomnumber" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="breakfast" class="form-label">Frühstück</label>
                                <input type="text" value="<?php echo $row["breakfast"] ?>" class="form-control " name="breakfast" id="breakfast" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="parkin" class="form-label">Parkplatz</label>
                                <input type="text" value="<?php echo $row["parking"] ?>" class="form-control " name="parking" id="parking" aria-label="parking" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="pet" class="form-label">Haustier</label>
                                <input type="text" value="<?php echo $row["pet"] ?>" class="form-control " name="pet" id="pet" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="nights" class="form-label">Nächte</label>
                                <input type="text" value="<?php echo $row["nights"] ?>" class="form-control " name="nights" id="nights" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Preis p.N.</label>
                                <input type="text" value="<?php echo $row["total"] / $row["nights"] ?>€" class="form-control " name="price" id="price" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="total" class="form-label">Preis insg.</label>
                                <input type="text" value="<?php echo $row["total"] ?>€" class="form-control " name="total" id="total" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" aria-label="Default select example" disabled>
                                    <option value="Neu" <?php if ($row['status'] == "Neu") { ?> selected <?php } ?>>Neu</option>
                                    <option value="Bestätigt" <?php if ($row['status'] == "Bestätigt") { ?> selected <?php } ?>>Bestätigt</option>
                                    <option value="Storniert" <?php if ($row['status'] == "Storniert") { ?> selected <?php } ?>>Storniert</option>
                                </select>
                            </div>
                            <?php if ($row['status'] != "Storniert") { ?>
                                <div class="mb-6">
                                    <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                                    <button type="submit" name="storno" class="btn btn-danger mt-3">Stornieren</button>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                <?php } else { ?>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <div class="alert alert-danger text-center" role="alert">
                            Probieren Sie es später nocheinmal!
                        </div>
                    </div>
                <?php
                    header("Refresh: 2, url=reservierung.php");
                }
                $stmt->close();
                $db_obj->close(); ?>
            <?php } ?>
        </div>
    </div>
</body>

</html>