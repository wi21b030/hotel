<?php
$uploadDirPic = "./uploads/news/pic/";
$errors = [];
$errors["exists"] = false;
$errors["upload"] = false;
$errors["connection"] = false;

if (!file_exists($uploadDirPic)) {
    mkdir($uploadDirPic);
}

// Delete vom News-Upload
if($_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete"])
    && $_POST["delete"] === "delete") {
        //
}

// Insert vom News-Upload
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["uploaden"])
    && $_POST["uploaden"] === "uploaden") {
    if (
        !empty($_POST["title"])
        && !empty($_POST["text"])
        && isset($_FILES["file"])
        && !empty($_FILES["file"])
    ) {
        $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
            require_once('config/dbaccess.php');
            $db_obj = new mysqli($host, $user, $password, $database);
            if ($db_obj->connect_error) {
                $errors["connection"] = true;
                exit();
            }
            $title = $_POST["title"];
            $uploadtime = time();
            $text = $_POST["text"];
            $pic = $_FILES["file"]["tmp_name"];
            $path = $uploadDirPic . $title . ".jpg";

            $sql = "INSERT INTO `news` (`title`, `uploadtime`, `text`, `path`) VALUES (?,?,?,?)";
            $stmt = $db_obj->prepare($sql);
            $stmt->bind_param("siss", $title, $uploadtime, $text, $path);

            $sql = "SELECT * FROM `news` WHERE `title` = '$title' AND `text` = '$text'";
            $result = $db_obj->query($sql);
            if ($result->num_rows > 0) {
                $errors["exists"] = true;
            } else {
                if ($stmt->execute()) {
                    move_uploaded_file($pic, $path);
                } else {
                    $errors["connection"] = true;
                }
            }
            $stmt->close();
            $db_obj->close();
        } else {
            $errors["upload"] = true;
        }
    } else {
        $errors["upload"] = true;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload</title>
</head>

<body>
    <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) : ?>
        <div class="container-fluid">
            <?php if ($errors["upload"] || $errors["connection"]) {
                $errors["upload"] = false;
                $errors["connection"] = false;
                header("Refresh: 2, url=blog.php"); ?>
                <div class="alert alert-danger text-center" role="alert">
                    Upload nicht möglich aufgrund fehlender oder fehlerhafter Daten!
                </div>
            <?php } ?>
            <?php if ($errors["exists"]) {
                $errors["exists"] = false;
                header("Refresh: 2, url=blog.php"); ?>
                <div class="alert alert-danger text-center" role="alert">
                    Upload nicht möglich, Beitrag mit gleichem Titel und Text existiert bereits!
                </div>
            <?php } ?>
            <form enctype="multipart/form-data" method="POST">
                <div class="row">
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="exampleInputEmail1" class="form-label">Überschrift</label>
                        <input type="text" name="title" class="form-control <?php if ($errors["exists"]) echo 'is-invalid'; ?>" id="exampleInputEmail1">
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="exampleFormControlTextarea1">Dazugehöriger Text</label>
                        <textarea class="form-control <?php if ($errors["exists"]) echo 'is-invalid'; ?>" name="text" id="exampleFormControlTextarea1" rows="3"></textarea>
                    </div>
                    <div class="col-sm-6 offset-sm-3 text-center">
                        <label for="formFile" class="form-label">Profilbild</label>
                        <input class="form-control <?php if ($errors["exists"]) echo 'is-invalid'; ?>" name="file" type="file" id="formFile" accept="image/*">
                    </div>
                    <div class="col-sm-10 offset-sm-1 text-center">
                        <input type="hidden" name="uploaden" value="uploaden">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif ?>
    <!-- Ausgabe ver News-Beiträge -->
    <?php if (file_exists($uploadDirPic)) : ?>
        <?php
        require_once('config/dbaccess.php');
        $db_obj = new mysqli($host, $user, $password, $database);
        if ($db_obj->connect_error) {
            $errors["connection"] = true;
        }
        $sql = "SELECT * FROM `news` ORDER BY `uploadtime` DESC";
        $result = $db_obj->query($sql); ?>
        <?php if ($result->num_rows > 0) : ?>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <a style="text-decoration: none" href="" class="text-dark">
                    <div class="row mb-4 border-bottom pb-2">
                        <div class="col-3">
                            <img src="<?php echo $row["path"] ?>" class="img-fluid shadow-1-strong rounded" alt="bild<?php $row["title"] ?>" />
                        </div>
                        <div class="col-9">
                            <p class="mb-2"><strong><?php echo $row["title"] ?></strong></p>
                            <p>
                                <?php echo $row["text"];
                                echo "<br><u>" . date("d.m.Y", $row["uploadtime"]) . "</u>";
                                ?>
                            </p>
                            <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                                <form enctype="multipart/form-data" method="POST">
                                    <div class="col-9">
                                        <div class="mb-2">
                                            <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                                            <input type="hidden" name="delete" value="delete">
                                            <button type="submit" class="btn btn-danger">Löschen</button>
                                        </div>
                                    </div>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </a>
            <?php endwhile ?>
        <?php endif ?>
        <?php $db_obj->close(); ?>
    <?php endif ?>
</body>

</html>