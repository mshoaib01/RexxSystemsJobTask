<?php
// Include the class definitions and create instances as needed.
require_once 'Database.php';
require_once 'JsonFileReader.php';

// Database connection parameters
$host_name = "localhost"; // Your MySQL server hostname or IP address
$database_name = "event_booking_data_rexx_systems"; // The name of the database you want to connect to
$user_name = "root";     // Your MySQL username
$password = "";     // Your MySQL password

$responseForLoopData = "";
$filter_option= '';
$filter_value = '';


$database = new Database($host_name, $database_name, $user_name, $password);
$responseForLoopData = $database->getData(); // I make this call in case when you open page if there is already data in database get that data and display


if(isset($_POST['search_button'])) {
    if (!empty($_POST['filter_option']) && !empty($_POST['filter_value'])) {
        $filter_option = $_POST['filter_option'];
        $filter_value = $_POST['filter_value'];
        $responseForLoopData =  $database->filterData($filter_option,$filter_value);
    } else {
        $responseForLoopData =  $database->getData();
    }
}

if (isset($_POST['uploadfile_button']) && isset($_FILES['json_file'])) {
    $filePath = $_FILES['json_file']['tmp_name'];
    // Create a JsonFileReader object and get data
    $jsonReader = new JsonFileReader($filePath);
    $data = $jsonReader->getData();

    if (!empty($data)) {
        // Data to be inserted
        if ($database->saveData($data)) {
            $responseForLoopData = $database->getData();
        } else {
            echo "Error saving data to the database.";
        }
    } else {
        echo "No data available or an error occurred.";
    }
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rexx Systems</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/bootstrap.bundle.min.js"></script>
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>

<div class="container-fluid p-5 bg-primary text-white text-center">
    <h1>Rexx Systems Code Challenge</h1>
</div>

<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mynavbar">
            <form class="form-inline" method="POST">
                <div class="d-flex">
                    <div class="mx-2">
                        <select class="form-select" aria-label="Default select example" name="filter_option">
                            <option value="" selected>Filter by</option>
                            <option value="employee_name" <?= ($filter_option === 'employee_name') ? 'selected' : ''; ?>>Filter by Employee Name</option>
                            <option value="event_name" <?= ($filter_option === 'event_name') ? 'selected' : ''; ?>>Filter by Event Name</option>
                            <option value="event_date" <?= ($filter_option === 'event_date') ? 'selected' : ''; ?>>Filter by Event Date</option>
                        </select>
                    </div>
                    <div class="mx-2">
                        <input class="form-control" type="text" placeholder="Search" name="filter_value" value="<?= $filter_value; ?>">
                    </div>
                    <div class="mx-2">
                        <button class="btn btn-primary" type="submit" name="search_button">Search</button>
                    </div>
                </div>
            </form>
            <form class="form-inline" method="POST" enctype="multipart/form-data">
                <div class="d-flex">
                    <div class="mx-2">
                        <input class="form-control" type="file" placeholder="Select Json file" accept="application/JSON" name="json_file" required>
                    </div>
                    <div class="mx-2">
                        <button class="btn btn-primary" type="submit" name="uploadfile_button">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid mt-2">
    <table class="table table-dark table-striped">
        <thead>
        <tr>
            <th>Participation ID</th>
            <th>Employee Name</th>
            <th>Employee EMail</th>
            <th>Event Name</th>
            <th>Participation Fee</th>
            <th>Event Date</th>
            <th>Version</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($responseForLoopData)): ?>
            <?php foreach ($responseForLoopData['data'] as $row): ?>
                <tr>
                    <td><?= $row['participation_id']; ?></td>
                    <td><?= $row['employee_name']; ?></td>
                    <td><?= $row['employee_mail']; ?></td>
                    <td><?= $row['event_name']; ?></td>
                    <td><?= $row['participation_fee']; ?></td>
                    <td><?= $row['event_date']; ?></td>
                    <td><?= $row['version']; ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="7">
                    <p>Total Participation Fee: <?= $responseForLoopData['totalParticipationFee']; ?></p>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>


