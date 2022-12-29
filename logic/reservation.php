<!DOCTYPE html>
<html lang="en">

<?php

$errors = [];
$errors["checkin"] = false;
$a = "";
$b = "";
$price = 0;

require_once ('config/dbaccess.php');
$db_obj = new mysqli($host, $user, $password, $database);
if (isset($_POST['checkin'], $_POST['checkout'])) {
    $a = $_POST["checkin"];
    $b = $_POST["checkout"];
}
if ( $a >= $b && isset($_POST["submit_button" ] )){
    $errors["checkin"] = true;
    echo "falsches Datum";
    
    


}




if (isset($_POST['checkin'], $_POST['checkout'], $_POST['breakfast'], $_POST['parking'], $_POST['pet']) && !$errors["checkin"] && isset($_POST["submit_button" ])) {
                // Process form data
                $datenow=time();
                $datenow = date('Y-m-d H:i:s', $datenow);
                $checkin = $_POST["checkin"];
                $checkout = $_POST["checkout"];
                $breakfast = $_POST["breakfast"];
                $parking = $_POST["parking"];
                $pet = $_POST["pet"];
                $user = $_SESSION["username"];
                $iduser = $_SESSION["id"];
                $price = 50;
                if ($breakfast){
                    $price += 10;
                            }
                if ($parking){
                    $price += 3;
                }
                if ($pet){
                    $price += 5;
                }
                
                $sql = "INSERT INTO `reservation` (`checkin`, `checkout`, `breakfast`, `parking`, `pet`, `users_username`, `time`, `iduser`) VALUES (?,?,?,?,?,?,?,?)";
                $stmt = $db_obj -> prepare ($sql);
                $stmt -> bind_param("ssiiissi", $checkin, $checkout, $breakfast, $parking, $pet, $user, $datenow, $iduser);
                $stmt->execute();
                $stmt -> close();
            } else {
               


            
            }
           
            
            
           
             
            
            
?>




<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
</head>

<body>
    <div class="container-fluid">
        <form action="reservierung.php" method="POST">
            <div class="row">
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
                    <button type="submit" name="submit_button" class="btn btn-primary mt-3">Buchen</button>
                </div>
                <div class="col-sm-10 offset-sm-1 text-center">
                <?php echo ($price) . ("$"); ?>
                </div>    
            </div>
        </form>
    
        
        

    </div>
</body>

</html>