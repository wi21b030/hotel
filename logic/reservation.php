<?php
$errors = [];
$errors["checkin"] = false;
$errors["checkout"] = false;
$errors["connection"] = false;
$reserved = false;
$price = 0;

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['checkin'], $_POST['checkout'], $_POST['breakfast'], $_POST['parking'], $_POST['pet'], $_POST["buchen"])
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
        $datenow = date('Y-m-d H:i:s', time());
        $breakfast = $_POST["breakfast"];
        $parking = $_POST["parking"];
        $pet = $_POST["pet"];
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

        $sql = "INSERT INTO `reservation` (`checkin`, `checkout`, `breakfast`, `parking`, `pet`, `users_username`, `time`, `user_id`) VALUES (?,?,?,?,?,?,?,?)";
        $stmt = $db_obj->prepare($sql);
        $stmt->bind_param("ssiiissi", $checkin, $checkout, $breakfast, $parking, $pet, $user, $datenow, $iduser);
        if ($stmt->execute()) {
            $reserved = true;
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
                        Reservierung konnte nicht gebucht werden!
                    </div>
                <?php } ?>
                <?php if ($reserved) {
                    $reserved = false;
                    header("Refresh: 2, url=reservierung.php");
                ?>
                    <div class="alert alert-success text-center" role="alert">
                        Reservierung wurde gebucht!
                    </div>
                <?php } ?>
            </div>
            <form method="POST">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="checkin" class="form-label">check-in</label>
                    <input type="date" name="checkin" class="form-control <?php if ($errors['checkin']) echo 'is invalid'; ?>" required>
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="checkout" class="form-label">check-out</label>
                    <input type="date" name="checkout" class="form-control <?php if ($errors['checkin']) echo 'is invalid'; ?>" required>
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="breakfast" class="form-label">Frühstück (+10$)</label>
                    <select class="form-select" name="breakfast" aria-label="Default select example" required>
                        <option value="0">Nein</option>
                        <option value="1">Ja</option>
                    </select>
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="parking" class="form-label">Parkplatz (+3$)</label>
                    <select class="form-select" name="parking" aria-label="Default select example" required>
                        <option value="0">Nein</option>
                        <option value="1">Ja</option>
                    </select>
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="pet" class="form-label">Haustier (+5$)</label>
                    <select class="form-select" name="pet" aria-label="Default select example" required>
                        <option value="0">Kein Haustier dabei</option>
                        <option value="1">Haustier dabei</option>
                    </select>
                </div>
                <div class="col-sm-10 offset-sm-1 text-center">
                    <button type="submit" name="buchen" class="btn btn-primary mt-3">Buchen</button>
                </div>
                <div class="col-sm-10 offset-sm-1 text-center">
                    <?php echo ($price) . ("$"); ?>
                </div>
            </form>
        </div>
    </div>
</body>

</html>