<?php
class Player {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT p.*, t.name AS team_name, t.id AS team_id,
                    l.name AS league_name
             FROM players p
             JOIN teams t   ON t.id = p.team_id
             JOIN leagues l ON l.id = t.league_id
             WHERE p.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $player = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $player ?: null;
    }

    public function getStats(int $playerId): ?array {
        $stmt = $this->conn->prepare(
            'SELECT * FROM player_stats WHERE player_id = ? ORDER BY season DESC LIMIT 1'
        );
        $stmt->bind_param('i', $playerId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $stats ?: null;
    }

    public function getGoalEvents(int $playerId, int $limit = 5): array {
        $stmt = $this->conn->prepare(
            "SELECT me.minute, m.match_date, m.home_score, m.away_score,
                    ht.name AS home_name, at.name AS away_name, m.id AS match_id
             FROM match_events me
             JOIN matches m ON m.id = me.match_id
             JOIN teams ht  ON ht.id = m.home_team_id
             JOIN teams at  ON at.id = m.away_team_id
             WHERE me.player_id = ? AND me.event_type = 'goal'
             ORDER BY m.match_date DESC
             LIMIT ?"
        );
        $stmt->bind_param('ii', $playerId, $limit);
        $stmt->execute();
        $goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $goals;
    }

    public function search(string $query): array {
        $like = '%' . $query . '%';
        $stmt = $this->conn->prepare(
            "SELECT p.*, t.name AS team_name, l.name AS league_name
             FROM players p
             JOIN teams t   ON t.id = p.team_id
             JOIN leagues l ON l.id = t.league_id
             WHERE p.name LIKE ? OR p.nationality LIKE ?
             ORDER BY p.name ASC"
        );
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $players;
    }

    public function getTopScorers(int $leagueId, int $limit = 10): array {
        $stmt = $this->conn->prepare(
            "SELECT p.id, p.name, p.nationality, p.position, p.number,
                    t.name AS team_name,
                    ps.goals, ps.assists, ps.matches_played
             FROM player_stats ps
             JOIN players p ON p.id = ps.player_id
             JOIN teams t   ON t.id = p.team_id
             WHERE t.league_id = ?
             ORDER BY ps.goals DESC
             LIMIT ?"
        );
        $stmt->bind_param('ii', $leagueId, $limit);
        $stmt->execute();
        $scorers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $scorers;
    }
}
