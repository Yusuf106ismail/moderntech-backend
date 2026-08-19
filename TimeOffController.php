<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class TimeOffController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Read: Fetch all time-off requests
    public function getAll() {
        $query = "SELECT t.*, CONCAT(e.first_name, ' ', e.last_name) AS employee_name, e.email 
                  FROM time_off_requests t 
                  JOIN employees e ON t.employee_id = e.id 
                  ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $requests = $stmt->fetchAll();

        sendResponse(200, ["status" => "success", "data" => $requests]);
    }

    // Create: Submit time-off request
    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['employee_id']) || empty($input['start_date']) || empty($input['end_date']) || empty($input['reason'])) {
            sendResponse(400, ["status" => "error", "message" => "All fields are required."]);
        }

        $query = "INSERT INTO time_off_requests (employee_id, start_date, end_date, reason) 
                  VALUES (:employee_id, :start_date, :end_date, :reason)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':employee_id', $input['employee_id'], PDO::PARAM_INT);
        $stmt->bindParam(':start_date', $input['start_date']);
        $stmt->bindParam(':end_date', $input['end_date']);
        $stmt->bindParam(':reason', $input['reason']);

        if ($stmt->execute()) {
            sendResponse(201, ["status" => "success", "message" => "Time-off request submitted successfully."]);
        } else {
            sendResponse(500, ["status" => "error", "message" => "Failed to submit request."]);
        }
    }

    // Update Status: Approve/Reject request
    public function updateStatus($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['status']) || !in_array($input['status'], ['approved', 'rejected', 'pending'])) {
            sendResponse(400, ["status" => "error", "message" => "Invalid status value."]);
        }

        $query = "UPDATE time_off_requests SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $input['status']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            sendResponse(200, ["status" => "success", "message" => "Time-off status updated to " . $input['status']]);
        } else {
            sendResponse(500, ["status" => "error", "message" => "Failed to update status."]);
        }
    }
}
