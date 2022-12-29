<?php
   
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["connection"] = true;
        exit();
    }
    $sql = "SELECT * FROM `reservation` WHERE `iduser` = $_SESSION[id]";
    $result = $db_obj->query($sql); 

    if (isset($_POST['checkin'], $_POST['checkout'], $_POST['breakfast'], $_POST['parking'], $_POST['pet']) && isset($_POST["reserve" ])) {
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
        $reservierungID = $_POST["ReservierungID"];
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
        
        $sql = "UPDATE `reservation` SET `checkin`=?, `checkout`=?, `breakfast`=?, `parking`=?, `pet`=?, `users_username`=?, `time`=?, `iduser`=? WHERE `iduser`=$iduser AND `reservierungID` = $reservierungID ";
        $stmt = $db_obj -> prepare ($sql);
        $stmt -> bind_param("ssiiissi", $checkin, $checkout, $breakfast, $parking, $pet, $user, $datenow, $iduser);
        $stmt->execute();
        $stmt -> close();
    } else {
       


    
    }











    ?>
    
    <?php while ($row = $result->fetch_assoc()) : ?>
    <div class="container-fluid">
                <form enctype="multipart/form-data" action="eigene_reservierungen.php" method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                        
                        <div class="mb-3">
                                <label for="checkin" class="form-label">checkin</label>
                                <input type="date" value="<?php echo $row["checkin"] ?>" class="form-control " name="checkin" id="checkin" required>
                            </div>

                            <div class="mb-3">
                                <label for="checkout" class="form-label">checkout</label>
                                <input type="date" value="<?php echo $row["checkout"] ?>" class="form-control " name="checkout" id="checkout" required>
                            </div>

                            <div class="mb-3">
                                <label for="breakfast" class="form-label">breakfast</label>
                                <select class="form-select" name="breakfast" aria-label="Default select example" required>
                                    <option value="1" <?php if ($row['breakfast'] == 1) { ?> selected <?php } ?>>Ja</option>
                                    <option value="0" <?php if ($row['breakfast'] == 0) { ?> selected <?php } ?>>Nein</option>
    
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="parking" class="form-label">parking</label>
                                <select class="form-select" name="parking" aria-label="Default select example" required>
                                    <option value="1" <?php if ($row['parking'] == 1) { ?> selected <?php } ?>>Ja</option>
                                    <option value="0" <?php if ($row['parking'] == 0) { ?> selected <?php } ?>>Nein</option>
    
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="pet" class="form-label">pet</label>
                                <select class="form-select" name="pet" aria-label="Default select example" required>
                                    <option value="1" <?php if ($row['pet'] == 1) { ?> selected <?php } ?>>Ja</option>
                                    <option value="0" <?php if ($row['pet'] == 0) { ?> selected <?php } ?>>Nein</option>
    
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="ReservierungID" class="form-label"></label>
                                <input type="hidden" value="<?php echo $row["reservierungID"] ?>" class="form-control " name="ReservierungID" id="ReservierungID">
                            </div>

                            
                            <div class="mb-3">
                                <input type="hidden" name="reserve" value="reserve">
                                <button class="btn btn-primary">Updaten</button>
                            </div>

                            

                        </div>
                    </div>
                </form>
            </div>
            <?php endwhile ?>

    