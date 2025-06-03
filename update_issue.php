<?php
session_start();
require_once 'auth.php';
requireLogin();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'error' => ''];
    
    try {
        // Validate inputs
        $new_qty = (int)$_POST['issuedqty'];
        if ($new_qty <= 0) {
            throw new Exception("Quantity must be greater than zero");
        }

        $conn->begin_transaction();
        
        // Get original record data
        $original_sql = "SELECT * FROM issue WHERE Nomenclature = ? AND timestamp = ?";
        $stmt = $conn->prepare($original_sql);
        $stmt->bind_param("ss", $_POST['original_nomenclature'], $_POST['original_timestamp']);
        $stmt->execute();
        $original = $stmt->get_result()->fetch_assoc();
        
        if (!$original) {
            throw new Exception("Original record not found");
        }
        
        // Calculate quantity difference
        $qty_diff = $new_qty - $original['issuedqty'];
        
        // If increasing quantity, check available stock
        if ($qty_diff > 0) {
            $stock_sql = "SELECT availableqty FROM part WHERE Nomenclature = ? AND make = ?";
            $stmt = $conn->prepare($stock_sql);
            $stmt->bind_param("ss", $_POST['original_nomenclature'], $original['make']);
            $stmt->execute();
            $available_stock = $stmt->get_result()->fetch_assoc()['availableqty'];
            $total_stock = $available_stock + $original['issuedqty'];
            if ($total_stock < $qty_diff) {
                throw new Exception("Not enough available stock. Only $total_stock units available");
            }
        }
        
        // Update issue record
        $update_sql = "UPDATE issue SET 
                      person_name = ?,
                      designation = ?,
                      purpose = ?,
                      issuedqty = ?
                      WHERE Nomenclature = ? AND timestamp = ?";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param(
            "sssiss",
            $_POST['person_name'],
            $_POST['designation'],
            $_POST['purpose'],
            $new_qty,
            $_POST['original_nomenclature'],
            $_POST['original_timestamp']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update issue record");
        }
        
        // Update parts inventory if quantity changed
        if ($qty_diff != 0) {
            $parts_sql = "UPDATE part SET 
                         availableqty = availableqty - ?,
                         usedqty = usedqty + ?
                         WHERE Nomenclature = ? AND make = ?";
            
            $stmt = $conn->prepare($parts_sql);
            $stmt->bind_param(
                "iiss",
                $qty_diff,
                $qty_diff,
                $_POST['original_nomenclature'],
                $original['make']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update parts inventory");
            }
        }
        
        $conn->commit();
        $response['success'] = true;
    } catch (Exception $e) {
        $conn->rollback();
        $response['error'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}