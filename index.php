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
        die("<div class='message error'>ERROR: Could not connect. " . mysqli_connect_error() . "</div>");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $_POST['name'] : 'Anonymous';
    $text = isset($_POST['text']) ? $_POST['text'] : '';
    
    // Directory to store uploaded files
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle file uploads (images, videos, files)
    $filePaths = [];
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        foreach ($_FILES['files']['name'] as $key => $fileName) {
            $tmpName = $_FILES['files']['tmp_name'][$key];
            $fileType = $_FILES['files']['type'][$key];
            $fileSize = $_FILES['files']['size'][$key];
            
            // Generate unique filename to avoid overwriting
            $newFileName = $uploadDir . uniqid() . '-' . basename($fileName);
            
            // Move file to upload directory
            if (move_uploaded_file($tmpName, $newFileName)) {
                $filePaths[] = $newFileName;
            } else {
                echo "<div class='message error'>Error uploading file: $fileName</div>";
            }
        }
    }

    // Convert file paths to a JSON string for storage
    $filePathsJson = json_encode($filePaths);

    // Insert the data into the MySQL database
    $sql = "INSERT INTO submissions (name, text, file_paths) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_text, $param_file_paths);

        $param_name = $name;
        $param_text = $text;
        $param_file_paths = $filePathsJson;

        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='message active'></div>";
        } else {
            echo "<div class='message error'>Error: Could not save data. Please try again.</div>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "<div class='message error'>Error: Could not prepare statement.</div>";
    }
}

// Close the MySQL connection
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGL Submission Form</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            padding: 20px;
            box-sizing: border-box;
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
            margin: auto;
        }

        .gun-container {
            margin-bottom: 20px;
            text-align: center;
        }

        .gun-container img {
            width: 100%;
            max-width: 200px;
            height: auto;
        }

        h1 {
            font-size: 2em;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #444;
            text-align: left;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
        }

        input[type="file"] {
            margin-bottom: 20px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            width: 100%;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-weight: bold;
            display: none;
        }

        .message.active {
            display: block;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (min-width: 769px) {
            body {
                flex-direction: row;
                justify-content: center;
            }

            .gun-container {
                align-self: center;
               left:20px;
                position: absolute;
            }
        }

        @media (max-width: 768px) {
            body {
                align-items: flex-start;
                padding-top: 40px;
            }

            .container {
                padding: 20px;
            }

            h1 {
                font-size: 1.5em;
            }

            input[type="text"],
            textarea {
                padding: 10px;
            }

            input[type="submit"] {
                padding: 12px;
                font-size: 1em;
            }

            .gun-container {
                order: -1;
                margin-bottom: 20px;
                text-align: center;
            }

            .gun-container img {
                max-width: 150px;
            }
        }

        @media (max-width: 480px) {
            .container {
                width: 90%;
            }

            input[type="text"],
            textarea {
                font-size: 0.9em;
            }

            input[type="submit"] {
                font-size: 0.9em;
                padding: 10px;
            }

            .gun-container img {
                max-width: 100px;
            }
        }
    </style>
    <script>
        function showMessage() {
            const messageDiv = document.querySelector('.message');
            messageDiv.classList.add('active');
            setTimeout(() => {
                messageDiv.classList.remove('active');
            }, 3000);
        }
    </script>
</head>
<body>
<div class="gun-container">
    <img src="https://media.tenor.com/_wnkrl7rTbYAAAAi/gunpoint-pointing-gun-at-you.gif" alt="Pointing a gun image">
    <h3>Mag memessage ka o hindi? :></h3>
</div>
<div class="container">
    <h1>Submit Your Message</h1>
    <form action="" method="post" enctype="multipart/form-data" onsubmit="showMessage();">
        <label for="name">Name (optional):</label>
        <input type="text" id="name" name="name" placeholder="Enter your name">

        <label for="text">Your Text:</label>
        <textarea id="text" name="text" rows="5" placeholder="Write your message here..."></textarea>

        <label for="files">Attach Files (optional - images, videos, documents):</label>
        <input type="file" id="files" name="files[]" multiple>

        <input type="submit" value="Submit">
    </form>
</div>
</body>
</html>
