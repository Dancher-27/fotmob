<?php
class Standing {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getByLeague(int $leagueId): array {
        $stmt = $this->conn->prepare(
            "SELECT s.*, t.name AS team_name, t.short_name,
                    (s.goals_for - s.goals_against) AS goal_diff
             FROM standings s
             JOIN teams t ON t.id = s.team_id
             WHERE s.league_id = ?
             ORDER BY s.points DESC, goal_diff DESC, s.goals_for DESC"
        );
        $stmt->bind_param('i', $leagueId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getTeamPosition(int $leagueId, int $teamId): int {
        $rows = $this->getByLeague($leagueId);
        foreach ($rows as $i => $row) {
            if ((int)$row['team_id'] === $teamId) return $i + 1;
        }
        return 0;
    }
}
