<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nomenclature']) && isset($_POST['timestamp'])) {
    $nomenclature = $conn->real_escape_string($_POST['nomenclature']);
    $timestamp = $conn->real_escape_string($_POST['timestamp']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get the issue record first (using both Nomenclature and timestamp as unique identifier)
        $sql = "SELECT * FROM issue WHERE Nomenclature = '$nomenclature' AND timestamp = '$timestamp'";
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            throw new Exception("Record not found");
        }
        
        $record = $result->fetch_assoc();
        $make = $record['make'];
        $issued_qty = $record['issuedqty'];
        
        // Delete the issue record
        $delete_sql = "DELETE FROM issue WHERE Nomenclature = '$nomenclature' AND timestamp = '$timestamp'";
        if (!$conn->query($delete_sql)) {
            throw new Exception("Failed to delete record");
        }
        
        // Update the parts table - add back to available, subtract from issued
        $update_sql = "UPDATE part SET 
                      availableqty = availableqty + '$issued_qty',
                      usedqty = usedqty - '$issued_qty'
                      WHERE Nomenclature = '$nomenclature' AND make = '$make'";
        
        if (!$conn->query($update_sql)) {
            throw new Exception("Failed to update parts inventory");
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    $conn->close();
    exit;
}