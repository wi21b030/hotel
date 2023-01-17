<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    session_destroy();
    header("Location: login.php");
}
?>
<?php include "bootstrap.php"; ?>
<nav class="navbar navbar-expand-lg bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php" style="color:white;">The Royal Espire Hotel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (!isset($_SESSION["username"])) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php" style="color:white;">Login</a>
                    </li>
                <?php } elseif (isset($_SESSION["username"])) { ?>
                    <li class="navbar-item">
                        <a class="nav-link" href="mein_profil.php" style="color:white;">
                            <img src="<?php echo $_SESSION["profilepic"] . "?" . time() ?>" class="d-inline-block align-top rounded-circle profilepic-size" alt="nav_profilbild"> <?php echo $_SESSION["username"]; ?>
                        </a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php" style="color:white;">Admin-Dashboard</a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && !$_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reservierung.php" style="color:white;">Reservierung</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="meine_reservierungen.php" style="color:white;">Meine Reservierungen</a>
                    </li>
                <?php } ?>
                <?php if (!isset($_SESSION["username"])) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="registrierung.php" style="color:white;">Registrierung</a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php" style="color:white;">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hilfe.php" style="color:white;">Hilfe</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="impressum.php" style="color:white;">Impressum</a>
                </li>
            </ul>
            <?php if (isset($_SESSION["username"])) { ?>
                <form class="d-flex" method="POST">
                    <button class="btn btn-danger" name="logout" type="submit">Logout</button>
                </form>
            <?php } ?>
        </div>
    </div>
</nav>