<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'db_config.php';

// Handle AJAX live search
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $sql = "SELECT * FROM part WHERE Nomenclature LIKE '$search_query%'";
    $result = $conn->query($sql);

    $response = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Parts</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #1a2332;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: white;
        }

        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-bar {
            display: flex;
        }

        .search-bar input[type="text"] {
            width: 300px;
            padding: 10px;
            border-radius: 4px;
            outline: none;
            font-size: 16px;
            font-weight: bolder;
            background-color: #1e283a;
            color: white;
        }

        .record-count {
            color: #9fef00;
            font-weight: bold;
            font-size: 16px;
            padding: 10px;
            background-color: #1e283a;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            color: white;
        }

        table th {
            background-color: #9fef00;
            color: black;
        }

        table tr:hover {
            background-color: #1e283a;
        }

        .no-results {
            text-align: center;
            color: #dc3545;
            font-size: 18px;
            margin-top: 20px;
            display: none;
        }
        
        .download-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .download-btn:hover {
            background-color: #8cd600;
            transform: translateY(-2px);
        }
        
        .download-btn img {
            margin-right: 5px;
        }
        
        .search-utils {
            display: flex;
            align-items: center;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const tableBody = document.querySelector('tbody');
        const recordCount = document.querySelector('.record-count');
        const noResults = document.querySelector('.no-results');

        function fetchResults(query) {
            fetch('view.php?ajax=1&search=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    if (data.length > 0) {
                        noResults.style.display = 'none';
                        data.forEach((part, index) => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${index + 1}</td>
                                <td>${part.Nomenclature}</td>
                                <td>${part.make}</td>
                                <td>${part.quantity}</td>
                                <td>${part.usedqty}</td>
                                <td>${part.availableqty}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                        recordCount.textContent = 'Found: ' + data.length + (data.length === 1 ? ' record' : ' records');
                    } else {
                        noResults.style.display = 'block';
                        recordCount.textContent = 'Found: 0 records';
                    }
                });
        }

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            fetchResults(query);
        });

        // Initial load
        fetchResults('');
    });
    </script>
</head>
<body>
    <div class="header">
        <div class="background-container">
            <div class="background-image"></div>
        </div>
    </div>

    <div class="menu-bar">
        <a href="index.php"><img src="images/icons8-home-30.png" class="menu-icon" alt="Home">Home</a>
        <!--<a href="download.php"><img src="images/icons8-download-24.png" class="menu-icon" alt="Download">Download</a>-->
        <a href="view.php"><img src="images/icons8-view-30.png" class="menu-icon" alt="View">View</a>
        <a href="issue.php"><img src="images/icons8-view-30.png" class="menu-icon" alt="Issue">Issued List</a>
        <a href="#"><img src="images/icons8-user-30.png" class="menu-icon" alt="Login"><?php echo htmlspecialchars(getUsername()); ?></a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h2>LIST OF CONNECTORS: EO-SAAW/RCI</h2><br>

        <div class="search-container">
            <div class="search-bar">
                <form onsubmit="return false;">
                    <input type="text" name="search" placeholder="Search by Part No" autocomplete="off">
                </form>
            </div>
            
            <div class="search-utils">
                <a href="download.php" class="download-btn">
                    <img src="images/icons8-download-24.png" width="16" height="16" alt="Download">
                    Download PDF
                </a>
                <div class="record-count">Found: 0 records</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Nomenclature</th>
                    <th>Make</th>
                    <th>Total Qty</th>
                    <th>Issued Qty</th>
                    <th>Available Qty</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows added by JS -->
            </tbody>
        </table>

        <div class="no-results">No matching records found.</div>
    </div>
</body>
</html>

<?php $conn->close(); ?>