# KickOff - Football Information Platform

A FotMob-inspired football (soccer) web application built with PHP and MySQL. Browse live scores, league standings, team squads, and player statistics across the top European competitions.

## Features

- **Match Dashboard** — View upcoming, live, and finished matches filtered by league
- **Match Details** — Live scores, match events (goals, cards, substitutions), lineups, and statistics (possession, shots, etc.)
- **League Standings** — Full tables with W/D/L records, goal difference, points, and top scorers per league
- **Team Pages** — Squad roster by position (GK/DEF/MID/FWD), recent results, and upcoming fixtures
- **Player Profiles** — Career stats, goal history, nationality, and position info
- **Search** — Full-text search across teams and players
- **User Accounts** — Register, login, and manage a personal favorites list (teams, players, leagues)

### Supported Leagues

- Premier League
- La Liga
- Bundesliga
- Serie A
- Ligue 1
- UEFA Champions League

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.4+ |
| Database | MySQL 8.0+ / MySQLi |
| Frontend | HTML5, CSS3 (vanilla) |
| Auth | PHP Sessions + password_hash() |

## Project Structure

```
project-fotmob/
├── classes/
│   ├── Database.php      # Singleton DB connection
│   ├── User.php          # Registration, login, logout
│   ├── Match.php         # Match queries & events
│   ├── Team.php          # Team data & squad
│   ├── Player.php        # Player profiles & stats
│   ├── League.php        # League management
│   ├── Standing.php      # League standings
│   └── Favorite.php      # User favorites
├── includes/
│   ├── config.php        # DB & site configuration
│   ├── navbar.php        # Navigation component
│   └── footer.php        # Footer component
├── css/
│   └── style.css         # Dark theme stylesheet
├── index.php             # Home — match dashboard
├── match.php             # Match detail page
├── team.php              # Team detail page
├── player.php            # Player profile page
├── standings.php         # Standings & top scorers
├── search.php            # Search page
├── favorites.php         # User favorites
├── login.php             # Login page
├── register.php          # Registration page
├── logout.php            # Session logout
└── fotmob.sql            # Database schema + seed data
```

## Installation

### Requirements

- PHP 7.4 or higher
- MySQL 8.0 or higher
- A local server like XAMPP, Laragon, or WAMP

### Steps

1. **Clone or download** the project into your web server's root directory:
   ```
   /xampp/htdocs/Portofolio-opdrachten/project-fotmob/
   ```

2. **Import the database** via phpMyAdmin or the MySQL CLI:
   ```bash
   mysql -u root -p < fotmob.sql
   ```
   This creates the `fotmob` database and inserts all seed data.

3. **Configure the database** in `includes/config.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'fotmob');
   ```

4. **Start your server** and open the site in your browser:
   ```
   http://localhost/Portofolio-opdrachten/project-fotmob/
   ```

## Database Schema

The database consists of 11 tables:

| Table | Description |
|-------|-------------|
| `users` | User accounts |
| `leagues` | Competition info |
| `teams` | Club data |
| `players` | Player profiles |
| `matches` | Match fixtures and results |
| `match_events` | Goals, cards, substitutions |
| `match_stats` | Per-match statistics |
| `match_lineups` | Starting XI and subs |
| `standings` | League table positions |
| `player_stats` | Aggregated player stats |
| `favorites` | User-saved favorites |

## Security

- Passwords hashed with `password_hash()` / `password_verify()`
- All queries use **prepared statements** to prevent SQL injection
- User output escaped with `htmlspecialchars()`
- Session-based authentication

## Screenshots

> _Add screenshots here once the project is running._

## License

This project was built as a portfolio assignment and is for educational purposes only.
