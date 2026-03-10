<?php
class User {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function register(string $username, string $email, string $password): bool|string {
        if ($this->usernameExists($username)) return 'Username already taken.';
        if ($this->emailExists($email))    return 'Email already in use.';

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare(
            'INSERT INTO users (username, email, password) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('sss', $username, $email, $hash);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok ? true : 'Registration failed. Please try again.';
    }

    public function login(string $username, string $password): bool {
        $stmt = $this->conn->prepare(
            'SELECT id, username, password FROM users WHERE username = ?'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        return false;
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
    }

    private function usernameExists(string $username): bool {
        $stmt = $this->conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    private function emailExists(string $email): bool {
        $stmt = $this->conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
}
