<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    session_destroy();
    header("Location: login.php");
}
?>

<?php include "bootstrap.php"; ?>
<nav class="navbar navbar-expand-lg bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">The Royal Espire Hotel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (!isset($_SESSION["username"])) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                <?php } elseif (isset($_SESSION["username"])) { ?>
                    <li class="navbar-item">
                        <a class="nav-link" href="mein_profil.php">
                            <img src="<?php echo $_SESSION["profilepic"] . "?" . time() ?>" width="30" height="30" class="d-inline-block align-top rounded-circle" alt="nav_profilbild"> <?php echo $_SESSION["username"]; ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_userverwaltung.php">User-Verwaltung</a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && !$_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reservierung.php">Reservierung</a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && !$_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="meine_reservierungen.php">Meine Reservierungen</a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_reservierungsverwaltung.php">Reservierungs-Verwaltung</a>
                    </li>
                <?php } ?>
                <?php if (!isset($_SESSION["username"])) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="registrierung.php">Registrierung</a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                        <a class="nav-link" href="blog.php">Blog-Verwaltung</a>
                    <?php } else { ?>
                        <a class="nav-link" href="blog.php">Blog</a>
                    <?php } ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hilfe.php">Hilfe</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="impressum.php">Impressum</a>
                </li>
            </ul>
            <?php if (isset($_SESSION["username"])) { ?>
                <form class="d-flex" method="POST">
                    <button class="btn btn-outline-danger" name="logout" type="submit">Logout</button>
                </form>
            <?php } ?>
        </div>
    </div>
</nav>