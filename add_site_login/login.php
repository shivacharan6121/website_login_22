<?php 
error_reporting(0); 

session_start();

// If already logged in, redirect to index
if(isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}



$username = $_POST['username'];
$password = $_POST['password'];


        
$dbServername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "connectors.db";

$conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);

if($conn->connect_error){
    die("connection failed : ".$conn->connect_error);
} 
else{
    $stmt = $conn->prepare("select * from users where username= ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt_result = $stmt->get_result();
    if($stmt_result->num_rows > 0) {
        $data = $stmt_result->fetch_assoc();
        if($data['password'] === $password) {
            $_SESSION['username'] = $data['username'];
            //$_SESSION['name'] = $data['name'];
            $_SESSION['loggedin'] = true;
            // Redirect to index page
            $username = $_SESSION['username'];
            echo '<script>alert("Welcome '.$username.' for SAAW Store Management"); window.location.href = "index.php";</script>';   
            exit();    
        } else {
            echo '<script>alert("Invalid username or password"); window.location.href = "login.html";</script>';   
            exit();    
            
        } 
    } else {
        // Redirect to another page
        echo '<script>alert("Invalid username or password"); window.location.href = "login.html";</script>';   
        exit();    
  
}
}
    
?>