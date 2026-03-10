<?php
class FootballMatch {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    /** All matches for a given date (YYYY-MM-DD), ordered by time */
    public function getMatchesByDate(string $date): array {
        $stmt = $this->conn->prepare(
            "SELECT m.*, l.name AS league_name, l.country,
                    ht.name AS home_name, ht.short_name AS home_short,
                    at.name AS away_name, at.short_name AS away_short
             FROM matches m
             JOIN leagues l ON l.id = m.league_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE DATE(m.match_date) = ?
             ORDER BY m.match_date ASC"
        );
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $matches = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $matches;
    }

    /** Matches for a league, ordered by date descending */
    public function getMatchesByLeague(int $leagueId, int $limit = 20): array {
        $stmt = $this->conn->prepare(
            "SELECT m.*, l.name AS league_name,
                    ht.name AS home_name, ht.short_name AS home_short,
                    at.name AS away_name, at.short_name AS away_short
             FROM matches m
             JOIN leagues l ON l.id = m.league_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE m.league_id = ?
             ORDER BY m.match_date DESC
             LIMIT ?"
        );
        $stmt->bind_param('ii', $leagueId, $limit);
        $stmt->execute();
        $result  = $stmt->get_result();
        $matches = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $matches;
    }

    /** Single match with teams + league */
    public function getMatchById(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT m.*, l.name AS league_name, l.country,
                    ht.name AS home_name, ht.short_name AS home_short,
                    ht.stadium AS home_stadium,
                    at.name AS away_name, at.short_name AS away_short
             FROM matches m
             JOIN leagues l ON l.id = m.league_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE m.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $match = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $match ?: null;
    }

    /** Events (goals, cards) for a match, ordered by minute */
    public function getEvents(int $matchId): array {
        $stmt = $this->conn->prepare(
            "SELECT me.*, p.name AS player_name, t.name AS team_name, t.short_name
             FROM match_events me
             LEFT JOIN players p ON p.id = me.player_id
             JOIN teams t        ON t.id = me.team_id
             WHERE me.match_id = ?
             ORDER BY me.minute ASC"
        );
        $stmt->bind_param('i', $matchId);
        $stmt->execute();
        $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $events;
    }

    /** Stats for both teams in a match */
    public function getStats(int $matchId): array {
        $stmt = $this->conn->prepare(
            "SELECT ms.*, t.name AS team_name, t.short_name
             FROM match_stats ms
             JOIN teams t ON t.id = ms.team_id
             WHERE ms.match_id = ?
             ORDER BY ms.id ASC"
        );
        $stmt->bind_param('i', $matchId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $stats;
    }

    /** Starting lineup for a match */
    public function getLineups(int $matchId): array {
        $stmt = $this->conn->prepare(
            "SELECT ml.*, p.name AS player_name, p.position, p.number,
                    t.name AS team_name, t.id AS team_id
             FROM match_lineups ml
             JOIN players p ON p.id = ml.player_id
             JOIN teams t   ON t.id = ml.team_id
             WHERE ml.match_id = ? AND ml.is_starting = 1
             ORDER BY t.id ASC, p.position ASC"
        );
        $stmt->bind_param('i', $matchId);
        $stmt->execute();
        $lineups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $lineups;
    }

    /** Recent matches for a team */
    public function getRecentByTeam(int $teamId, int $limit = 5): array {
        $stmt = $this->conn->prepare(
            "SELECT m.*, l.name AS league_name,
                    ht.name AS home_name, ht.short_name AS home_short,
                    at.name AS away_name, at.short_name AS away_short
             FROM matches m
             JOIN leagues l ON l.id = m.league_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE (m.home_team_id = ? OR m.away_team_id = ?)
               AND m.status = 'finished'
             ORDER BY m.match_date DESC
             LIMIT ?"
        );
        $stmt->bind_param('iii', $teamId, $teamId, $limit);
        $stmt->execute();
        $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $matches;
    }

    /** Next upcoming match for a team */
    public function getUpcomingByTeam(int $teamId, int $limit = 3): array {
        $stmt = $this->conn->prepare(
            "SELECT m.*, l.name AS league_name,
                    ht.name AS home_name, ht.short_name AS home_short,
                    at.name AS away_name, at.short_name AS away_short
             FROM matches m
             JOIN leagues l ON l.id = m.league_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE (m.home_team_id = ? OR m.away_team_id = ?)
               AND m.status = 'scheduled'
             ORDER BY m.match_date ASC
             LIMIT ?"
        );
        $stmt->bind_param('iii', $teamId, $teamId, $limit);
        $stmt->execute();
        $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $matches;
    }

    /** All matches grouped by league for the home page */
    public function getTodaysMatches(): array {
        $today = date('Y-m-d');
        return $this->getMatchesByDate($today);
    }

    /** Latest finished matches across all leagues */
    public function getRecentResults(int $limit = 12): array {
        $stmt = $this->conn->prepare(
            "SELECT m.*, l.name AS league_name, l.country,
                    ht.name AS home_name, ht.short_name AS home_short,
                    at.name AS away_name, at.short_name AS away_short
             FROM matches m
             JOIN leagues l ON l.id = m.league_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE m.status = 'finished'
             ORDER BY m.match_date DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $matches;
    }

    /** Upcoming scheduled matches */
    public function getUpcoming(int $limit = 12): array {
        $stmt = $this->conn->prepare(
            "SELECT m.*, l.name AS league_name, l.country,
                    ht.name AS home_name, ht.short_name AS home_short,
                    at.name AS away_name, at.short_name AS away_short
             FROM matches m
             JOIN leagues l ON l.id = m.league_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE m.status = 'scheduled'
             ORDER BY m.match_date ASC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $matches;
    }
}
