<?php
class League {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getAll(): array {
        $result = $this->conn->query('SELECT * FROM leagues ORDER BY id ASC');
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->conn->prepare('SELECT * FROM leagues WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $league = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $league ?: null;
    }
}
