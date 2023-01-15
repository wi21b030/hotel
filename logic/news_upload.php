<?php
$uploadDirPic = "uploads/news/";
$errors = [];
$errors["exists"] = false;
$errors["upload"] = false;
$errors["connection"] = false;
$errors["delete"] = false;
$uploaded = false;
$deleted = false;

// checks if upload directory for thumbnails is not made and if not then it creates the directory
if (!file_exists($uploadDirPic)) {
    mkdir($uploadDirPic);
}

// resample image here, only works if extension 'gd' installed -> returns true if resampled
function thumbnailmade($pic, $path, $extension)
{
    $made = false;
    // get the width and height of given given image
    list($width, $height) = getimagesize($pic);
    // calculate ratio to make adjustment of image smoother
    // we create thumbnails of a maximum width or height of 300px
    $ratio = $width / $height;
    if ($ratio > 1) {
        $nwidth = 300;
        $nheight = 300 / $ratio;
    } else {
        $nwidth = 300 * $ratio;
        $nheight = 300;
    }
    $newimage = imagecreatetruecolor($nwidth, $nheight);
    /* depending on if picture is png or jpg/jpeg we use different methods here
    used function imagecopyresampled instead of imagecopyresized because the first one delivers better quality
    depending on if picture is png or jpg/jpeg we use different methods here
    if thumbnail is made in given path then return true */
    if ($extension == "png") {
        $source = imagecreatefrompng($pic);
        imagecopyresampled($newimage, $source, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);
        if (imagepng($newimage, $path)) {
            $made = true;
        }
    } else {
        $source = imagecreatefromjpeg($pic);
        imagecopyresampled($newimage, $source, 0, 0, 0, 0, $nwidth, $nheight, $width, $height);
        if (imagejpeg($newimage, $path)) {
            $made = true;
        }
    }
    return $made;
}

// delete of chosen blog post
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete"])
) {
    require_once('config/dbaccess.php');
    $db_obj = new mysqli($host, $user, $password, $database);
    if ($db_obj->connect_error) {
        $errors["delete"] = true;
    }
    // prepared delete-query
    $id = $_POST["id"];
    $sql = "DELETE FROM `news` WHERE `id` = ?";
    $stmt = $db_obj->prepare($sql);
    $stmt->bind_param("i", $id);

    $path = $_POST["path"];
    if ($stmt->execute() && unlink($path)) {
        $deleted = true;
    } else {
        $errors["delete"] = true;
    }
    $stmt->close();
    $db_obj->close();
}

// insert of new blog post
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["upload"])
) {
    if (
        // check if input is valid
        !empty($_POST["title"])
        && !empty($_POST["text"])
        && isset($_FILES["file"])
        && !empty($_FILES["file"])
        && !empty($_POST["keyword"])
    ) {
        // get extension of choses file to check if truly image has been selected
        $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') {
            require_once('config/dbaccess.php');
            $db_obj = new mysqli($host, $user, $password, $database);
            if ($db_obj->connect_error) {
                $errors["connection"] = true;
            }
            $title = htmlspecialchars($_POST["title"], ENT_QUOTES);
            $uploadtime = time();
            $text = htmlspecialchars($_POST["text"], ENT_QUOTES);
            $pic = $_FILES["file"]["tmp_name"];
            $path = $uploadDirPic . $title . ".jpg";
            $keyword = htmlspecialchars($_POST["keyword"], ENT_QUOTES);

            // prepared insert-query to ensure protection against SQL-Injection
            $sql = "INSERT INTO `news` (`title`, `uploadtime`, `text`, `path`, `keyword`) VALUES (?,?,?,?,?)";
            $stmt = $db_obj->prepare($sql);
            $stmt->bind_param("sisss", $title, $uploadtime, $text, $path, $keyword);

            // prepared select-query to ensure protection against SQL-Injection
            $sql = "SELECT * FROM `news` WHERE `title`=?";
            $check = $db_obj->prepare($sql);
            $check->bind_param("s", $title);
            if ($check->execute()) {
                $result = $check->get_result();
                // check if news-blog-post with same title already exists
                if ($result->num_rows > 0) {
                    $errors["exists"] = true;
                } else {
                    // if insert executed and thumbnailmade show
                    if ($stmt->execute() && thumbnailmade($pic, $path, $extension)) {
                        $uploaded = true;
                    } else {
                        $errors["upload"] = true;
                    }
                }
            } else {
                $errors["upload"] = true;
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
            <!-- alerts for different edge cases or success -->
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
                                <input type="hidden" name="upload">
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif ?>
        </div>
        <!-- output of news blog posts -->
        <?php if (file_exists($uploadDirPic)) {
            require_once('config/dbaccess.php');
            $db_obj = new mysqli($host, $user, $password, $database);
            if ($db_obj->connect_error) {
                $errors["connection"] = true;
            }
            // select-query to get all news-posts ordered uploadtime descending
            $sql = "SELECT * FROM `news` ORDER BY `uploadtime` DESC";
            $result = $db_obj->query($sql);
            if ($result) {
                if ($result->num_rows > 0) { ?>
                    <div class="container-fluid">
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
                                        <!-- if admin is logged in they will see the delete button -->
                                        <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                                            <div class="col-9">
                                                <div class="mb-2">
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                                                        <input type="hidden" name="path" value="<?php echo $row["path"] ?>">
                                                        <input type="hidden" name="delete">
                                                        <button type="submit" class="btn btn-danger">Löschen</button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                    <!-- if normal user or not registered user is on the news-page and there are no posts they will see this alert -->
                <?php } elseif (!isset($_SESSION["admin"]) || (isset($_SESSION["admin"]) && !$_SESSION["admin"])) { ?>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6 offset-sm-3 text-center">
                                <div class="alert alert-primary text-center" role="alert">
                                    Es gibt momentan keine Beiträge!
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6 offset-sm-3 text-center">
                            <div class="alert alert-danger text-center" role="alert">
                                Fehler bei der Abfrage!
                            </div>
                        </div>
                    </div>
                </div>
            <?php header("Refresh: 2, url=index.php");
            }
            $db_obj->close(); ?>
        <?php } ?>
    </div>
</body>

</html>