<?php
$uploadDirPic = "uploads/news/pic/";
$errors = [];
$errors["exists"] = false;
$errors["upload"] = false;
$errors["connection"] = false;
$errors["delete"] = false;
$uploaded = false;
$deleted = false;

if (!file_exists($uploadDirPic)) {
    mkdir($uploadDirPic);
}

// resample image here, only works if extension 'gd' installed -> returns true if resampled
function thumbnailmade($pic, $path)
{
    $made = false;
    list($width, $height) = getimagesize($pic);
    // calculate ratio to make adjusment of image smoother
    $ratio = $width / $height;
    if ($ratio > 1) {
        $nwidth = 300;
        $nheight = 300 / $ratio;
    } else {
        $nwidth = 300 * $ratio;
        $nheight = 300;
    }
    // if instead we want to use specific ratio then use commented code below
    // $nwidth = $width * 0.75;
    // $nheight = $height* 0.75;
    $newimage = imagecreatetruecolor($nwidth, $nheight);
    $source = imagecreatefromjpeg($pic);
    // used function imagecopyresampled instead of imagecopyresized because the first one delivers better quality
    imagecopyresampled($newimage, $source, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);
    if (imagejpeg($newimage, $path)) {
        $made = true;
    }
    return $made;
}

// delete of chosen blog post
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete"])
    && $_POST["delete"] === "delete"
) {
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["delete"] = true;
    }

    $id = $_POST["id"];
    $sql = "DELETE FROM `news` WHERE `id` = ?";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("i", $id);

    $sql = "SELECT * FROM `news` WHERE `id` = '$id'";
    $result = $db_obj->query($sql);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (unlink($row["path"]) && $stmt->execute()) {
            $deleted = true;
        } else {
            $errors["delete"] = true;
        }
    } else {
        $errors["delete"] = true;
    }
    $stmt->close();
    $db_obj->close();
}

