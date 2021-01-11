# CoronaAmpel
Eine Website, um einen einfacheren Überblick zu behalten was man aktuell darf und was nicht

# Was macht diese Website?

Sie bietet ein einfaches Interface, sowohl für Verwaltende als auch für den Endbenutzer, um via einer Ampel anzuzeigen,
was aktuell erlaubt ist und was nicht.

![Screenshot](enduserview.png?raw=true "Enduserview")
![Screenshot](adminview.png?raw=true "Adminview")

# Installation

Damit die Seite funktioniert, braucht man eine PHP-Instanz (z.B. mit Apache) und eine MySQL- oder MariaDB-Datenbank auf Linux.
Das Passwort für die MariaDB muss in der einzigen Zeile in

> /etc/vvzdbpw

stehen. Wenn das der Fall ist und die Dateien auf den Webserver kopiert worden sind, kann man einmalig im
Browser zur Installation die /admin.php aufrufen. Damit werden automatisch die DB-Tabellen eingerichtet und konfiguriert.
Danach steht die Website zur Verfügung.
