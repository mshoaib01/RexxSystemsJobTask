<?php
class Database
{
    private $pdo;
    public function __construct($host, $dbname, $username, $password)
    {
        try {
            // Create a PDO instance
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            // Set the PDO error mode to exception
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function saveData($data)
    {
        try {
            // Start a transaction
            $this->pdo->beginTransaction();
            // Prepare the insert statement for Employees table
            $stmtEmployees = $this->pdo->prepare("INSERT INTO Employees (employee_name, employee_mail) VALUES (:employee_name, :employee_mail) ON DUPLICATE KEY UPDATE  employee_mail = VALUES(employee_mail)");

            // Prepare the insert statement for Events table
            $stmtEvents = $this->pdo->prepare("INSERT IGNORE  INTO Events (event_id, event_name) VALUES (:event_id, :event_name)");

            // Prepare the insert statement for Bookings table
            $stmtBookings= $this->pdo->prepare("INSERT IGNORE INTO Bookings (participation_id, employee_id, event_id, participation_fee, version, event_date) VALUES (:participation_id, :employee_id, :event_id, :participation_fee, :version, :event_date)");

            // Loop to insert  records
            foreach ($data as $item) {
                // Check if the employee with the same email already exists
                $existingEmployeeId = $this->getExistingEmployeeId($item['employee_mail']);
                // I get the $employee_id of the newly inserted employee record that I will use in $stmtBookings
                if ($existingEmployeeId !== false) {
                    $employee_id = $existingEmployeeId;
                } else {
                    // Execute the insert statement for Employees table
                    $stmtEmployees->execute([
                        ':employee_name' => $item['employee_name'],
                        ':employee_mail' => $item['employee_mail'],
                    ]);
                    $employee_id = $this->pdo->lastInsertId();
                }

                // Execute the insert statement for Events table
                $stmtEvents->execute([
                    ':event_id' => $item['event_id'],
                    ':event_name' => $item['event_name'],
                ]);

                // Execute the insert statement for Bookings table
                $stmtBookings->execute([
                    ':participation_id'=>$item['participation_id'],
                    ':employee_id' => $employee_id,
                    ':event_id' => $item['event_id'],
                    ':participation_fee' => $item['participation_fee'],
                    ':version' => isset($item['version']) ? $item['version'] : null,
                    ':event_date' => $item['event_date'],
                ]);

            }

            // Commit the transaction
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getData() {
        try {
            // Query to retrieve data
            $query = "SELECT 
                    Employees.employee_id,
                    Employees.employee_name,
                    Employees.employee_mail,
                    Events.event_name,
                    Bookings.participation_id,
                    Bookings.participation_fee,
                    Bookings.event_date,
                    Bookings.version
                FROM 
                    Employees
                INNER JOIN 
                    Bookings ON Employees.employee_id = Bookings.employee_id
                INNER JOIN 
                    Events ON Bookings.event_id = Events.event_id";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate the total Participation Fee
            $totalParticipationFee = array_sum(array_column($result, 'participation_fee'));
            return array("data" => $result, "totalParticipationFee" => $totalParticipationFee, "error" => null);
        } catch (PDOException $e) {
            return array("data" => array(), "totalParticipationFee" => 0, "error" => $e->getMessage());
        }

    }

    public function filterData($filter, $filterValue) {
        try {
            // Query to retrieve data
            $filterValue = '%'.$filterValue.'%';
            $query = "SELECT 
                Employees.employee_id,
                Employees.employee_name,
                Employees.employee_mail,
                Events.event_name,
                Bookings.participation_id,
                Bookings.participation_fee,
                Bookings.event_date,
                Bookings.version
            FROM 
                Employees
            INNER JOIN 
                Bookings ON Employees.employee_id = Bookings.employee_id
            INNER JOIN 
                Events ON Bookings.event_id = Events.event_id
            WHERE 
                $filter LIKE :filterValue";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                ':filterValue' => $filterValue,
            ]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate the total Participation Fee
            $totalParticipationFee = array_sum(array_column($result, 'participation_fee'));
            return array("data" => $result, "totalParticipationFee" => $totalParticipationFee, "error" => null);
        } catch (PDOException $e) {
            return array("data" => array(), "totalParticipationFee" => 0, "error" => $e->getMessage());
        }

    }

    public function getExistingEmployeeId($employeeMail) {
        try {
            $stmt = $this->pdo->prepare("SELECT employee_id FROM Employees WHERE employee_mail = :employee_mail");
            $stmt->execute([':employee_mail' => $employeeMail]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['employee_id'] : false;
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}