-- ============================================================
--  FotMob-inspired Football Site — Database + Seed Data
--  Leagues: Premier League, La Liga, Bundesliga,
--           Serie A, Ligue 1, UEFA Champions League
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────────
--  DROP (clean slate)
-- ─────────────────────────────────────────────
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS player_stats;
DROP TABLE IF EXISTS match_lineups;
DROP TABLE IF EXISTS match_stats;
DROP TABLE IF EXISTS match_events;
DROP TABLE IF EXISTS standings;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS leagues;
DROP TABLE IF EXISTS users;

-- ─────────────────────────────────────────────
--  CREATE TABLES
-- ─────────────────────────────────────────────

CREATE TABLE users (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE leagues (
    id      INT PRIMARY KEY AUTO_INCREMENT,
    name    VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    season  VARCHAR(10)  NOT NULL
);

CREATE TABLE teams (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    league_id  INT NOT NULL,
    name       VARCHAR(100) NOT NULL,
    short_name VARCHAR(10)  NOT NULL,
    stadium    VARCHAR(100),
    founded    INT,
    coach      VARCHAR(100),
    FOREIGN KEY (league_id) REFERENCES leagues(id)
);

CREATE TABLE players (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    team_id     INT NOT NULL,
    name        VARCHAR(100) NOT NULL,
    position    ENUM('GK','DEF','MID','FWD') NOT NULL,
    nationality VARCHAR(100),
    age         INT,
    number      INT,
    FOREIGN KEY (team_id) REFERENCES teams(id)
);

CREATE TABLE matches (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    league_id    INT NOT NULL,
    home_team_id INT NOT NULL,
    away_team_id INT NOT NULL,
    home_score   INT DEFAULT NULL,
    away_score   INT DEFAULT NULL,
    status       ENUM('scheduled','live','finished') NOT NULL DEFAULT 'scheduled',
    match_date   DATETIME NOT NULL,
    matchday     INT,
    venue        VARCHAR(100),
    FOREIGN KEY (league_id)    REFERENCES leagues(id),
    FOREIGN KEY (home_team_id) REFERENCES teams(id),
    FOREIGN KEY (away_team_id) REFERENCES teams(id)
);

CREATE TABLE match_events (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    match_id   INT NOT NULL,
    team_id    INT NOT NULL,
    player_id  INT,
    event_type ENUM('goal','yellow_card','red_card','substitution') NOT NULL,
    minute     INT NOT NULL,
    FOREIGN KEY (match_id)  REFERENCES matches(id),
    FOREIGN KEY (team_id)   REFERENCES teams(id),
    FOREIGN KEY (player_id) REFERENCES players(id)
);

CREATE TABLE match_stats (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    match_id        INT NOT NULL,
    team_id         INT NOT NULL,
    possession      INT DEFAULT 0,
    shots           INT DEFAULT 0,
    shots_on_target INT DEFAULT 0,
    corners         INT DEFAULT 0,
    fouls           INT DEFAULT 0,
    yellow_cards    INT DEFAULT 0,
    red_cards       INT DEFAULT 0,
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (team_id)  REFERENCES teams(id)
);

CREATE TABLE match_lineups (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    match_id    INT NOT NULL,
    team_id     INT NOT NULL,
    player_id   INT NOT NULL,
    is_starting TINYINT(1) DEFAULT 1,
    FOREIGN KEY (match_id)  REFERENCES matches(id),
    FOREIGN KEY (team_id)   REFERENCES teams(id),
    FOREIGN KEY (player_id) REFERENCES players(id)
);

CREATE TABLE standings (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    league_id      INT NOT NULL,
    team_id        INT NOT NULL,
    played         INT DEFAULT 0,
    won            INT DEFAULT 0,
    drawn          INT DEFAULT 0,
    lost           INT DEFAULT 0,
    goals_for      INT DEFAULT 0,
    goals_against  INT DEFAULT 0,
    points         INT DEFAULT 0,
    FOREIGN KEY (league_id) REFERENCES leagues(id),
    FOREIGN KEY (team_id)   REFERENCES teams(id)
);

CREATE TABLE player_stats (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    player_id      INT NOT NULL,
    season         VARCHAR(10) NOT NULL,
    matches_played INT DEFAULT 0,
    goals          INT DEFAULT 0,
    assists        INT DEFAULT 0,
    yellow_cards   INT DEFAULT 0,
    red_cards      INT DEFAULT 0,
    minutes_played INT DEFAULT 0,
    FOREIGN KEY (player_id) REFERENCES players(id)
);

CREATE TABLE favorites (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    user_id      INT NOT NULL,
    type         ENUM('team','player','league') NOT NULL,
    reference_id INT NOT NULL,
    UNIQUE KEY unique_fav (user_id, type, reference_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ─────────────────────────────────────────────
--  LEAGUES
-- ─────────────────────────────────────────────
INSERT INTO leagues (id, name, country, season) VALUES
(1, 'Premier League',          'England',  '2025/26'),
(2, 'La Liga',                 'Spain',    '2025/26'),
(3, 'Bundesliga',              'Germany',  '2025/26'),
(4, 'Serie A',                 'Italy',    '2025/26'),
(5, 'Ligue 1',                 'France',   '2025/26'),
(6, 'UEFA Champions League',   'Europe',   '2025/26');

-- ─────────────────────────────────────────────
--  TEAMS  (8 per domestic league, 8 in UCL)
--  UCL teams reuse domestic team IDs → same league_id trick handled in PHP
-- ─────────────────────────────────────────────

-- Premier League (league_id=1)
INSERT INTO teams (id, league_id, name, short_name, stadium, founded, coach) VALUES
(1,  1, 'Arsenal',           'ARS', 'Emirates Stadium',        1886, 'Mikel Arteta'),
(2,  1, 'Liverpool',         'LIV', 'Anfield',                 1892, 'Arne Slot'),
(3,  1, 'Manchester City',   'MCI', 'Etihad Stadium',          1880, 'Pep Guardiola'),
(4,  1, 'Chelsea',           'CHE', 'Stamford Bridge',         1905, 'Enzo Maresca'),
(5,  1, 'Manchester United', 'MUN', 'Old Trafford',            1878, 'Ruben Amorim'),
(6,  1, 'Tottenham',         'TOT', 'Tottenham Hotspur Stadium',1882, 'Ange Postecoglou'),
(7,  1, 'Newcastle',         'NEW', 'St. James Park',          1892, 'Eddie Howe'),
(8,  1, 'Aston Villa',       'AVL', 'Villa Park',              1874, 'Unai Emery');

-- La Liga (league_id=2)
INSERT INTO teams (id, league_id, name, short_name, stadium, founded, coach) VALUES
(9,  2, 'Real Madrid',    'RMA', 'Santiago Bernabeu',   1902, 'Carlo Ancelotti'),
(10, 2, 'Barcelona',      'BAR', 'Estadi Olimpic',      1899, 'Hansi Flick'),
(11, 2, 'Atletico Madrid','ATM', 'Civitas Metropolitano',1903, 'Diego Simeone'),
(12, 2, 'Sevilla',        'SEV', 'Ramon Sanchez-Pizjuan',1890, 'Quique Sanchez'),
(13, 2, 'Valencia',       'VAL', 'Mestalla',             1919, 'Ruben Baraja'),
(14, 2, 'Villarreal',     'VIL', 'Estadio de la Ceramica',1923,'Marcelino Garcia'),
(15, 2, 'Real Betis',     'BET', 'Benito Villamarin',   1907, 'Manuel Pellegrini'),
(16, 2, 'Athletic Club',  'ATH', 'San Mames',           1898, 'Ernesto Valverde');

-- Bundesliga (league_id=3)
INSERT INTO teams (id, league_id, name, short_name, stadium, founded, coach) VALUES
(17, 3, 'Bayern Munich',       'BAY', 'Allianz Arena',       1900, 'Vincent Kompany'),
(18, 3, 'Borussia Dortmund',   'BVB', 'Signal Iduna Park',   1909, 'Niko Kovac'),
(19, 3, 'RB Leipzig',          'RBL', 'Red Bull Arena',      2009, 'Marco Rose'),
(20, 3, 'Bayer Leverkusen',    'LEV', 'BayArena',            1904, 'Xabi Alonso'),
(21, 3, 'Eintracht Frankfurt', 'SGE', 'Deutsche Bank Park',  1899, 'Dino Toppmoller'),
(22, 3, 'VfB Stuttgart',       'VFB', 'Mercedes-Benz Arena', 1893, 'Sebastian Hoeness'),
(23, 3, 'Wolfsburg',           'WOB', 'Volkswagen Arena',    1945, 'Ralph Hasenhuttl'),
(24, 3, 'Freiburg',            'SCF', 'Europa-Park Stadion', 1904, 'Julian Schuster');

-- Serie A (league_id=4)
INSERT INTO teams (id, league_id, name, short_name, stadium, founded, coach) VALUES
(25, 4, 'Juventus',    'JUV', 'Juventus Stadium',  1897, 'Thiago Motta'),
(26, 4, 'AC Milan',    'MIL', 'San Siro',          1899, 'Paulo Fonseca'),
(27, 4, 'Inter Milan', 'INT', 'San Siro',          1908, 'Simone Inzaghi'),
(28, 4, 'Napoli',      'NAP', 'Diego Armando Maradona',1926,'Antonio Conte'),
(29, 4, 'Roma',        'ROM', 'Stadio Olimpico',   1927, 'Ivan Juric'),
(30, 4, 'Lazio',       'LAZ', 'Stadio Olimpico',   1900, 'Marco Baroni'),
(31, 4, 'Atalanta',    'ATA', 'Gewiss Stadium',    1907, 'Gian Piero Gasperini'),
(32, 4, 'Fiorentina',  'FIO', 'Stadio Artemio Franchi',1926,'Raffaele Palladino');

-- Ligue 1 (league_id=5)
INSERT INTO teams (id, league_id, name, short_name, stadium, founded, coach) VALUES
(33, 5, 'PSG',       'PSG', 'Parc des Princes',     1970, 'Luis Enrique'),
(34, 5, 'Marseille', 'MAR', 'Orange Velodrome',     1899, 'Roberto De Zerbi'),
(35, 5, 'Lyon',      'LYO', 'Groupama Stadium',     1950, 'Pierre Sage'),
(36, 5, 'Monaco',    'MON', 'Stade Louis II',       1924, 'Adi Hutter'),
(37, 5, 'Lille',     'LIL', 'Stade Pierre-Mauroy',  1944, 'Bruno Genesio'),
(38, 5, 'Rennes',    'REN', 'Roazhon Park',         1901, 'Julien Stephan'),
(39, 5, 'Nice',      'NIC', 'Allianz Riviera',      1904, 'Franck Haise'),
(40, 5, 'Lens',      'RCL', 'Stade Bollaert-Delelis',1906,'Will Still');

-- ─────────────────────────────────────────────
--  PLAYERS (5 key players per team)
-- ─────────────────────────────────────────────

-- Arsenal (team_id=1)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(1,  1, 'David Raya',          'GK',  'Spain',   30, 22),
(2,  1, 'Ben White',           'DEF', 'England', 27,  4),
(3,  1, 'Martin Odegaard',     'MID', 'Norway',  27,  8),
(4,  1, 'Bukayo Saka',         'FWD', 'England', 24,  7),
(5,  1, 'Gabriel Martinelli',  'FWD', 'Brazil',  23, 11);

-- Liverpool (team_id=2)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(6,  2, 'Alisson Becker',      'GK',  'Brazil',      33,  1),
(7,  2, 'Virgil van Dijk',     'DEF', 'Netherlands', 33,  4),
(8,  2, 'Mohamed Salah',       'FWD', 'Egypt',       33, 11),
(9,  2, 'Trent Alexander-Arnold','DEF','England',    27, 66),
(10, 2, 'Luis Diaz',           'FWD', 'Colombia',    28,  7);

-- Manchester City (team_id=3)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(11, 3, 'Ederson',             'GK',  'Brazil',  32, 31),
(12, 3, 'Ruben Dias',          'DEF', 'Portugal',28,  3),
(13, 3, 'Kevin De Bruyne',     'MID', 'Belgium', 34, 17),
(14, 3, 'Phil Foden',          'MID', 'England', 25, 47),
(15, 3, 'Erling Haaland',      'FWD', 'Norway',  25,  9);

-- Chelsea (team_id=4)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(16, 4, 'Robert Sanchez',      'GK',  'Spain',     27,  1),
(17, 4, 'Reece James',         'DEF', 'England',   25, 24),
(18, 4, 'Enzo Fernandez',      'MID', 'Argentina', 24,  8),
(19, 4, 'Cole Palmer',         'MID', 'England',   23, 20),
(20, 4, 'Nicolas Jackson',     'FWD', 'Senegal',   24, 15);

-- Manchester United (team_id=5)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(21, 5, 'Andre Onana',         'GK',  'Cameroon',  28, 24),
(22, 5, 'Lisandro Martinez',   'DEF', 'Argentina', 27,  6),
(23, 5, 'Bruno Fernandes',     'MID', 'Portugal',  30,  8),
(24, 5, 'Marcus Rashford',     'FWD', 'England',   28, 10),
(25, 5, 'Rasmus Hojlund',      'FWD', 'Denmark',   22, 11);

-- Tottenham (team_id=6)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(26, 6, 'Guglielmo Vicario',   'GK',  'Italy',       28,  1),
(27, 6, 'Pedro Porro',         'DEF', 'Spain',        26, 23),
(28, 6, 'James Maddison',      'MID', 'England',      28, 10),
(29, 6, 'Son Heung-min',       'FWD', 'South Korea',  32,  7),
(30, 6, 'Dominic Solanke',     'FWD', 'England',      27,  9);

-- Newcastle (team_id=7)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(31, 7, 'Nick Pope',           'GK',  'England',  33, 22),
(32, 7, 'Kieran Trippier',     'DEF', 'England',  34,  2),
(33, 7, 'Bruno Guimaraes',     'MID', 'Brazil',   27, 39),
(34, 7, 'Alexander Isak',      'FWD', 'Sweden',   26, 14),
(35, 7, 'Anthony Gordon',      'FWD', 'England',  24, 10);

-- Aston Villa (team_id=8)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(36, 8, 'Emiliano Martinez',   'GK',  'Argentina', 32,  1),
(37, 8, 'Ezri Konsa',          'DEF', 'England',   27,  4),
(38, 8, 'John McGinn',         'MID', 'Scotland',  30,  7),
(39, 8, 'Ollie Watkins',       'FWD', 'England',   29, 11),
(40, 8, 'Leon Bailey',         'FWD', 'Jamaica',   27, 31);

-- Real Madrid (team_id=9)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(41, 9, 'Thibaut Courtois',    'GK',  'Belgium',  33,  1),
(42, 9, 'Dani Carvajal',       'DEF', 'Spain',    33,  2),
(43, 9, 'Luka Modric',         'MID', 'Croatia',  40, 10),
(44, 9, 'Vinicius Jr',         'FWD', 'Brazil',   25,  7),
(45, 9, 'Jude Bellingham',     'MID', 'England',  22,  5);

-- Barcelona (team_id=10)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(46, 10, 'Inaki Pena',          'GK',  'Spain',   25,  1),
(47, 10, 'Ronald Araujo',       'DEF', 'Uruguay', 26,  4),
(48, 10, 'Pedri',               'MID', 'Spain',   23,  8),
(49, 10, 'Robert Lewandowski',  'FWD', 'Poland',  37,  9),
(50, 10, 'Lamine Yamal',        'FWD', 'Spain',   18, 19);

-- Atletico Madrid (team_id=11)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(51, 11, 'Jan Oblak',           'GK',  'Slovenia', 32, 13),
(52, 11, 'Jose Gimenez',        'DEF', 'Uruguay',  30,  2),
(53, 11, 'Koke',                'MID', 'Spain',    33,  6),
(54, 11, 'Antoine Griezmann',   'FWD', 'France',   34,  8),
(55, 11, 'Julien Alvarez',      'FWD', 'Argentina',25,  19);

-- Sevilla (team_id=12)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(56, 12, 'Orjan Nyland',        'GK',  'Norway',  35,  1),
(57, 12, 'Marcos Acuna',        'DEF', 'Argentina',33, 12),
(58, 12, 'Soumare',             'MID', 'France',  25,  6),
(59, 12, 'Isaac Romero',        'FWD', 'Spain',   23,  9),
(60, 12, 'Dodi Lukebakio',      'FWD', 'Belgium', 27, 11);

-- Valencia (team_id=13)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(61, 13, 'Giorgi Mamardashvili','GK',  'Georgia', 24,  1),
(62, 13, 'Gabriel Paulista',    'DEF', 'Brazil',  34,  5),
(63, 13, 'Pepelu',              'MID', 'Spain',   25, 14),
(64, 13, 'Hugo Duro',           'FWD', 'Spain',   25, 21),
(65, 13, 'Thierry Correia',     'DEF', 'Portugal',25, 22);

-- Villarreal (team_id=14)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(66, 14, 'Filip Jorgensen',     'GK',  'Denmark',  22, 13),
(67, 14, 'Juan Foyth',          'DEF', 'Argentina',27,  2),
(68, 14, 'Dani Parejo',         'MID', 'Spain',    36, 10),
(69, 14, 'Gerard Moreno',       'FWD', 'Spain',    33,  7),
(70, 14, 'Alexander Sorloth',   'FWD', 'Norway',   29,  9);

-- Real Betis (team_id=15)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(71, 15, 'Rui Silva',           'GK',  'Portugal',  31, 25),
(72, 15, 'Aitor Ruibal',        'DEF', 'Spain',     26, 16),
(73, 15, 'Giovani Lo Celso',    'MID', 'Argentina', 29, 22),
(74, 15, 'Antony',              'FWD', 'Brazil',    25, 11),
(75, 15, 'Chimy Avila',         'FWD', 'Argentina', 31,  9);

-- Athletic Club (team_id=16)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(76, 16, 'Unai Simon',          'GK',  'Spain', 27,  1),
(77, 16, 'Dani Vivian',         'DEF', 'Spain', 25,  3),
(78, 16, 'Oihan Sancet',        'MID', 'Spain', 24, 10),
(79, 16, 'Gorka Guruzeta',      'FWD', 'Spain', 27,  9),
(80, 16, 'Nico Williams',       'FWD', 'Spain', 22, 11);

-- Bayern Munich (team_id=17)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(81, 17, 'Manuel Neuer',        'GK',  'Germany',     39,  1),
(82, 17, 'Min-jae Kim',         'DEF', 'South Korea', 28,  3),
(83, 17, 'Joshua Kimmich',      'MID', 'Germany',     30,  6),
(84, 17, 'Harry Kane',          'FWD', 'England',     32,  9),
(85, 17, 'Leroy Sane',          'FWD', 'Germany',     29, 10);

-- Borussia Dortmund (team_id=18)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(86, 18, 'Gregor Kobel',        'GK',  'Switzerland', 27,  1),
(87, 18, 'Niklas Sule',         'DEF', 'Germany',     30, 25),
(88, 18, 'Julian Brandt',       'MID', 'Germany',     29, 19),
(89, 18, 'Serhou Guirassy',     'FWD', 'Guinea',      29,  9),
(90, 18, 'Karim Adeyemi',       'FWD', 'Germany',     23, 27);

-- RB Leipzig (team_id=19)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(91, 19, 'Peter Gulacsi',       'GK',  'Hungary',     35,  1),
(92, 19, 'Willi Orban',         'DEF', 'Hungary',     32,  4),
(93, 19, 'Xavi Simons',         'MID', 'Netherlands', 22, 20),
(94, 19, 'Lois Openda',         'FWD', 'Belgium',     25, 11),
(95, 19, 'Benjamin Sesko',      'FWD', 'Slovenia',    22, 30);

-- Bayer Leverkusen (team_id=20)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(96,  20, 'Lukas Hradecky',     'GK',  'Finland',     36,  1),
(97,  20, 'Piero Hincapie',     'DEF', 'Ecuador',     23,  3),
(98,  20, 'Granit Xhaka',       'MID', 'Switzerland', 33,  10),
(99,  20, 'Florian Wirtz',      'MID', 'Germany',     22, 17),
(100, 20, 'Victor Boniface',    'FWD', 'Nigeria',     24,  9);

-- Eintracht Frankfurt (team_id=21)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(101, 21, 'Kevin Trapp',        'GK',  'Germany', 35,  1),
(102, 21, 'Tuta',               'DEF', 'Brazil',  26,  3),
(103, 21, 'Mario Gotze',        'MID', 'Germany', 33, 10),
(104, 21, 'Omar Marmoush',      'FWD', 'Egypt',   26, 11),
(105, 21, 'Hugo Ekitike',       'FWD', 'France',  23,  9);

-- VfB Stuttgart (team_id=22)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(106, 22, 'Alexander Nubel',    'GK',  'Germany', 29,  1),
(107, 22, 'Hiroki Ito',         'DEF', 'Japan',   26,  3),
(108, 22, 'Chris Fuhrich',      'MID', 'Germany', 26, 18),
(109, 22, 'Deniz Undav',        'FWD', 'Germany', 29,  9),
(110, 22, 'Nick Woltemade',     'FWD', 'Germany', 23, 27);

-- Wolfsburg (team_id=23)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(111, 23, 'Kamil Grabara',      'GK',  'Poland',  26,  1),
(112, 23, 'Sebastiaan Bornauw', 'DEF', 'Belgium', 26,  5),
(113, 23, 'Maximilian Arnold',  'MID', 'Germany', 31, 27),
(114, 23, 'Jonas Wind',         'FWD', 'Denmark', 26,  9),
(115, 23, 'Mohamed Amoura',     'FWD', 'Algeria', 24, 11);

-- Freiburg (team_id=24)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(116, 24, 'Noah Atubolu',       'GK',  'Germany', 22,  1),
(117, 24, 'Philipp Lienhart',   'DEF', 'Austria', 29,  5),
(118, 24, 'Christian Gunter',   'DEF', 'Germany', 32, 20),
(119, 24, 'Ritsu Doan',         'MID', 'Japan',   27, 21),
(120, 24, 'Lucas Holer',        'FWD', 'Austria', 30, 11);

-- Juventus (team_id=25)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(121, 25, 'Michele Di Gregorio', 'GK', 'Italy',   27,  1),
(122, 25, 'Gleison Bremer',     'DEF', 'Brazil',  28,  3),
(123, 25, 'Manuel Locatelli',   'MID', 'Italy',   27,  5),
(124, 25, 'Dusan Vlahovic',     'FWD', 'Serbia',  25,  9),
(125, 25, 'Kenan Yildiz',       'FWD', 'Turkey',  20, 10);

-- AC Milan (team_id=26)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(126, 26, 'Mike Maignan',       'GK',  'France',      29, 16),
(127, 26, 'Theo Hernandez',     'DEF', 'France',      27, 19),
(128, 26, 'Tijjani Reijnders',  'MID', 'Netherlands', 26, 14),
(129, 26, 'Rafael Leao',        'FWD', 'Portugal',    26, 10),
(130, 26, 'Alvaro Morata',      'FWD', 'Spain',       33,  7);

-- Inter Milan (team_id=27)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(131, 27, 'Yann Sommer',        'GK',  'Switzerland', 36,  1),
(132, 27, 'Alessandro Bastoni', 'DEF', 'Italy',       26, 95),
(133, 27, 'Nicolo Barella',     'MID', 'Italy',       28, 23),
(134, 27, 'Lautaro Martinez',   'FWD', 'Argentina',   27, 10),
(135, 27, 'Marcus Thuram',      'FWD', 'France',      28,  9);

-- Napoli (team_id=28)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(136, 28, 'Alex Meret',         'GK',  'Italy',   28,  1),
(137, 28, 'Amir Rrahmani',      'DEF', 'Kosovo',  31, 13),
(138, 28, 'Stanislav Lobotka',  'MID', 'Slovakia',30, 68),
(139, 28, 'Victor Osimhen',     'FWD', 'Nigeria', 26,  9),
(140, 28, 'Khvicha Kvaratskhelia','FWD','Georgia', 24, 77);

-- Roma (team_id=29)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(141, 29, 'Mile Svilar',        'GK',  'Belgium',   25, 99),
(142, 29, 'Gianluca Mancini',   'DEF', 'Italy',     29, 23),
(143, 29, 'Lorenzo Pellegrini', 'MID', 'Italy',     29,  7),
(144, 29, 'Romelu Lukaku',      'FWD', 'Belgium',   32, 90),
(145, 29, 'Paulo Dybala',       'FWD', 'Argentina', 32, 21);

-- Lazio (team_id=30)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(146, 30, 'Ivan Provedel',      'GK',  'Italy',    31, 94),
(147, 30, 'Mario Gila',         'DEF', 'Spain',    24,  5),
(148, 30, 'Matteo Guendouzi',   'MID', 'France',   26, 29),
(149, 30, 'Mattia Zaccagni',    'FWD', 'Italy',    29, 20),
(150, 30, 'Valentin Castellanos','FWD','Argentina', 26,  9);

-- Atalanta (team_id=31)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(151, 31, 'Marco Carnesecchi',  'GK',  'Italy',   25, 29),
(152, 31, 'Rafael Toloi',       'DEF', 'Brazil',  35,  2),
(153, 31, 'Teun Koopmeiners',   'MID', 'Netherlands',27, 7),
(154, 31, 'Gianluca Scamacca',  'FWD', 'Italy',   26, 90),
(155, 31, 'Ademola Lookman',    'FWD', 'Nigeria', 27, 11);

-- Fiorentina (team_id=32)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(156, 32, 'David De Gea',       'GK',  'Spain',      34,  1),
(157, 32, 'Lucas Martinez Quarta','DEF','Argentina',  28, 28),
(158, 32, 'Rolando Mandragora', 'MID', 'Italy',       28, 38),
(159, 32, 'Moise Kean',         'FWD', 'Italy',       25,  9),
(160, 32, 'Albert Gudmundsson', 'FWD', 'Iceland',     27, 11);

-- PSG (team_id=33)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(161, 33, 'Gianluigi Donnarumma','GK', 'Italy',   26, 99),
(162, 33, 'Marquinhos',          'DEF','Brazil',   31,  5),
(163, 33, 'Vitinha',             'MID','Portugal', 25, 17),
(164, 33, 'Ousmane Dembele',     'FWD','France',   28, 10),
(165, 33, 'Bradley Barcola',     'FWD','France',   23, 29);

-- Marseille (team_id=34)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(166, 34, 'Pau Lopez',           'GK', 'Spain',     30, 16),
(167, 34, 'Leonardo Balerdi',    'DEF','Argentina', 26,  5),
(168, 34, 'Valentin Rongier',    'MID','France',    30, 21),
(169, 34, 'Mason Greenwood',     'FWD','England',   24, 10),
(170, 34, 'Luis Henrique',       'FWD','Brazil',    23, 11);

-- Lyon (team_id=35)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(171, 35, 'Lucas Perri',         'GK', 'Brazil',    26,  1),
(172, 35, 'Nicolas Tagliafico',  'DEF','Argentina', 33,  3),
(173, 35, 'Corentin Tolisso',    'MID','France',    31,  8),
(174, 35, 'Alexandre Lacazette', 'FWD','France',    34, 10),
(175, 35, 'Ernest Nuamah',       'FWD','Ghana',     22, 18);

-- Monaco (team_id=36)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(176, 36, 'Radoslaw Majecki',    'GK', 'Poland',  25, 16),
(177, 36, 'Axel Disasi',         'DEF','France',  27,  3),
(178, 36, 'Youssouf Fofana',     'MID','France',  26, 19),
(179, 36, 'Wissam Ben Yedder',   'FWD','France',  35, 10),
(180, 36, 'Folarin Balogun',     'FWD','USA',      23,  9);

-- Lille (team_id=37)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(181, 37, 'Lucas Chevalier',     'GK', 'France',   23, 30),
(182, 37, 'Alexsandro',          'DEF','Brazil',   25,  4),
(183, 37, 'Benjamin Andre',      'MID','France',   35, 62),
(184, 37, 'Jonathan David',      'FWD','Canada',   25,  9),
(185, 37, 'Edon Zhegrova',       'FWD','Kosovo',   26, 10);

-- Rennes (team_id=38)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(186, 38, 'Steve Mandanda',      'GK', 'France',  40, 16),
(187, 38, 'Adrien Truffert',     'DEF','France',  23, 27),
(188, 38, 'Benjamin Bourigeaud', 'MID','France',  31,  8),
(189, 38, 'Martin Terrier',      'FWD','France',  28,  7),
(190, 38, 'Desire Doue',         'MID','France',  20, 17);

-- Nice (team_id=39)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(191, 39, 'Marcin Bulka',        'GK', 'Poland',  25, 16),
(192, 39, 'Jean-Clair Todibo',   'DEF','France',  25,  5),
(193, 39, 'Hicham Boudaoui',     'MID','Algeria', 24, 44),
(194, 39, 'Terem Moffi',         'FWD','Nigeria', 26,  9),
(195, 39, 'Evann Guessand',      'FWD','France',  23, 11);

-- Lens (team_id=40)
INSERT INTO players (id, team_id, name, position, nationality, age, number) VALUES
(196, 40, 'Brice Samba',         'GK', 'Congo',  31, 16),
(197, 40, 'Kevin Danso',         'DEF','Austria', 26, 26),
(198, 40, 'Salis Abdul Samed',   'MID','Ghana',   24,  4),
(199, 40, 'Elye Wahi',           'FWD','France',  22,  9),
(200, 40, 'Przemyslaw Frankowski','FWD','Poland',  29, 11);

-- ─────────────────────────────────────────────
--  MATCHES
--  Status: finished = has scores, scheduled = upcoming, live = in progress
--  All dates relative to 2026-03-10 (today)
-- ─────────────────────────────────────────────

-- === PREMIER LEAGUE ===
INSERT INTO matches (id, league_id, home_team_id, away_team_id, home_score, away_score, status, match_date, matchday, venue) VALUES
(1,  1, 1, 2,  2, 1, 'finished',  '2026-03-07 20:00:00', 28, 'Emirates Stadium'),
(2,  1, 3, 4,  3, 1, 'finished',  '2026-03-07 17:30:00', 28, 'Etihad Stadium'),
(3,  1, 6, 5,  1, 1, 'finished',  '2026-03-04 20:00:00', 27, 'Tottenham Hotspur Stadium'),
(4,  1, 7, 8,  2, 0, 'finished',  '2026-03-04 17:30:00', 27, 'St. James Park'),
(5,  1, 1, 3,  NULL, NULL, 'scheduled', '2026-03-10 17:30:00', 29, 'Emirates Stadium'),
(6,  1, 2, 4,  NULL, NULL, 'scheduled', '2026-03-10 20:00:00', 29, 'Anfield'),
(7,  1, 1, 6,  NULL, NULL, 'scheduled', '2026-03-14 17:30:00', 30, 'Emirates Stadium'),
(8,  1, 3, 7,  NULL, NULL, 'scheduled', '2026-03-21 20:00:00', 31, 'Etihad Stadium');

-- === LA LIGA ===
INSERT INTO matches (id, league_id, home_team_id, away_team_id, home_score, away_score, status, match_date, matchday, venue) VALUES
(9,  2, 9,  10, 2, 0, 'finished',  '2026-03-08 21:00:00', 27, 'Santiago Bernabeu'),
(10, 2, 11, 12, 1, 0, 'finished',  '2026-03-08 18:30:00', 27, 'Civitas Metropolitano'),
(11, 2, 16, 14, 2, 1, 'finished',  '2026-03-04 20:00:00', 26, 'San Mames'),
(12, 2, 10, 11, NULL, NULL, 'scheduled', '2026-03-10 21:00:00', 28, 'Estadi Olimpic'),
(13, 2, 9,  16, NULL, NULL, 'scheduled', '2026-03-10 18:30:00', 28, 'Santiago Bernabeu'),
(14, 2, 9,  11, NULL, NULL, 'scheduled', '2026-03-14 21:00:00', 29, 'Santiago Bernabeu');

-- === BUNDESLIGA ===
INSERT INTO matches (id, league_id, home_team_id, away_team_id, home_score, away_score, status, match_date, matchday, venue) VALUES
(15, 3, 17, 18, 4, 2, 'finished',  '2026-03-08 18:30:00', 25, 'Allianz Arena'),
(16, 3, 20, 19, 2, 1, 'finished',  '2026-03-08 15:30:00', 25, 'BayArena'),
(17, 3, 22, 21, 1, 1, 'finished',  '2026-03-04 20:30:00', 24, 'Mercedes-Benz Arena'),
(18, 3, 17, 20, NULL, NULL, 'scheduled', '2026-03-10 18:30:00', 26, 'Allianz Arena'),
(19, 3, 18, 19, NULL, NULL, 'scheduled', '2026-03-10 15:30:00', 26, 'Signal Iduna Park'),
(20, 3, 20, 18, NULL, NULL, 'scheduled', '2026-03-14 20:30:00', 27, 'BayArena');

-- === SERIE A ===
INSERT INTO matches (id, league_id, home_team_id, away_team_id, home_score, away_score, status, match_date, matchday, venue) VALUES
(21, 4, 27, 25, 1, 0, 'finished',  '2026-03-08 20:45:00', 26, 'San Siro'),
(22, 4, 28, 26, 2, 2, 'finished',  '2026-03-08 18:00:00', 26, 'Diego Armando Maradona'),
(23, 4, 29, 31, 0, 2, 'finished',  '2026-03-04 20:45:00', 25, 'Stadio Olimpico'),
(24, 4, 27, 28, NULL, NULL, 'scheduled', '2026-03-10 20:45:00', 27, 'San Siro'),
(25, 4, 25, 26, NULL, NULL, 'scheduled', '2026-03-10 18:00:00', 27, 'Juventus Stadium'),
(26, 4, 31, 27, NULL, NULL, 'scheduled', '2026-03-14 20:45:00', 28, 'Gewiss Stadium');

-- === LIGUE 1 ===
INSERT INTO matches (id, league_id, home_team_id, away_team_id, home_score, away_score, status, match_date, matchday, venue) VALUES
(27, 5, 33, 34, 3, 0, 'finished',  '2026-03-08 21:00:00', 26, 'Parc des Princes'),
(28, 5, 36, 35, 1, 1, 'finished',  '2026-03-08 19:00:00', 26, 'Stade Louis II'),
(29, 5, 37, 38, 2, 0, 'finished',  '2026-03-04 21:00:00', 25, 'Stade Pierre-Mauroy'),
(30, 5, 33, 36, NULL, NULL, 'scheduled', '2026-03-10 21:00:00', 27, 'Parc des Princes'),
(31, 5, 34, 35, NULL, NULL, 'scheduled', '2026-03-10 19:00:00', 27, 'Orange Velodrome'),
(32, 5, 36, 33, NULL, NULL, 'scheduled', '2026-03-14 21:00:00', 28, 'Stade Louis II');

-- === UEFA CHAMPIONS LEAGUE (QF) ===
INSERT INTO matches (id, league_id, home_team_id, away_team_id, home_score, away_score, status, match_date, matchday, venue) VALUES
(33, 6, 9,  3,  1, 1, 'finished',  '2026-03-05 21:00:00', NULL, 'Santiago Bernabeu'),
(34, 6, 17, 1,  2, 0, 'finished',  '2026-03-05 21:00:00', NULL, 'Allianz Arena'),
(35, 6, 27, 10, 1, 0, 'finished',  '2026-03-05 21:00:00', NULL, 'San Siro'),
(36, 6, 18, 33, 0, 2, 'finished',  '2026-03-05 21:00:00', NULL, 'Signal Iduna Park'),
(37, 6, 3,  9,  NULL, NULL, 'scheduled', '2026-03-12 21:00:00', NULL, 'Etihad Stadium'),
(38, 6, 1,  17, NULL, NULL, 'scheduled', '2026-03-12 21:00:00', NULL, 'Emirates Stadium'),
(39, 6, 10, 27, NULL, NULL, 'scheduled', '2026-03-12 21:00:00', NULL, 'Estadi Olimpic'),
(40, 6, 33, 18, NULL, NULL, 'scheduled', '2026-03-12 21:00:00', NULL, 'Parc des Princes');

-- ─────────────────────────────────────────────
--  MATCH EVENTS (goals, cards for finished matches)
-- ─────────────────────────────────────────────

-- Match 1: Arsenal 2-1 Liverpool
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(1, 1, 4,  'goal',        35),   -- Saka
(1, 2, 8,  'goal',        58),   -- Salah
(1, 2, 7,  'yellow_card', 63),   -- Van Dijk
(1, 1, 5,  'goal',        78);   -- Martinelli

-- Match 2: Man City 3-1 Chelsea
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(2, 3, 15, 'goal',        12),   -- Haaland
(2, 3, 15, 'goal',        45),   -- Haaland
(2, 4, 19, 'goal',        67),   -- Palmer
(2, 3, 14, 'goal',        82);   -- Foden

-- Match 3: Tottenham 1-1 Man United
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(3, 6, 29, 'goal',        23),   -- Son
(3, 5, 23, 'yellow_card', 44),   -- B. Fernandes
(3, 5, 24, 'goal',        71);   -- Rashford

-- Match 4: Newcastle 2-0 Aston Villa
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(4, 7, 34, 'goal',        14),   -- Isak
(4, 8, 38, 'yellow_card', 52),   -- McGinn
(4, 7, 35, 'goal',        88);   -- Gordon

-- Match 9: Real Madrid 2-0 Barcelona (El Clasico)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(9, 9, 44, 'goal',        34),   -- Vinicius Jr
(9, 10,47, 'yellow_card', 58),   -- Araujo
(9, 9, 45, 'goal',        72);   -- Bellingham

-- Match 10: Atletico 1-0 Sevilla
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(10, 11, 54, 'goal',        55);  -- Griezmann

-- Match 11: Athletic 2-1 Villarreal
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(11, 16, 79, 'goal',        28),   -- Guruzeta
(11, 14, 69, 'goal',        51),   -- Moreno
(11, 16, 78, 'goal',        85);   -- Sancet

-- Match 15: Bayern 4-2 Dortmund (Der Klassiker)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(15, 17, 84, 'goal',         7),   -- Kane
(15, 18, 89, 'goal',        33),   -- Guirassy
(15, 17, 84, 'goal',        45),   -- Kane
(15, 18, 90, 'goal',        56),   -- Adeyemi
(15, 18, 87, 'yellow_card', 61),   -- Sule
(15, 17, 85, 'goal',        71),   -- Sane
(15, 17, 83, 'goal',        89);   -- Kimmich

-- Match 16: Leverkusen 2-1 Leipzig
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(16, 20, 100, 'goal',        23),  -- Boniface
(16, 19, 94,  'goal',        54),  -- Openda
(16, 20, 99,  'goal',        79);  -- Wirtz

-- Match 17: Stuttgart 1-1 Frankfurt
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(17, 22, 109, 'goal',        38),  -- Undav
(17, 21, 104, 'goal',        66);  -- Marmoush

-- Match 21: Inter 1-0 Juventus
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(21, 27, 134, 'goal',        41),  -- Lautaro
(21, 25, 123, 'yellow_card', 72);  -- Locatelli

-- Match 22: Napoli 2-2 AC Milan
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(22, 28, 139, 'goal',        15),  -- Osimhen
(22, 26, 129, 'goal',        37),  -- Leao
(22, 28, 140, 'goal',        61),  -- Kvaratskhelia
(22, 26, 130, 'goal',        83);  -- Morata

-- Match 23: Roma 0-2 Atalanta
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(23, 31, 155, 'goal',        29),  -- Lookman
(23, 29, 143, 'yellow_card', 44),  -- Pellegrini
(23, 31, 154, 'goal',        77);  -- Scamacca

-- Match 27: PSG 3-0 Marseille (Le Classique)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(27, 33, 164, 'goal',        18),  -- Dembele
(27, 33, 165, 'goal',        44),  -- Barcola
(27, 34, 167, 'yellow_card', 65),  -- Balerdi
(27, 33, 164, 'goal',        74);  -- Dembele

-- Match 28: Monaco 1-1 Lyon
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(28, 36, 179, 'goal',        31),  -- Ben Yedder
(28, 35, 174, 'goal',        69);  -- Lacazette

-- Match 29: Lille 2-0 Rennes
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(29, 37, 184, 'goal',        22),  -- J. David
(29, 37, 184, 'goal',        58);  -- J. David

-- Match 33: Real Madrid 1-1 Man City (UCL QF 1st Leg)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(33, 9,  44, 'goal',        45),   -- Vinicius Jr
(33, 3,  15, 'goal',        77);   -- Haaland

-- Match 34: Bayern 2-0 Arsenal (UCL QF 1st Leg)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(34, 17, 84, 'goal',        11),   -- Kane
(34, 1,   4, 'yellow_card', 38),   -- Saka
(34, 17, 84, 'goal',        67);   -- Kane

-- Match 35: Inter 1-0 Barcelona (UCL QF 1st Leg)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(35, 27, 135, 'goal',        52);  -- Thuram

-- Match 36: Dortmund 0-2 PSG (UCL QF 1st Leg)
INSERT INTO match_events (match_id, team_id, player_id, event_type, minute) VALUES
(36, 33, 164, 'goal',        38),  -- Dembele
(36, 33, 165, 'goal',        82);  -- Barcola

-- ─────────────────────────────────────────────
--  MATCH STATS (home team first, away team second)
-- ─────────────────────────────────────────────

INSERT INTO match_stats (match_id, team_id, possession, shots, shots_on_target, corners, fouls, yellow_cards, red_cards) VALUES
-- Match 1: Arsenal 2-1 Liverpool
(1, 1, 54, 14,  6, 6, 9, 1, 0),
(1, 2, 46, 11,  5, 4, 11, 2, 0),
-- Match 2: Man City 3-1 Chelsea
(2, 3, 62, 18,  9, 8, 7, 0, 0),
(2, 4, 38,  9,  3, 3, 13, 1, 0),
-- Match 3: Tottenham 1-1 Man United
(3, 6, 48, 12,  4, 5, 12, 1, 0),
(3, 5, 52, 13,  5, 4, 10, 2, 0),
-- Match 4: Newcastle 2-0 Aston Villa
(4, 7, 50, 13,  5, 7, 8, 0, 0),
(4, 8, 50, 10,  2, 3, 9, 1, 0),
-- Match 9: Real Madrid 2-0 Barcelona
(9,  9,  55, 16,  7, 7, 10, 1, 0),
(9,  10, 45, 12,  4, 4, 12, 2, 0),
-- Match 10: Atletico 1-0 Sevilla
(10, 11, 43,  9,  3, 4, 14, 1, 0),
(10, 12, 57, 14,  4, 6,  9, 0, 0),
-- Match 11: Athletic 2-1 Villarreal
(11, 16, 46, 11,  5, 5, 11, 0, 0),
(11, 14, 54, 13,  4, 4, 10, 1, 0),
-- Match 15: Bayern 4-2 Dortmund
(15, 17, 58, 20,  9, 9, 8, 0, 0),
(15, 18, 42, 15,  6, 5, 12, 2, 0),
-- Match 16: Leverkusen 2-1 Leipzig
(16, 20, 51, 14,  6, 6, 9, 1, 0),
(16, 19, 49, 12,  4, 4, 11, 0, 0),
-- Match 17: Stuttgart 1-1 Frankfurt
(17, 22, 47, 10,  4, 5, 12, 0, 0),
(17, 21, 53, 12,  5, 4,  9, 1, 0),
-- Match 21: Inter 1-0 Juventus
(21, 27, 52, 13,  5, 6, 10, 1, 0),
(21, 25, 48, 11,  3, 4, 11, 2, 0),
-- Match 22: Napoli 2-2 AC Milan
(22, 28, 45, 13,  6, 5, 13, 0, 0),
(22, 26, 55, 15,  7, 7,  9, 1, 0),
-- Match 23: Roma 0-2 Atalanta
(23, 29, 46, 10,  2, 4, 13, 1, 0),
(23, 31, 54, 14,  6, 6,  9, 0, 0),
-- Match 27: PSG 3-0 Marseille
(27, 33, 63, 19,  9, 8, 7, 0, 0),
(27, 34, 37,  7,  2, 3, 14, 2, 0),
-- Match 28: Monaco 1-1 Lyon
(28, 36, 49, 11,  4, 5, 10, 0, 0),
(28, 35, 51, 12,  5, 6,  9, 1, 0),
-- Match 29: Lille 2-0 Rennes
(29, 37, 53, 13,  6, 6, 11, 0, 0),
(29, 38, 47,  8,  2, 3, 12, 1, 0),
-- Match 33: Real Madrid 1-1 Man City (UCL)
(33, 9,  50, 14,  5, 6, 10, 1, 0),
(33, 3,  50, 14,  6, 5, 10, 0, 0),
-- Match 34: Bayern 2-0 Arsenal (UCL)
(34, 17, 54, 16,  7, 7, 9, 0, 0),
(34, 1,  46, 12,  3, 4, 11, 2, 0),
-- Match 35: Inter 1-0 Barcelona (UCL)
(35, 27, 47, 11,  4, 5, 13, 1, 0),
(35, 10, 53, 16,  5, 8,  9, 0, 0),
-- Match 36: Dortmund 0-2 PSG (UCL)
(36, 18, 44,  9,  2, 3, 12, 1, 0),
(36, 33, 56, 14,  6, 7,  8, 0, 0);

-- ─────────────────────────────────────────────
--  MATCH LINEUPS (for key matches)
-- ─────────────────────────────────────────────

-- Match 1: Arsenal (1) vs Liverpool (2) - starting XI (5 per team for demo)
INSERT INTO match_lineups (match_id, team_id, player_id, is_starting) VALUES
(1, 1, 1, 1),(1, 1, 2, 1),(1, 1, 3, 1),(1, 1, 4, 1),(1, 1, 5, 1),
(1, 2, 6, 1),(1, 2, 7, 1),(1, 2, 8, 1),(1, 2, 9, 1),(1, 2, 10, 1);

-- Match 9: Real Madrid vs Barcelona (El Clasico)
INSERT INTO match_lineups (match_id, team_id, player_id, is_starting) VALUES
(9, 9,  41, 1),(9, 9,  42, 1),(9, 9,  43, 1),(9, 9,  44, 1),(9, 9,  45, 1),
(9, 10, 46, 1),(9, 10, 47, 1),(9, 10, 48, 1),(9, 10, 49, 1),(9, 10, 50, 1);

-- Match 15: Bayern vs Dortmund (Der Klassiker)
INSERT INTO match_lineups (match_id, team_id, player_id, is_starting) VALUES
(15, 17, 81, 1),(15, 17, 82, 1),(15, 17, 83, 1),(15, 17, 84, 1),(15, 17, 85, 1),
(15, 18, 86, 1),(15, 18, 87, 1),(15, 18, 88, 1),(15, 18, 89, 1),(15, 18, 90, 1);

-- Match 27: PSG vs Marseille (Le Classique)
INSERT INTO match_lineups (match_id, team_id, player_id, is_starting) VALUES
(27, 33, 161, 1),(27, 33, 162, 1),(27, 33, 163, 1),(27, 33, 164, 1),(27, 33, 165, 1),
(27, 34, 166, 1),(27, 34, 167, 1),(27, 34, 168, 1),(27, 34, 169, 1),(27, 34, 170, 1);

-- Match 33: Real Madrid vs Man City (UCL)
INSERT INTO match_lineups (match_id, team_id, player_id, is_starting) VALUES
(33, 9,  41, 1),(33, 9,  42, 1),(33, 9,  43, 1),(33, 9,  44, 1),(33, 9,  45, 1),
(33, 3,  11, 1),(33, 3,  12, 1),(33, 3,  13, 1),(33, 3,  14, 1),(33, 3,  15, 1);

-- ─────────────────────────────────────────────
--  STANDINGS
-- ─────────────────────────────────────────────

-- Premier League
INSERT INTO standings (league_id, team_id, played, won, drawn, lost, goals_for, goals_against, points) VALUES
(1, 1,  28, 19, 4, 5,  63, 28, 61),  -- Arsenal
(1, 2,  28, 18, 4, 6,  67, 35, 58),  -- Liverpool
(1, 3,  28, 17, 3, 8,  59, 38, 54),  -- Man City
(1, 4,  28, 15, 5, 8,  52, 41, 50),  -- Chelsea
(1, 6,  28, 14, 5, 9,  50, 43, 47),  -- Tottenham
(1, 8,  28, 13, 5, 10, 48, 44, 44),  -- Aston Villa
(1, 7,  28, 12, 6, 10, 45, 42, 42),  -- Newcastle
(1, 5,  28, 11, 4, 13, 42, 55, 37);  -- Man United

-- La Liga
INSERT INTO standings (league_id, team_id, played, won, drawn, lost, goals_for, goals_against, points) VALUES
(2, 9,  27, 20, 4, 3,  65, 22, 64),  -- Real Madrid
(2, 10, 27, 17, 5, 5,  60, 32, 56),  -- Barcelona
(2, 11, 27, 16, 5, 6,  50, 30, 53),  -- Atletico
(2, 16, 27, 14, 6, 7,  48, 35, 48),  -- Athletic
(2, 14, 27, 13, 5, 9,  44, 40, 44),  -- Villarreal
(2, 15, 27, 12, 5, 10, 40, 42, 41),  -- Real Betis
(2, 13, 27, 11, 5, 11, 38, 44, 38),  -- Valencia
(2, 12, 27,  9, 6, 12, 35, 48, 33);  -- Sevilla

-- Bundesliga
INSERT INTO standings (league_id, team_id, played, won, drawn, lost, goals_for, goals_against, points) VALUES
(3, 20, 25, 19, 4, 2,  62, 22, 61),  -- Leverkusen
(3, 17, 25, 17, 4, 4,  66, 30, 55),  -- Bayern
(3, 19, 25, 14, 4, 7,  50, 35, 46),  -- Leipzig
(3, 21, 25, 13, 4, 8,  45, 38, 43),  -- Frankfurt
(3, 22, 25, 12, 5, 8,  43, 39, 41),  -- Stuttgart
(3, 18, 25, 11, 4, 10, 48, 50, 37),  -- Dortmund
(3, 24, 25,  9, 5, 11, 33, 38, 32),  -- Freiburg
(3, 23, 25,  8, 4, 13, 31, 50, 28);  -- Wolfsburg

-- Serie A
INSERT INTO standings (league_id, team_id, played, won, drawn, lost, goals_for, goals_against, points) VALUES
(4, 27, 26, 19, 4, 3,  62, 25, 61),  -- Inter
(4, 28, 26, 18, 3, 5,  60, 28, 57),  -- Napoli
(4, 31, 26, 16, 5, 5,  58, 30, 53),  -- Atalanta
(4, 25, 26, 14, 5, 7,  48, 35, 47),  -- Juventus
(4, 26, 26, 13, 5, 8,  45, 38, 44),  -- AC Milan
(4, 32, 26, 12, 5, 9,  40, 38, 41),  -- Fiorentina
(4, 29, 26, 10, 5, 11, 38, 45, 35),  -- Roma
(4, 30, 26, 10, 3, 13, 36, 48, 33);  -- Lazio

-- Ligue 1
INSERT INTO standings (league_id, team_id, played, won, drawn, lost, goals_for, goals_against, points) VALUES
(5, 33, 26, 21, 3, 2,  72, 22, 66),  -- PSG
(5, 36, 26, 15, 5, 6,  50, 35, 50),  -- Monaco
(5, 37, 26, 14, 5, 7,  44, 30, 47),  -- Lille
(5, 34, 26, 14, 3, 9,  45, 35, 45),  -- Marseille
(5, 35, 26, 12, 5, 9,  40, 38, 41),  -- Lyon
(5, 38, 26, 11, 5, 10, 38, 40, 38),  -- Rennes
(5, 39, 26, 10, 6, 10, 35, 38, 36),  -- Nice
(5, 40, 26,  9, 5, 12, 32, 44, 32);  -- Lens

-- UCL (simplified league phase standings)
INSERT INTO standings (league_id, team_id, played, won, drawn, lost, goals_for, goals_against, points) VALUES
(6, 9,  6, 5, 1, 0, 15,  4, 16),  -- Real Madrid
(6, 3,  6, 4, 1, 1, 13,  7, 13),  -- Man City
(6, 17, 6, 4, 0, 2, 12,  8, 12),  -- Bayern
(6, 1,  6, 3, 2, 1, 10,  7, 11),  -- Arsenal
(6, 10, 6, 3, 1, 2,  9,  8, 10),  -- Barcelona
(6, 27, 6, 2, 2, 2,  8,  9,  8),  -- Inter
(6, 18, 6, 1, 1, 4,  6, 14,  4),  -- Dortmund
(6, 33, 6, 0, 2, 4,  5, 16,  2);  -- PSG

-- ─────────────────────────────────────────────
--  PLAYER STATS (season 2025/26)
-- ─────────────────────────────────────────────

INSERT INTO player_stats (player_id, season, matches_played, goals, assists, yellow_cards, red_cards, minutes_played) VALUES
-- Arsenal
(1,  '2025/26', 26, 0, 0, 1, 0, 2340),
(2,  '2025/26', 24, 2, 3, 2, 0, 2160),
(3,  '2025/26', 25, 8, 11, 3, 0, 2250),
(4,  '2025/26', 26, 14, 9, 2, 0, 2340),
(5,  '2025/26', 24, 11, 7, 1, 0, 2160),
-- Liverpool
(6,  '2025/26', 27, 0, 0, 0, 0, 2430),
(7,  '2025/26', 25, 3, 1, 3, 0, 2250),
(8,  '2025/26', 27, 18, 8, 1, 0, 2430),
(9,  '2025/26', 26, 4, 12, 2, 0, 2340),
(10, '2025/26', 24, 9, 5, 1, 0, 2160),
-- Man City
(11, '2025/26', 26, 0, 0, 0, 0, 2340),
(12, '2025/26', 25, 1, 2, 1, 0, 2250),
(13, '2025/26', 22, 5, 14, 2, 0, 1980),
(14, '2025/26', 27, 10, 8, 1, 0, 2430),
(15, '2025/26', 26, 22, 6, 0, 0, 2340),
-- Chelsea
(16, '2025/26', 25, 0, 0, 1, 0, 2250),
(17, '2025/26', 20, 2, 5, 2, 0, 1800),
(18, '2025/26', 26, 4, 9, 3, 0, 2340),
(19, '2025/26', 27, 16, 11, 1, 0, 2430),
(20, '2025/26', 24, 11, 4, 0, 0, 2160),
-- Man United
(21, '2025/26', 26, 0, 0, 2, 0, 2340),
(22, '2025/26', 22, 1, 0, 4, 0, 1980),
(23, '2025/26', 26, 7, 10, 5, 0, 2340),
(24, '2025/26', 25, 9, 4, 1, 0, 2250),
(25, '2025/26', 23, 8, 3, 0, 0, 2070),
-- Real Madrid
(41, '2025/26', 25, 0, 0, 0, 0, 2250),
(42, '2025/26', 20, 2, 3, 2, 0, 1800),
(43, '2025/26', 26, 3, 9, 1, 0, 2340),
(44, '2025/26', 27, 20, 11, 2, 0, 2430),
(45, '2025/26', 27, 15, 10, 1, 0, 2430),
-- Barcelona
(48, '2025/26', 26, 8, 12, 2, 0, 2340),
(49, '2025/26', 25, 17, 6, 0, 0, 2250),
(50, '2025/26', 27, 13, 14, 0, 0, 2430),
-- Bayern Munich
(83, '2025/26', 27, 6, 10, 2, 0, 2430),
(84, '2025/26', 26, 24, 8, 0, 0, 2340),
(85, '2025/26', 25, 11, 9, 1, 0, 2250),
-- Dortmund
(89, '2025/26', 25, 16, 5, 1, 0, 2250),
(90, '2025/26', 24, 9, 6, 2, 0, 2160),
-- Inter
(133, '2025/26', 27, 5, 8, 3, 0, 2430),
(134, '2025/26', 26, 18, 7, 1, 0, 2340),
(135, '2025/26', 25, 14, 9, 0, 0, 2250),
-- Napoli
(139, '2025/26', 24, 19, 5, 1, 0, 2160),
(140, '2025/26', 26, 14, 13, 0, 0, 2340),
-- PSG
(163, '2025/26', 26, 5, 14, 1, 0, 2340),
(164, '2025/26', 25, 16, 9, 2, 0, 2250),
(165, '2025/26', 27, 13, 8, 0, 0, 2430),
-- Lille
(184, '2025/26', 26, 20, 6, 0, 0, 2340);

SET FOREIGN_KEY_CHECKS = 1;
