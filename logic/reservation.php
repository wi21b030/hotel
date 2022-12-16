<?php
$errors = [];
$errors["checkin"] = false;
$errors["checkout"] = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date1 = date($_POST["checkin"],);
    $date2 = date($_POST["checkout"]);
    if (!empty($_POST["checkin"]) || !empty($_POST["checkout"])) {
        $errors["checkin"] = true;
        $errors["checkout"] = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
</head>

<body>
    <div class="container-fluid">
        <form action="reservieren.php" method="POST">
            <div class="row">
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="checkin" class="form-label">check-in</label>
                    <input type="date" name="checkin" class="form-control <?php if ($errors['checkin']) echo 'is-invalid'; ?>">
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="checkout" class="form-label">check-out</label>
                    <input type="date" name="checkout" class="form-control <?php if ($errors['checkout']) echo 'is-invalid'; ?>">
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="breakfast" class="form-label">Frühstück</label>
                    <select class="form-select" name="breakfast" aria-label="Default select example" required>
                        <option value="1">Nein</option>
                        <option value="2">Ja</option>
                    </select>
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="parking" class="form-label">Parkplatz</label>
                    <select class="form-select" name="parking" aria-label="Default select example" required>
                        <option value="1">Nein</option>
                        <option value="2">Ja</option>
                    </select>
                </div>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <label for="pet" class="form-label">Haustier</label>
                    <select class="form-select" name="pet" aria-label="Default select example" required>
                        <option value="1">Kein Haustier dabei</option>
                        <option value="2">Hund</option>
                        <option value="2">Katze</option>
                    </select>
                </div>
                <div class="col-sm-10 offset-sm-1 text-center">
                    <button type="submit" class="btn btn-primary mt-3">Buchen</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>