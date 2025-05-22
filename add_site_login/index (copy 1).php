<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form_type = $_POST['form_type'];
    
    switch ($form_type) {
        case 'add_part':
            $part_no = $conn->real_escape_string($_POST['part_name']);
            $quantity = (int)$_POST['conn_count'];
            
            // Check if part_no already exists
            $check_sql = "SELECT quantity FROM parts WHERE part_no = '$part_no'";
            $result = $conn->query($check_sql);
            
            if ($result->num_rows > 0) {
                $_SESSION['alert'] = [
                    'type' => 'error',
                    'message' => "Part number $part_no already exists!"
                ];
            } else {
                $sql = "INSERT INTO parts (part_no, quantity) VALUES ('$part_no', $quantity)";
                if ($conn->query($sql) === TRUE) {
                    $_SESSION['alert'] = [
                        'type' => 'success',
                        'message' => "New part $part_no added successfully with $quantity connectors!"
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'type' => 'error',
                        'message' => "Error adding part: " . $conn->error
                    ];
                }
            }
            break;
            
        case 'add_conn':
            $part_no = $conn->real_escape_string($_POST['conn_name']);
            $add_quantity = (int)$_POST['pin_count'];
            
            // Check if part_no exists
            $check_sql = "SELECT quantity FROM parts WHERE part_no = '$part_no'";
            $result = $conn->query($check_sql);
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $current_quantity = $row['quantity'];
                $new_quantity = $current_quantity + $add_quantity;
                
                $sql = "UPDATE parts SET quantity = $new_quantity WHERE part_no = '$part_no'";
                if ($conn->query($sql) === TRUE) {
                    $_SESSION['alert'] = [
                        'type' => 'success',
                        'message' => "Successfully added $add_quantity connectors to part $part_no\n\nPrevious quantity: $current_quantity\nNew quantity: $new_quantity"
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'type' => 'error',
                        'message' => "Error adding connectors: " . $conn->error
                    ];
                }
            } else {
                $_SESSION['alert'] = [
                    'type' => 'warning',
                    'message' => "Part number does not exist! Please add the part first."
                ];
            }
            break;
            
        case 'required_conn':
            $part_no = $conn->real_escape_string($_POST['part_name']);
            $remove_quantity = (int)$_POST['req_conn'];
            
            // Check if part_no exists
            $check_sql = "SELECT quantity FROM parts WHERE part_no = '$part_no'";
            $result = $conn->query($check_sql);
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $current_quantity = $row['quantity'];
                
                if ($remove_quantity > $current_quantity) {
                    $_SESSION['alert'] = [
                        'type' => 'warning',
                        'message' => "Insufficient connectors!\n\nAvailable: $current_quantity\nRequired: $remove_quantity\nShortage: " . ($remove_quantity - $current_quantity)
                    ];
                } else {
                    $new_quantity = $current_quantity - $remove_quantity;
                    $sql = "UPDATE parts SET quantity = $new_quantity WHERE part_no = '$part_no'";
                    if ($conn->query($sql) === TRUE) {
                        $_SESSION['alert'] = [
                            'type' => 'success',
                            'message' => "Successfully removed connectors from part $part_no\n\nPrevious quantity: $current_quantity\nRemoved: $remove_quantity\nRemaining: $new_quantity"
                        ];
                    } else {
                        $_SESSION['alert'] = [
                            'type' => 'error',
                            'message' => "Error removing connectors: " . $conn->error
                        ];
                    }
                }
            } else {
                $_SESSION['alert'] = [
                    'type' => 'warning',
                    'message' => "Part number does not exist!"
                ];
            }
            break;
    }
    
    header("Location: index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connector Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .menu-bar {
            background-color: #34495e;
            padding: 1rem;
            text-align: center;
        }

        .menu-bar a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .menu-bar a:hover {
            background-color: #2c3e50;
        }

        .container {
            max-width: 800px;
            min-height: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #f5f6fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-close {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0 0.5rem;
        }

        .toggle-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .toggle-btn {
            flex: 1;
            padding: 0.5rem;
            margin: 0 0.25rem;
            border: none;
            background-color: #bdc3c7;
            font-size: 15px;
            font-weight: bolder;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .toggle-btn.active {
            background-color: #3498db;
            color: white;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-size: 15px;
            font-weight: bolder;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 15px;
            font-weight: bolder;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bolder;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #219a52;
        }

        /* Custom Message Box Styles */
        .message-box {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 300px;
            text-align: center;
        }

        .message-box p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        .message-box button {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .message-box button.ok {
            background-color: #27ae60;
            color: white;
        }

        .message-box button.cancel {
            background-color: #e74c3c;
            color: white;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Connector Management System</h1>
    </div>

    <div class="menu-bar">
        <a href="#">Home</a>
        <a href="#">Download</a>
        <a href="#">Logout</a>
    </div>

    <div class="container">
        <div class="toggle-buttons">
            <button class="toggle-btn active" onclick="showForm('add-part')">Add Part</button>
            <button class="toggle-btn" onclick="showForm('add-conn')">Add Connectors</button>
            <button class="toggle-btn" onclick="showForm('required-conn')">Required Connectors</button>
        </div>

        <form id="add-part" class="form-section active" action="process.php" method="POST">
            <input type="hidden" name="form_type" value="add_part">
            <div class="form-group">
                <label for="part-name">Enter New Part No:</label>
                <input type="text" id="part-name" name="part_name" placeholder="Enter new part number" required>
            </div>
            <div class="form-group">
                <label for="part-conn">Enter No. of Conn:</label>
                <input type="number" id="part-conn" name="conn_count" placeholder="Enter number of connectors" required>
            </div>
            <button type="submit" class="submit-btn">Add Part</button>
        </form>

        <form id="add-conn" class="form-section" action="process.php" method="POST">
            <input type="hidden" name="form_type" value="add_conn">
            <div class="form-group">
                <label for="conn-name">Enter Part No:</label>
                <input type="text" id="conn-name" name="conn_name" placeholder="Enter existing part number" required>
            </div>
            <div class="form-group">
                <label for="pin-count">Enter No. of Additional Conn:</label>
                <input type="number" id="pin-count" name="pin_count" placeholder="Enter number of additional connectors" required>
            </div>
            <button type="submit" class="submit-btn">Add Connectors</button>
        </form>

        <form id="required-conn" class="form-section" action="process.php" method="POST">
            <input type="hidden" name="form_type" value="required_conn">
            <div class="form-group">
                <label for="req-part">Enter Part No:</label>
                <input type="text" id="req-part" name="part_name" placeholder="Enter existing part number" required>
            </div>
            <div class="form-group">
                <label for="req-conn">Enter No. of Required Conn:</label>
                <input type="number" id="req-conn" name="req_conn" placeholder="Enter number of required connectors" required>
            </div>
            <button type="submit" class="submit-btn">Required Connectors</button>
        </form>
    </div>

    <!-- Custom Message Box -->
    <div class="overlay" id="overlay"></div>
    <div class="message-box" id="messageBox">
        <p id="messageText"><?php echo isset($_SESSION['alert']) ? $_SESSION['alert']['message'] : ''; ?></p>
        <button class="ok" onclick="hideMessageBox()">OK</button>
    </div>

    <script>
        // Function to show the custom message box
        function showMessageBox() {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('messageBox').style.display = 'block';
        }

        // Function to hide the custom message box
        function hideMessageBox() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('messageBox').style.display = 'none';
        }

        // Show the message box if there is a PHP message
        <?php if (isset($_SESSION['alert'])): ?>
            showMessageBox();
        <?php unset($_SESSION['alert']); endif; ?>

        // Function to switch between forms
        function showForm(formId) {
            document.querySelectorAll('.form-section').forEach(form => form.classList.remove('active'));
            document.getElementById(formId).classList.add('active');

            // Update active class for toggle buttons
            document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`[onclick="showForm('${formId}')"]`).classList.add('active');
        }
    </script>
</body>
</html>