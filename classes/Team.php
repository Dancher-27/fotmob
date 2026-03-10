<?php
class Team {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare(
            "SELECT t.*, l.name AS league_name, l.country, l.season
             FROM teams t
             JOIN leagues l ON l.id = t.league_id
             WHERE t.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $team = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $team ?: null;
    }

    public function getByLeague(int $leagueId): array {
        $stmt = $this->conn->prepare(
            'SELECT * FROM teams WHERE league_id = ? ORDER BY name ASC'
        );
        $stmt->bind_param('i', $leagueId);
        $stmt->execute();
        $teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $teams;
    }

    public function getSquad(int $teamId): array {
        $stmt = $this->conn->prepare(
            "SELECT * FROM players WHERE team_id = ?
             ORDER BY FIELD(position,'GK','DEF','MID','FWD'), number ASC"
        );
        $stmt->bind_param('i', $teamId);
        $stmt->execute();
        $players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $players;
    }

    public function search(string $query): array {
        $like  = '%' . $query . '%';
        $stmt  = $this->conn->prepare(
            "SELECT t.*, l.name AS league_name
             FROM teams t
             JOIN leagues l ON l.id = t.league_id
             WHERE t.name LIKE ? OR t.short_name LIKE ?
             ORDER BY t.name ASC"
        );
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $teams;
    }

    public function getAll(): array {
        $result = $this->conn->query(
            "SELECT t.*, l.name AS league_name
             FROM teams t
             JOIN leagues l ON l.id = t.league_id
             ORDER BY l.id ASC, t.name ASC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
