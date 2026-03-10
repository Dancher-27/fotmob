<?php
class Favorite {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function add(int $userId, string $type, int $referenceId): bool {
        $stmt = $this->conn->prepare(
            'INSERT IGNORE INTO favorites (user_id, type, reference_id) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('isi', $userId, $type, $referenceId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function remove(int $userId, string $type, int $referenceId): bool {
        $stmt = $this->conn->prepare(
            'DELETE FROM favorites WHERE user_id = ? AND type = ? AND reference_id = ?'
        );
        $stmt->bind_param('isi', $userId, $type, $referenceId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function isFavorite(int $userId, string $type, int $referenceId): bool {
        $stmt = $this->conn->prepare(
            'SELECT id FROM favorites WHERE user_id = ? AND type = ? AND reference_id = ?'
        );
        $stmt->bind_param('isi', $userId, $type, $referenceId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    /** All favorited teams for a user (with team details) */
    public function getFavoriteTeams(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT t.*, l.name AS league_name, f.id AS fav_id
             FROM favorites f
             JOIN teams t   ON t.id = f.reference_id
             JOIN leagues l ON l.id = t.league_id
             WHERE f.user_id = ? AND f.type = 'team'
             ORDER BY t.name ASC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $teams;
    }

    /** All favorited players for a user */
    public function getFavoritePlayers(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT p.*, t.name AS team_name, l.name AS league_name, f.id AS fav_id
             FROM favorites f
             JOIN players p ON p.id = f.reference_id
             JOIN teams t   ON t.id = p.team_id
             JOIN leagues l ON l.id = t.league_id
             WHERE f.user_id = ? AND f.type = 'player'
             ORDER BY p.name ASC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $players;
    }

    /** All favorited leagues for a user */
    public function getFavoriteLeagues(int $userId): array {
        $stmt = $this->conn->prepare(
            "SELECT l.*, f.id AS fav_id
             FROM favorites f
             JOIN leagues l ON l.id = f.reference_id
             WHERE f.user_id = ? AND f.type = 'league'
             ORDER BY l.name ASC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $leagues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $leagues;
    }

    public function toggle(int $userId, string $type, int $referenceId): string {
        if ($this->isFavorite($userId, $type, $referenceId)) {
            $this->remove($userId, $type, $referenceId);
            return 'removed';
        }
        $this->add($userId, $type, $referenceId);
        return 'added';
    }
}
