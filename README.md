KickOff — Football Information Platform

KickOff is een voetbal webapplicatie geïnspireerd op apps zoals FotMob.
Gebruikers kunnen wedstrijden, teams, spelers en competities bekijken en statistieken volgen.

Het project is gebouwd met PHP en MySQL en laat zien hoe een grotere webapplicatie met meerdere datamodellen en functionaliteiten wordt opgebouwd.

Functionaliteiten

Wedstrijd overzicht
Bekijk aankomende, live en gespeelde wedstrijden.

Wedstrijd details
Score, wedstrijd gebeurtenissen (doelpunten, kaarten, wissels) en statistieken.

Competitie standen
Volledige ranglijsten met punten, doelsaldo en top scorers.

Team pagina’s
Selectie per positie (keeper, verdediger, middenvelder, aanvaller) en recente wedstrijden.

Speler profielen
Informatie over spelers inclusief statistieken en positie.

Zoekfunctie
Zoek naar teams of spelers.

Gebruikersaccounts

Registreren

Inloggen

Favorieten opslaan (teams, spelers of competities)

Ondersteunde competities

Premier League

La Liga

Bundesliga

Serie A

Ligue 1

UEFA Champions League

Gebruikte technologieën

Backend: PHP (Object-Oriented Programming)

Database: MySQL

Frontend: HTML5, CSS3

Authenticatie: PHP Sessions + password_hash()

Projectstructuur
project-fotmob/
│
├── classes/
│   ├── Database.php
│   ├── User.php
│   ├── Match.php
│   ├── Team.php
│   ├── Player.php
│   ├── League.php
│   ├── Standing.php
│   └── Favorite.php
│
├── includes/
│   ├── config.php
│   ├── navbar.php
│   └── footer.php
│
├── css/
│   └── style.css
│
├── index.php
├── match.php
├── team.php
├── player.php
├── standings.php
├── search.php
├── favorites.php
├── login.php
├── register.php
├── logout.php
│
└── fotmob.sql
Vereisten

PHP 7.4 of hoger

MySQL

Lokale server zoals XAMPP, Laragon of WAMP

Installatie

Plaats het project in je webserver map:

/xampp/htdocs/project-fotmob/

Importeer de database:

fotmob.sql

Dit maakt de database fotmob aan met voorbeelddata.

Controleer de database instellingen in:

includes/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fotmob');

Start Apache en MySQL en open het project in je browser:

http://localhost/project-fotmob/
Database

De applicatie gebruikt 11 tabellen, waaronder:

Tabel	Beschrijving
users	Gebruikersaccounts
leagues	Competitie informatie
teams	Club informatie
players	Speler profielen
matches	Wedstrijd data
match_events	Wedstrijd gebeurtenissen
match_stats	Wedstrijd statistieken
standings	Competitie standen
player_stats	Speler statistieken
favorites	Gebruiker favorieten
Beveiliging

Wachtwoorden worden opgeslagen met password_hash()

Queries gebruiken prepared statements (bescherming tegen SQL injection)

Output wordt geescaped met htmlspecialchars()

Authenticatie via PHP sessions

Doel van dit project

Dit project is gemaakt als onderdeel van mijn software development portfolio.
Het laat zien hoe een grotere PHP webapplicatie kan worden gebouwd met meerdere datamodellen, gebruikersaccounts en database relaties.
