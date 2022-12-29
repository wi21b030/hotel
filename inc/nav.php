<?php
session_start();
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
                <?php } elseif (isset($_SESSION["username"]) && !$_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="mein_profil.php">Mein Profil</a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_profilverwaltung.php">Profil-Verwaltung</a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && !$_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reservierung.php">Reservierung</a>
                    </li>
                <?php } ?>
                <?php if (isset($_SESSION["username"]) && !$_SESSION["admin"]) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="eigene_reservierungen.php">Meine Reservierungen</a>
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
                    <a class="nav-link" href="blog.php"><?php if (isset($_SESSION["username"]) && $_SESSION["admin"]) { echo "Blog-Verwaltung"; } else { echo "Blog"; }?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hilfe.php">Hilfe</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="impressum.php">Impressum</a>
                </li>
            </ul>
            <?php if (isset($_SESSION["username"])) { ?>
                <form action="logic/logout.php" class="d-flex">
                    <button class="btn btn-outline-danger" type="submit">Logout</button>
                </form>
            <?php } ?>
        </div>
    </div>
</nav>