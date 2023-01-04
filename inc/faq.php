<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ</title>
</head>

<body>
    <?php include "bootstrap.php"; ?>
    <div class="accordion" id="accordionExample">
        <?php if (!isset($_SESSION["username"])) { ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        Wie registrieren Sie sich?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <a href="registrierung.php">Hier</a> gelangen Sie zur Seite für die Registrierung. Alternativ können Sie die Seite auf unserer Homepage aufrufen indem Sie auf der Navigationsleiste auf <strong>Registrierung</strong> klicken.
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Wo können Sie Zimmer buchen?
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    <?php if (isset($_SESSION["username"]) && !$_SESSION["admin"]) { ?>
                        <a href="reservieren.php">Hier</a> gelangen Sie zur Seite zum Reservieren!
                    <?php } else { ?>
                        Wenn Sie reservieren wollen, müssen Sie <a href="registrierung.php">registriert</a> oder <a href="login.php">eingeloggt</a> sein.                        
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Wo befindet sich das Hotel?
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    Die genau Adresse finden Sie <a href="impressum.php">hier</a>. Unser Hotel befindet sich nahe dem Hauptbahnhof und kann mit der 10er Straßenbahn erreicht werden. Einfach in der Station "Irgendwogasse" aussteigen und Sie werden unserem Hotel gegenüberstehen.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Ich habe meine Zugangsdaten vergessen, was kann ich tun?
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    In diesem Fall kontaktieren Sie bitte unseren Support. Diesen erreichen Sie <a href="impressum.php">hier</a>.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFive">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    Gibt es Parkplätze?
                </button>
            </h2>
            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    Wir bieten für unsere Kunden Parkplätze an, aber bei der Reservierung muss angegeben werden, ob man ein Parkplatz in Anspruch nehmen möchte. Ansonsten gibt es keinen Anspruch auf Parkplätze und es darf in unserem Gelände nicht geparkt werden.
                </div>
            </div>
        </div>
    </div>
</body>

</html>