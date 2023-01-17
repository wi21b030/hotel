<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Profil</title>
</head>

<body>
    <?php include "inc/nav.php"; ?>
    <?php if (isset($_SESSION["username"])) {
        include "logic/user_edit_user.php";
    } else {
        header("Location: index.php");
    }
    ?>
</body>

</html>