<?php
// Database connection parameters
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ngl_database');

// Create a connection to MySQL
db_connect();
function db_connect() {
    global $link;
    $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check the connection
    if($link === false) {
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }
}

// Delete submission if the delete button is pressed
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $delete_sql = "DELETE FROM submissions WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        if (mysqli_stmt_execute($stmt)) {
            echo "<p>Submission deleted successfully.</p>";
        } else {
            echo "<p>Error: Could not execute delete query.</p>";
        }
        mysqli_stmt_close($stmt);
    }
}

// Reconnect to MySQL for displaying submissions
db_connect();

// Search functionality
$search_name = '';
if (isset($_GET['search_name'])) {
    $search_name = mysqli_real_escape_string($link, $_GET['search_name']);
}

$sql = "SELECT * FROM submissions";
if (!empty($search_name)) {
    $sql .= " WHERE name LIKE '%$search_name%'";
}
$sql .= " ORDER BY id DESC";

if ($result = mysqli_query($link, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        echo "<h1 style='text-align: center; color: #BBE1FA;'>Admin Page - View Submissions</h1>";
        echo "<form method='get' action='' style='text-align: center; margin-bottom: 20px;'>";
        echo "<input type='text' name='search_name' value='" . htmlspecialchars($search_name) . "' placeholder='Search by name'>";
        echo "<input type='submit' value='Search' style='background-color: #3282B8; color: white;'>";
        echo "</form>";
        echo "<table border='1' cellpadding='10' style='margin: 10px auto; text-align: center; background-color: #1B262C; color: #BBE1FA; width: 90%;'>";
        echo "<tr style='background-color: #0F4C75;'><th>ID</th><th>Name</th><th>Message</th><th>Files</th><th>Action</th></tr>";
        while ($row = mysqli_fetch_array($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['text'])) . "</td>";
            echo "<td>";
            $filePaths = json_decode($row['file_paths'], true);
            if (!empty($filePaths)) {
                echo "<ul>";
                foreach ($filePaths as $filePath) {
                    echo "<li><a href='" . htmlspecialchars($filePath) . "' target='_blank' style='color: #3282B8;'>" . basename($filePath) . "</a></li>";
                }
                echo "</ul>";
            }
            echo "</td>";
            echo "<td>";
            echo "<form method='post' action=''>";
            echo "<input type='hidden' name='delete_id' value='" . htmlspecialchars($row['id']) . "'>";
            echo "<input type='submit' value='Delete' style='background-color: red; color: white;' onclick='return confirm(\"Are you sure you want to delete this submission?\");'>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        mysqli_free_result($result);
    } else {
        echo "<p style='text-align: center; color: #BBE1FA;'>No submissions found.</p>";
    }
} else {
    echo "<p style='text-align: center; color: #BBE1FA;'>Error: Could not execute query.</p>";
}

// Close the MySQL connection
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page - View User Submissions</title>
    <style>
        body {
            background-color: #1B262C;
            color: #BBE1FA;
            text-align: center;
        }
        a {
            color: #3282B8;
        }
    </style>
</head>
<body>
    <a href="admin_view_user_submissions.php">Go to Admin View Submissions Page</a>
</body>
</html>
