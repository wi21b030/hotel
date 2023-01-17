<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reservierung</title>
</head>

<body>
    <?php include "inc/nav.php"; ?>
    <?php if (isset($_SESSION["admin"]) && !$_SESSION["admin"]) {
        include "logic/reservation.php";
    } else {
        header("Location: index.php");
    }
    ?>
</body>

</html>