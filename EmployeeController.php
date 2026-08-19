<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class EmployeeController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Read: Fetch all employees with department info
    public function getAll() {
        $query = "SELECT e.id, e.first_name, e.last_name, e.email, e.job_title, e.salary, e.hire_date, 
                         e.department_id, d.name AS department_name, d.location 
                  FROM employees e 
                  JOIN departments d ON e.department_id = d.id 
                  ORDER BY e.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $employees = $stmt->fetchAll();

        sendResponse(200, ["status" => "success", "data" => $employees]);
    }

    // Read: Fetch single employee by ID
    public function getOne($id) {
        $query = "SELECT e.*, d.name AS department_name FROM employees e JOIN departments d ON e.department_id = d.id WHERE e.id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch();

        if ($employee) {
            sendResponse(200, ["status" => "success", "data" => $employee]);
        } else {
            sendResponse(404, ["status" => "error", "message" => "Employee not found."]);
        }
    }

    // Create: Add new employee record
    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);

        // Server-side Input Validation
        if (empty($input['first_name']) || empty($input['last_name']) || empty($input['email']) || 
            empty($input['job_title']) || empty($input['salary']) || empty($input['hire_date']) || empty($input['department_id'])) {
            sendResponse(400, ["status" => "error", "message" => "All fields are required."]);
        }

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            sendResponse(400, ["status" => "error", "message" => "Invalid email format."]);
        }

        $query = "INSERT INTO employees (first_name, last_name, email, job_title, salary, hire_date, department_id) 
                  VALUES (:first_name, :last_name, :email, :job_title, :salary, :hire_date, :department_id)";
        
        try {
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':first_name', $input['first_name']);
            $stmt->bindParam(':last_name', $input['last_name']);
            $stmt->bindParam(':email', $input['email']);
            $stmt->bindParam(':job_title', $input['job_title']);
            $stmt->bindParam(':salary', $input['salary']);
            $stmt->bindParam(':hire_date', $input['hire_date']);
            $stmt->bindParam(':department_id', $input['department_id'], PDO::PARAM_INT);
            $stmt->execute();

            sendResponse(201, ["status" => "success", "message" => "Employee created successfully.", "id" => $this->db->lastInsertId()]);
        } catch (PDOException $e) {
            sendResponse(400, ["status" => "error", "message" => "Could not create employee. Email might already exist."]);
        }
    }

    // Update: Edit existing employee details
    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input['first_name']) || empty($input['last_name']) || empty($input['email']) || 
            empty($input['job_title']) || empty($input['salary']) || empty($input['department_id'])) {
            sendResponse(400, ["status" => "error", "message" => "All required fields must be populated."]);
        }

        $query = "UPDATE employees 
                  SET first_name = :first_name, last_name = :last_name, email = :email, 
                      job_title = :job_title, salary = :salary, department_id = :department_id 
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':first_name', $input['first_name']);
        $stmt->bindParam(':last_name', $input['last_name']);
        $stmt->bindParam(':email', $input['email']);
        $stmt->bindParam(':job_title', $input['job_title']);
        $stmt->bindParam(':salary', $input['salary']);
        $stmt->bindParam(':department_id', $input['department_id'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            sendResponse(200, ["status" => "success", "message" => "Employee updated successfully."]);
        } else {
            sendResponse(500, ["status" => "error", "message" => "Failed to update employee."]);
        }
    }

    // Delete: Remove employee record
    public function delete($id) {
        $query = "DELETE FROM employees WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            sendResponse(200, ["status" => "success", "message" => "Employee deleted successfully."]);
        } else {
            sendResponse(500, ["status" => "error", "message" => "Failed to delete employee."]);
        }
    }
}
