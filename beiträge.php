<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beiträge</title>
</head>

<body>
    <?php include "inc/nav.php"; ?>
    <div class="container-fluid">
        <div> 
            <?php include "logic/news_upload.php"; ?>
            <?php include "inc/staticnews.php"; ?>
        </div>
    </div>
</body>

</html>