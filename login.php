<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>

<body>
    <?php include "inc/nav.php"; ?>
    <?php if (!isset($_SESSION["username"])) {
        include "logic/login_logic.php";
    } else {
        header("Location: index.php");
    }
    ?>
</body>

</html>