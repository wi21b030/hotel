Hinweise:

- Damit der File-Upload bei der Blogverwaltung durch den Administrator funktionieren kann, muss 
  die php-Extension "gd" installiert sein. Hier finden Sie einen Link zur Installationsanleitung: 
  https://www.geeksforgeeks.org/how-to-install-php-gd-in-windows/

- Database-Access (Kopie der Datenbank befindet sich im config-Ordner):
  $host = "localhost";
  $user = "royal";
  $password = "royalespire";
  $database = "royalespire";

- Admin-Login-Daten für Website:
  Username: admin
  Passwort: 12345678

- Beispiel-User-Login-Daten für Website:
  Username: hadi
  Passwort: 23456789

  Username: kevin
  Passwort: 34567890

- Wenn Sie Google-Chrome verwenden, kann es zu Problemen kommen, wenn man Bilder ändert beziehungsweise löscht (bspw. Profilbild-Änderung). 
  Um dies zu vermeiden wurde mithilfe eines Stack-Overflow-Posts dies umgangen
  Siehe: https://stackoverflow.com/questions/2089559/picture-is-not-refreshing-in-my-browser
  Daher wird bei jeder Bildausgabe (es sei denn es ist ein statisches Bild) die Zeit mithilfe von time() am sourcepath angehängt

  
