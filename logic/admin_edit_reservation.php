<?php
$errors = [];
$errors["connection"] = false;
$errors["update"] = false;
$updated = false;


// if admin clicks on update, reservation status will be changed with this code
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update"])
) {
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["connection"] = true;
    }
    // get id and new status of reservation via POST
    $id = $_POST["id"];
    $status = $_POST["status"];
    // update reservation with given new status
    $sql = "UPDATE `reservation` SET `status`='$status' WHERE `id`='$id'";
    if ($db_obj->query($sql)) {
        $updated = true;
    }
    $db_obj->close();
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
    <!-- alerts for different edge cases, set booleans false again so they are not always true  -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6 offset-sm-3 text-center">
                <?php if ($errors["connection"] || $errors["update"]) {
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
            </div>
        </div>
    </div>
    <?php
    // output of a dropdown list where you can choose to filter the reservations
    // only shown if button filter and view have not been clicked
    if (!isset($_POST["filter"]) && !isset($_POST["view"])) { ?>
        <div class="container-fluid">
            <form method="POST">
                <div class="row">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="username" class="form-label">Reservierungs-Status:</label>
                        <select name="status" class="form-select" aria-label="Default select example" required>
                            <option value="Neu">Neu</option>
                            <option value="Bestätigt">Bestätigt</option>
                            <option value="Storniert">Storniert</option>
                        </select>
                    </div>
                    <div class="col-sm-10 offset-sm-1 text-center">
                        <input type="hidden" name="filter">
                        <button class="btn btn-primary mt-3">Filtern</button>
                    </div>
                </div>
            </form>
        </div>
    <?php } ?>
    <?php
    // dropdown list with all filtered reservations
    // this list gets the filter via POST from the previous form
    if (!isset($_POST["view"]) && isset($_POST["filter"])) {
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $status = $_POST["status"];
        // inner join to get all filtered reservations, we used an inner join so we can display the reservations with the corresponding customer for easier editing for the admin
        $sql = "SELECT r.id, r.checkin, r.checkout, u.firstname, u.secondname FROM `reservation` as r INNER JOIN `users` as u ON r.user_id=u.id  WHERE r.status='$status' ORDER BY r.checkin, r.checkout, u.secondname, u.firstname";
        $result = $db_obj->query($sql);
        // only show list of reservations if there any with given filter
        if ($result->num_rows > 0) { ?>
            <div class="container-fluid">
                <form method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <label for="username" class="form-label">Reservierungen</label>
                            <select name="id" class="form-select" aria-label="Default select example" required>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <option value="<?php echo $row["id"] ?>"><?php echo $row['secondname'] . " " . $row["firstname"] . ": " . $row['checkin'] . " bis " . $row["checkout"]; ?></option>
                                <?php endwhile ?>
                            </select>
                        </div>
                        <div class="col-sm-10 offset-sm-1 text-center">
                            <input type="hidden" name="view">
                            <button class="btn btn-primary mt-3">Bearbeiten</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php
        // otherwise show this alert
        } else { ?>
            <div class="col-sm-6 offset-sm-3 text-center">
                <div class="alert alert-primary text-center" role="alert">
                    Es gibt momentan keine Reservierungen mit dem ausgewählten Filter!
                </div>
            </div>
    <?php header("Refresh: 2, url=admin_reservierungsverwaltung.php");
        }
        $db_obj->close();
    } ?>
    <?php
    // form for updating chosen reservation
    // the previous form gets the id of the reservation via POST
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
        // inner join to view all details about the reservation and the room
        $sql = "SELECT * FROM `reservation` INNER JOIN `rooms`ON reservation.room=rooms.room_number WHERE reservation.id = '$id'";
        $result = $db_obj->query($sql);
        $row = $result->fetch_assoc(); ?>
        <div class="container-fluid">
            <form method="POST">
                <div class="row">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <div class="mb-3">
                            <label for="checkin" class="form-label">Check-In</label>
                            <input type="date" value="<?php echo $row["checkin"] ?>" class="form-control " name="checkin" aria-label="Check-In" id="checkin-input" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="checkout" class="form-label">Check-Out</label>
                            <input type="date" value="<?php echo $row["checkout"] ?>" class="form-control " name="checkout" id="checkout" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="roomtype" class="form-label">Zimmer-Art</label>
                            <input type="text" value="<?php echo $row["type"] ?>-Zimmer" class="form-control " name="type" id="roomtype" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="roomnumber" class="form-label">Zimmer-Nummer</label>
                            <input type="text" value="<?php echo $row["room_number"] ?>" class="form-control " name="roomnumber" id="roomnumber" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="breakfast" class="form-label">Frühstuck</label>
                            <input type="text" value="<?php echo $row["breakfast"] ?>" class="form-control " name="breakfast" id="breakfast" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="parkin" class="form-label">Parkplatz</label>
                            <input type="text" value="<?php echo $row["parking"] ?>" class="form-control " aria-label="Parkplatz" name="parking" id="parking" disabled>
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
                            <select class="form-select" name="status" aria-label="Default select example" <?php if ($row['status'] == "Storniert") { ?>disabled<?php } ?>>
                                <option value="Neu" <?php if ($row['status'] == "Neu") { ?> selected <?php } ?>>Neu</option>
                                <option value="Bestätigt" <?php if ($row['status'] == "Bestätigt") { ?> selected <?php } ?>>Bestätigt</option>
                                <option value="Storniert" <?php if ($row['status'] == "Storniert") { ?> selected <?php } ?>>Storniert</option>
                            </select>
                        </div>
                        <?php if ($row['status'] != "Storniert") { ?>
                            <div class="mb-6">
                                <input type="hidden" value="<?php echo $row["id"] ?>" class="form-control " name="id" id="id">
                                <input type="hidden" name="update">
                                <button class="btn btn-primary">Aktualisieren</button>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    <?php $db_obj->close();
    }
    ?>
</body>

</html>