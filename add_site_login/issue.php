<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'db_config.php';

// Handle AJAX live search
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $sql = "SELECT * FROM issue WHERE Nomenclature LIKE '$search_query%'";
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

// Handle search functionality
$search_query = '';
$searching = false;
$total_records = 0;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
    $searching = true;
}

// Fetch matching parts when searching, otherwise return all records
$sql = "SELECT * FROM issue";
if (!empty($search_query)) {
    $sql .= " WHERE Nomenclature LIKE '%$search_query%'";
}
$sql .= " ORDER BY timestamp DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Get total number of records
$total_records = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>View Parts</title>
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

        .search-bar button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bolder;
            font-size: 16px;
            margin-left: 10px;
        }

        .search-bar button:hover {
            background-color: #0056b3;
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
            border-collapse: collapse;
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
        }
        
        .print-btn {
            background-color: #9fef00;
            color: black;
            padding: 8px 15px;
            border: none;
            font-weight: bold;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .print-btn:hover {
            background-color: #8cd600;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            overflow: auto;
        }
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 800px;
            color: black;
            box-sizing: border-box;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 10px 0;
            color: black;
        }
        .modal-header h2 {
            font-weight: bold;
            color: black;
        }
        .modal-body {
            overflow-y: auto;
            flex-grow: 1;
            box-sizing: border-box;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .modal-buttons {
            text-align: right;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .modal-document {
            border: 2px solid black;
            padding: 30px;
            background-color: white;
            width: 100%;
            box-sizing: border-box;
            color: black;
        }
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .document-container, .input-form {
                width: 100%;
                margin-right: 0;
                margin-bottom: 20px;
            }
            .modal-content {
                width: 95%;
                margin: 2% auto;
                box-sizing: border-box;
            }
            .modal-document {
                padding: 15px;
                transform: scale(0.85);
            }
        }
        /* Document-specific styles */
        .document-title {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 30px;
            text-decoration: underline;
        }
        .document-subtitle {
            text-align: left;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .document-line {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 5px;
        }
        .document-label {
            width: 150px;
            font-weight: bold;
        }
        .document-value {
            flex-grow: 1;
            padding-left: 10px;
        }
        .signature-section {
            display: flex;
            justify-content: right;
            margin-right: 90px;
            margin-top: 100px;
        }
        .signature-box {
            text-align: right;
            width: 200px;
        }
        .approval-section {
            text-align: center;
            margin-top: 40px;
        }
        /* Button styles */
        .cancel-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .cancel-btn:hover {
            background-color: #c82333;
        }
        .print-but {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-but:hover {
            background-color: #218838;
        }
        
        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .modal-document, .modal-document * {
                visibility: visible;
            }
            .modal-document {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: auto;
                margin: 0;
                border: 2px solid #000 !important;
                padding: 30px !important;
                background-color: white;
                box-sizing: border-box;
                border-radius: 0;
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            .modal-header, .modal-buttons {
                display: none;
            }
            @page {
                size: A4;
                margin: 20mm;
            }
        }
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
        <a href="view.php"><img src="images/icons8-view-30.png" class="menu-icon" alt="View">View</a>
        <a href="issue.php"><img src="images/icons8-view-30.png" class="menu-icon" alt="Issue">Issued List</a>
        <a href="#"><img src="images/icons8-user-30.png" class="menu-icon" alt="Login"><?php echo htmlspecialchars(getUsername()); ?></a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <h2>LIST OF CONNECTORS ISSUED: EO-SAAW/RCI</h2><br>

        <!-- Search Bar and Record Count -->
        <div class="search-container">
            <div class="search-bar">
                <form onsubmit="return false;">
                    <input type="text" name="search" placeholder="Search by Part No" value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
                </form>
            </div>
            
            <div class="search-utils">
                <a href="issue_download.php" class="download-btn">
                    <img src="images/icons8-download-24.png" width="16" height="16" alt="Download">
                    Download PDF
                </a>
                <div class="record-count">
                    <?php 
                    if ($searching) {
                        echo "Found: $total_records record".($total_records != 1 ? 's' : '');
                    } else {
                        echo "Total: $total_records record".($total_records != 1 ? 's' : '');
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Display Records -->
        <table>
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Nomenclature</th>
                    <th>Make</th>
                    <th>Issued Qty</th>
                    <th>Person Name</th>
                    <th>Designation</th>
                    <th>Purpose</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="resultsTable">
                <?php
                $serial_no = 1;
                while ($row = $result->fetch_assoc()): 
                    // Split timestamp into date and time
                    $timestamp = strtotime($row['timestamp']);
                    $date = date('d/m/Y', $timestamp);
                    $time = date('h:i A', $timestamp);
                ?>
                    <tr>
                        <td><?php echo $serial_no++; ?></td>
                        <td><?php echo htmlspecialchars($row['Nomenclature']); ?></td>
                        <td><?php echo htmlspecialchars($row['make']); ?></td>
                        <td><?php echo htmlspecialchars($row['issuedqty']); ?></td>
                        <td><?php echo htmlspecialchars($row['person_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                        <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                        <td><?php echo $date; ?></td>
                        <td><?php echo $time; ?></td>
                        <td>
                            <button class="print-btn" onclick="showPrintDocument(
                                ':&nbsp;<?php echo htmlspecialchars($row['person_name'], ENT_QUOTES); ?>',
                                ':&nbsp;<?php echo htmlspecialchars($row['designation'] ?? '', ENT_QUOTES); ?>',
                                ':&nbsp;<?php echo htmlspecialchars($row['Nomenclature'] , ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['make'], ENT_QUOTES); ?>',
                                ':&nbsp;<?php echo htmlspecialchars($row['issuedqty'], ENT_QUOTES); ?>',
                                ':&nbsp;<?php echo $date; ?>',
                                ':&nbsp;<?php echo $time; ?>'
                            )">Print</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="no-results" id="noResults" style="display: <?php echo $total_records === 0 ? 'block' : 'none'; ?>">No matching records found.</div>
    </div>
    
    <!-- Print Modal -->
    <div id="printModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Document Preview</h2>
                <p>Review your document before printing:</p>
            </div>
            <div class="modal-body">
                <div class="modal-document">
                    <div id="printDocumentContent" style="font-family: Arial, sans-serif; color: black;">
                        <!-- Document content will be inserted here -->
                    </div>
                </div>
            </div>
            <div class="modal-buttons">
                <button onclick="closeModal()" class="cancel-btn" id="cancel-btn">Cancel</button>
                <button onclick="printDocument()" class="print-but" id="print-but">Print</button>
            </div>
        </div>
    </div>
    
    <script>
        // Live search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const tableBody = document.getElementById('resultsTable');
            const recordCount = document.querySelector('.record-count');
            const noResults = document.getElementById('noResults');

            function fetchResults(query) {
                fetch('issue.php?ajax=1&search=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        tableBody.innerHTML = '';
                        if (data.length > 0) {
                            noResults.style.display = 'none';
                            data.forEach((issue, index) => {
                                const timestamp = new Date(issue.timestamp);
                                const date = timestamp.toLocaleDateString('en-GB');
                                const time = timestamp.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
                                
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${index + 1}</td>
                                    <td>${issue.Nomenclature}</td>
                                    <td>${issue.make}</td>
                                    <td>${issue.issuedqty}</td>
                                    <td>${issue.person_name}</td>
                                    <td>${issue.designation || 'N/A'}</td>
                                    <td>${issue.purpose || ''}</td>
                                    <td>${date}</td>
                                    <td>${time}</td>
                                    <td>
                                        <button class="print-btn" onclick="showPrintDocument(
                                            ':&nbsp;${issue.person_name}',
                                            ':&nbsp;${issue.designation || ''}',
                                            ':&nbsp;${issue.Nomenclature}',
                                            '${issue.make}',
                                            ':&nbsp;${issue.issuedqty}',
                                            ':&nbsp;${date}',
                                            ':&nbsp;${time}'
                                        )">Print</button>
                                    </td>
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
        });

        // Print functionality
        function showPrintDocument(name, designation, partno, make, quantity, date, time) {
            const documentHTML = `
                <div class="document-title">SAAW STORES MANAGEMENT</div>
                <br><br>
                <div class="document-subtitle">Following Items are issued to:</div>
                <br>
                <div class="document-line">
                    <div class="document-label">Name</div>
                    <div class="document-value">${name}</div>
                </div>
                <div class="document-line">
                    <div class="document-label">Designation</div>
                    <div class="document-value">${designation}</div>
                </div>
                <div class="document-line">
                    <div class="document-label">Connector Part No</div>
                    <div class="document-value">${partno} (${make})</div>
                </div>
                <div class="document-line">
                    <div class="document-label">Quantity</div>
                    <div class="document-value">${quantity}</div>
                </div>
                <div class="document-line">
                    <div class="document-label">Date of Issue</div>
                    <div class="document-value">${date}</div>
                </div>
                <div class="document-line">
                    <div class="document-label">Time of Issue</div>
                    <div class="document-value">${time}</div>
                </div>
                <br><br>
                <div class="signature-section">
                    <div class="signature-box">
                        <div>Signature</div>
                    </div>
                </div>
                
                <div class="approval-section">
                    <div>Approved</div><br><br><br>
                    <div>Project Director<br>EO-SAAW</div>
                </div>
                <br><br><br><br><br>
            `;
            
            document.getElementById('printDocumentContent').innerHTML = documentHTML;
            document.getElementById('printModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('printModal').style.display = 'none';
        }

        function printDocument() {
            const printContent = document.getElementById('printDocumentContent').innerHTML;
            
            const win = window.open('', '', 'width=900,height=650');
            win.document.write(`
                <html>
                <head>
                    <title>EO-SAAW Stores Management</title>
                    <style>
                        body * {
                            visibility: hidden;
                        }
                        .document-title {
                            text-align: center;
                            font-size: 30px;
                            font-weight: bold;
                            margin-top: 25px;
                            margin-bottom: 30px;
                            text-decoration: underline;
                        }
                        .document-subtitle {
                            text-align: left;
                            font-size: 16px;
                            margin-bottom: 30px;
                        }
                        .document-line {
                            display: flex;
                            margin-bottom: 15px;
                            padding-bottom: 5px;
                        }
                        .document-label {
                            width: 250px;
                            font-weight: bold;
                        }
                        .document-value {
                            flex-grow: 1;
                            padding-left: 10px;
                        }
                        .signature-section {
                            display: flex;
                            justify-content: right;
                            margin-right: 50px;
                            margin-top:80px;
                        }
                        .signature-box {
                            text-align: right;
                            width: 200px;
                        }
                        .approval-section {
                            text-align: center;
                            margin-top: 60px;
                            margin-bottom: 70px;
                        }
                        .modal-document, .modal-document * {
                            visibility: visible;
                        }
                        .modal-document {
                            position: absolute;
                            left: 0;
                            top: 0;
                            width: 100%;
                            height: auto;
                            margin: 0;
                            border: 2px solid #000 !important;
                            padding: 30px !important;
                            background-color: white;
                            box-sizing: border-box;
                            border-radius: 0;
                            page-break-after: avoid;
                            page-break-inside: avoid;
                        }
                        .modal-header, .modal-buttons {
                            display: none;
                        }
                        @page {
                            size: A4;
                            margin: 15mm;
                        }
                    </style>
                </head>
                <body>
                    <div class="modal-document">
                        ${printContent}
                    </div>
                </body>
                </html>
            `);
            win.document.close();
            win.focus();
            win.print();
            win.close();
        }

        window.onclick = function(event) {
            const modal = document.getElementById('printModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>