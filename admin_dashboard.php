<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin-Dashboard</title>
    <link href="inc/style.css" rel="stylesheet">

</head>

<body>
    <?php include "inc/nav.php"; ?>
    <div class="container-fluid text-center" style="margin-top: 50px; ">
        <div class="row">
            <div class="col-md-4 mb-3 d-flex justify-content-center">
                <div class="card" style="width: 180px; height:auto;">
                    <img src="uploads/icons/user.png" class="card-img-top dashboard-size" alt="...">
                    <div class="card-body">
                        <h5>User-Verwaltung</h5>
                        <a href="admin_userverwaltung.php" class="btn btn-success">Bearbeiten</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 d-flex justify-content-center">
                <div class="card" style="width: 180px; height:auto;">
                    <img src="uploads/icons/appointment.png" class="card-img-top dashboard-size" alt="...">
                    <div class="card-body">
                        <h5>Reservierungs-Verwaltung</h5>
                        <a href="admin_reservierungsverwaltung.php" class="btn btn-success">Bearbeiten</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 d-flex justify-content-center">
                <div class="card" style="width: 180px; height:auto;">
                    <img src="uploads/icons/blog.png" class="card-img-top dashboard-size" alt="...">
                    <div class="card-body">
                        <h5>Blog-Verwaltung</h5>
                        <a href="blog.php" class="btn btn-success">Bearbeiten</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>