// insert of new blog post
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["uploaden"])
    && $_POST["uploaden"] === "uploaden"
) {
    if (
        !empty($_POST["title"])
        && !empty($_POST["text"])
        && isset($_FILES["file"])
        && !empty($_FILES["file"])
        && !empty($_POST["keyword"])
    ) {
        $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
            require_once('config/dbaccess.php');
            $db_obj = new mysqli($host, $user, $password, $database);
            if ($db_obj->connect_error) {
                $errors["connection"] = true;
            }
            $title = $_POST["title"];
            $uploadtime = time();
            $text = $_POST["text"];
            $pic = $_FILES["file"]["tmp_name"];
            $path = $uploadDirPic . $title . ".jpg";
            $keyword = $_POST["keyword"];

            $sql = "INSERT INTO `news` (`title`, `uploadtime`, `text`, `path`, `keyword`) VALUES (?,?,?,?,?)";
            $stmt = $db_obj->prepare($sql);
            $stmt->bind_param("sisss", $title, $uploadtime, $text, $path, $keyword);

            $sql = "SELECT * FROM `news` WHERE `title`=?";
            $check = $db_obj->prepare($sql);
            $check->bind_param("s", $title);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows > 0) {
                $errors["exists"] = true;
            } else {
                if ($stmt->execute() && thumbnailmade($pic, $path)) {
                    $uploaded = true;
                } else {
                    $errors["upload"] = true;
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
    <title>Blog</title>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) : ?>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <?php if ($uploaded) {
                        $uploaded = false;
                        header("Refresh: 2, url=blog.php");
                    ?>
                        <div class="alert alert-success text-center" role="alert">
                            Beitrag wurde hochgeladen!
                        </div>
                    <?php } ?>
                    <?php if ($errors["upload"] || $errors["connection"]) {
                        $errors["upload"] = false;
                        $errors["connection"] = false;
                        header("Refresh: 2, url=blog.php");
                    ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Upload nicht möglich aufgrund fehlender oder fehlerhafter Daten!
                        </div>
                    <?php } ?>
                    <?php if ($errors["exists"]) {
                        $errors["exists"] = false;
                        header("Refresh: 2, url=blog.php");
                    ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Upload nicht möglich, Beitrag mit gleichem Titel existiert bereits!
                        </div>
                    <?php } ?>
                    <?php if ($deleted) {
                        $deleted = false;
                        header("Refresh: 2, url=blog.php");
                    ?>
                        <div class="alert alert-primary text-center" role="alert">
                            Beitrag wurde gelöscht!
                        </div>
                    <?php } ?>
                    <?php if ($errors["delete"]) {
                        $errors["delete"] = false;
                        header("Refresh: 2, url=blog.php");
                    ?>
                        <div class="alert alert-danger text-center" role="alert">
                            Beitrag konnte nicht gelöscht werden!
                        </div>
                    <?php } ?>
                </div>
                <form enctype="multipart/form-data" method="POST">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <div class="mb-2">
                                <label for="exampleInputEmail1" class="form-label">Überschrift</label>
                                <input type="text" name="title" class="form-control <?php if ($errors["exists"]) echo 'is-invalid'; ?>" aria-label="Überschrift" id="title" required>
                            </div>
                            <div class="mb-2">
                                <label for="exampleInputEmail1" class="form-label">Keywords</label>
                                <input type="text" name="keyword" class="form-control <?php if ($errors["exists"]) echo 'is-invalid'; ?>" aria-label="Keywords" id="keyword" required>
                            </div>
                            <div class="mb-2">
                                <label for="exampleFormControlTextarea1">Dazugehöriger Text</label>
                                <textarea class="form-control <?php if ($errors["exists"]) echo 'is-invalid'; ?>" name="text" aria-label="Text" id="text" rows="3"></textarea>
                            </div>
                            <div class="mb-2">
                                <label for="formFile" class="form-label">Thumbnail</label>
                                <input class="form-control <?php if ($errors["exists"]) echo 'is-invalid'; ?>" name="file" type="file" aria-label="Thumbnail" id="formFile" accept="image/*" required>
                            </div>
                            <div class="mb-2">
                                <input type="hidden" name="uploaden" value="uploaden">
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif ?>
        </div>
        <!-- output of news blog posts -->
        <?php if (file_exists($uploadDirPic)) { ?>
            <?php
            require_once('config/dbaccess.php');
            $db_obj = new mysqli($host, $user, $password, $database);
            if ($db_obj->connect_error) {
                $errors["connection"] = true;
            }
            $sql = "SELECT * FROM `news` ORDER BY `uploadtime` DESC";
            $result = $db_obj->query($sql); ?>
            <?php if ($result->num_rows > 0) { ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <a style="text-decoration: none" href="https://www.1000things.at/suche/<?php echo $row["keyword"] ?>" class="text-dark">
                        <div class="row mt-2 border-bottom pb-2">
                            <div class="col-3">
                                <img src="<?php echo $row["path"] . "?" . time() ?>" class="img-fluid shadow-1-strong rounded" alt="<?php $row["title"] ?>" />
                            </div>
                            <div class="col-9">
                                <p class="mb-2"><strong><?php echo $row["title"] ?></strong></p>
                                <p>
                                    <?php echo $row["text"];
                                    echo "<br><u>" . date("d.m.Y", $row["uploadtime"]) . "</u>";
                                    ?>
                                </p>
                                <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                                    <div class="col-9">
                                        <div class="mb-2">
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                                                <input type="hidden" name="delete" value="delete">
                                                <button type="submit" class="btn btn-danger">Löschen</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            <?php } elseif (!isset($_SESSION["admin"]) || (isset($_SESSION["admin"]) && !$_SESSION["admin"])) { ?>
                <div class="col-sm-6 offset-sm-3 text-center">
                    <div class="alert alert-primary text-center" role="alert">
                        Es gibt momentan keine Beiträge!
                    </div>
                </div>
            <?php
            }
            $db_obj->close(); ?>
        <?php } ?>
    </div>
</body>

</html>