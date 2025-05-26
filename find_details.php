<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'db_config.php';

// --- AJAX endpoint for live part number search (for Add Existing Connectors & Required Connectors) ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'search_part_no' && isset($_GET['term'])) {
    $term = $conn->real_escape_string($_GET['term']);
    $sql = "SELECT DISTINCT Nomenclature, descp FROM part WHERE Nomenclature LIKE '$term%' OR descp LIKE '%$term%'";
    $result = $conn->query($sql);

    $part_numbers = [];
    while ($row = $result->fetch_assoc()) {
        $part_numbers[] = $row['Nomenclature'] . ' - ' . $row['descp'];
    }
    header('Content-Type: application/json');
    echo json_encode($part_numbers);
    exit;
}


// --- AJAX endpoint for fetching makes for a given part number ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_makes' && isset($_GET['part_no'])) {
    $part_no = $conn->real_escape_string($_GET['part_no']);
    $sql = "SELECT DISTINCT make FROM part WHERE Nomenclature = '$part_no'";
    $result = $conn->query($sql);

    $makes = [];
    while ($row = $result->fetch_assoc()) {
        $makes[] = $row['make'];
    }
    header('Content-Type: application/json');
    echo json_encode($makes);
    exit;
}

// --- AJAX endpoint for fetching part details ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_part_details' && isset($_GET['part_no']) && isset($_GET['make'])) {
    $part_no = $conn->real_escape_string($_GET['part_no']);
    $make = $conn->real_escape_string($_GET['make']);
    
    $sql = "SELECT quantity, usedqty, availableqty FROM part WHERE Nomenclature = '$part_no' AND make = '$make'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'found' => true,
            'quantity' => $row['quantity'],
            'usedqty' => $row['usedqty'],
            'availableqty' => $row['availableqty']
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoresManagement System</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        
        
        /* ===== FIND PART SECTION ===== */
        .find-part-section {
            background-color: #1e283a;
            padding: 25px;
            border-radius: 8px;
            margin: 40px auto;
            max-width: 800px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .find-part-section h3 {
            color: #ffffff;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.5rem;
            border-bottom: 2px solid #3a4a6b;
            padding-bottom: 10px;
        }

        /* Form Group Styling */
        .find-form-group {
            margin-bottom: 20px;
            position: relative; /* Needed for suggestion dropdown */
        }

        .find-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #a0a8c0;
            font-weight: 500;
        }

        .find-form-group input,
        .find-form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #3a4a6b;
            border-radius: 6px;
            background-color: #2a3447;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .find-form-group input:focus,
        .find-form-group select:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.2);
        }

        /* Suggestions Dropdown */
        .custom-suggestions {
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background-color: #2a3447;
            border: 1px solid #3a4a6b;
            border-top: none;
            border-radius: 0 0 6px 6px;
            z-index: 100;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .suggestion-item {
            padding: 10px 15px;
            color: #ffffff;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .suggestion-item:hover {
            background-color: #3a4a6b;
        }

        /* Details Button */
        #find-details-btn {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: block;
            margin: 20px auto 0;
        }

        #find-details-btn:hover {
            background-color: #3a80d2;
            transform: translateY(-2px);
        }

        #find-details-btn:disabled {
            background-color: #3a4a6b;
            cursor: not-allowed;
            transform: none;
        }

        /* Results Display */
        #find-details-result {
            background-color: #2a3447;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
            color: #ffffff;
            border-left: 4px solid #4a90e2;
            line-height: 1.6;
        }

        #find-details-result div {
            margin-bottom: 8px;
        }

        #find-details-result b {
            color: #4a90e2;
        }

        #find-details-result span.error {
            color: #ff6b6b;
            display: block;
            text-align: center;
        }

        /* Loading State */
        #find-details-result.loading {
            color: #a0a8c0;
            font-style: italic;
            text-align: center;
        }

        /* Make Dropdown Group */
        #find-make-group {
            transition: all 0.3s ease;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .find-part-section {
                padding: 15px;
                margin: 20px auto;
            }
            
            .find-form-group input,
            .find-form-group select {
                padding: 10px;
            }
            
            #find-details-btn {
                padding: 10px 20px;
            }
        }
        /* ... other styles remain unchanged ... */
        /* (Rest of your style block as before, omitted for brevity) */
    </style>
</head>
<body>
    <div class="header">
        <div class="background-container">
            <div class="background-image"></div>
        </div>
    </div>

    <div class="menu-bar">
        <a href="index.php"><img src="images/icons8-home-30.png" class="menu-icon" alt="Home">Home</a>
        <a href="find_details.php"><img src="images/icons8-view-30.png" class="menu-icon" alt="View">Search</a>
        <a href="view.php"><img src="images/icons8-view-30.png" class="menu-icon" alt="View">View</a>
        <a href="issue.php"><img src="images/icons8-view-30.png" class="menu-icon" alt="Issue">Issued List</a>
        <a href="#"><img src="images/icons8-user-30.png" class="menu-icon" alt="Login"><?php echo htmlspecialchars(getUsername()); ?></a>
        <a href="logout.php">Logout</a>
    </div>
    <!-- Find Part Section -->
    <div class="find-part-section">
        <h3>Find Part Details</h3>
        <div class="find-form-group">
            <label for="find-part-box">Search Part No:</label>
            <input type="text" id="find-part-box" placeholder="Type part number..." autocomplete="off">
            <datalist id="find-suggestions"></datalist>
        </div>
        <div class="find-form-group" id="find-make-group" style="display:none;">
            <label for="find-make-select">Select Make:</label>
            <select id="find-make-select"></select>
        </div>
        <button id="find-details-btn" style="display:none;" type="button">Get Details</button>
        <div id="find-details-result"></div>
    </div>
    
    <script src="javascript/findscript.js"></script>
</body>
</html>