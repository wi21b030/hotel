<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Dashboard</title>
</head>

<body>
    <?php include "inc/nav.php"; ?>
    <?php if (isset($_SESSION["admin"]) && $_SESSION["admin"]) {
        include "inc/admin_tools.php";
    } else {
        header("Location: index.php");
    }
    ?>
</body>

</html>