<?php
session_start();

// Redirect to index.php if no form data is found in the session
if (!isset($_SESSION['form_type']) && !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Retrieve form data from the session
$form_type = $_SESSION['form_type'];
$message = "Are you sure you want to perform this action?";

// Customize the confirmation message based on the form type
switch ($form_type) {
    case 'add_part':
        $message = "Are you sure you want to add the part " . $_SESSION['part_no'] . " with " . $_SESSION['quantity'] . " connectors?";
        break;
    case 'add_conn':
        $message = "Are you sure you want to add " . $_SESSION['add_quantity'] . " connectors to part " . $_SESSION['part_no'] . "?";
        break;
    case 'required_conn':
        $message = "Are you sure you want to remove " . $_SESSION['remove_quantity'] . " connectors from part " . $_SESSION['part_no'] . "?";
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <style>
        /* Confirmation box styling */
        .confirmation-box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .confirmation-box p {
            margin-bottom: 20px;
            font-size: 18px;
        }
        .confirmation-box button {
            margin: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .confirmation-box button.confirm {
            background-color: #27ae60;
            color: white;
        }
        .confirmation-box button.cancel {
            background-color: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <p><?php echo $message; ?></p>
        <form action="index.php" method="POST">
            <button type="submit" name="confirm" class="confirm">Confirm</button>
            <button type="submit" name="cancel" class="cancel">Cancel</button>
        </form>
    </div>
</body>
</html>