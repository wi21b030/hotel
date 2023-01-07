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
    $id = $_POST["id"];
    $status = $_POST["status"];
    // simple update query
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
    <!-- alerts for different edge cases  -->
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
    if (!isset($_POST["filter"]) && !isset($_POST["view"])) { ?>
        <div class="container-fluid">
            <form method="POST">
                <div class="row">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="username" class="form-label">Reservierungs-Status:</label>
                        <select name="status" class="form-select" aria-label="Default select example" required>
                            <option value="0">Neu</option>
                            <option value="1">Bestätigt</option>
                            <option value="2">Storniert</option>
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
    if (!isset($_POST["view"]) && isset($_POST["filter"])) {
        // dropdown list with all filtered reservations
        // this list gets the filter via POST from the previous form
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $status = $_POST["status"];
        // inner join to get all filtered reservations, we used an inner join so we can display the reservations with the corresponding customer for easier editing for the admin
        $sql = "SELECT r.id, r.checkin, r.checkout, u.firstname, u.secondname FROM `reservation` as r INNER JOIN `users` as u ON r.user_id=u.id  WHERE r.status='$status' ORDER BY r.checkin, r.checkout, u.secondname, u.firstname";
        $result = $db_obj->query($sql); ?>
        <?php if ($result->num_rows > 0) { ?>
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
        <?php } ?>
    <?php $db_obj->close();
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
                            <select class="form-select" name="status" aria-label="Default select example">
                                <option value="0" <?php if ($row['status'] == 0) { ?> selected <?php } ?>>Neu</option>
                                <option value="1" <?php if ($row['status'] == 1) { ?> selected <?php } ?>>Bestätigt</option>
                                <option value="2" <?php if ($row['status'] == 2) { ?> selected <?php } ?>>Storniert</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <input type="hidden" value="<?php echo $row["id"] ?>" class="form-control " name="id" id="id">
                            <input type="hidden" name="update">
                            <button class="btn btn-primary">Aktualisieren</button>
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