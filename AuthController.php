<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class AuthController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login() {
        session_start();
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['email']) || empty($input['password'])) {
            sendResponse(400, ["status" => "error", "message" => "Email and password are required."]);
        }

        $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            sendResponse(400, ["status" => "error", "message" => "Invalid email format."]);
        }

        // Use parameterized prepared statement to prevent SQL Injection
        $query = "SELECT id, email, password_hash, role FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user && password_verify($input['password'], $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            sendResponse(200, [
                "status" => "success",
                "message" => "Login successful",
                "user" => [
                    "id" => $user['id'],
                    "email" => $user['email'],
                    "role" => $user['role']
                ]
            ]);
        } else {
            sendResponse(401, ["status" => "error", "message" => "Invalid credentials."]);
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        sendResponse(200, ["status" => "success", "message" => "Logged out successfully"]);
    }
}
