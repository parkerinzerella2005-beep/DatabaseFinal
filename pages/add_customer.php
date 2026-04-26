<?php
session_start();
require '../config/database.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM CUSTOMER WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    if ($stmt->fetch()) {
        $error = "Username already taken.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO CUSTOMER (first_name, last_name, email, phone, username, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['phone'], $_POST['username'], $_POST['password']]);
        $success = "Customer added successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Customer - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Add Customer</h2>
  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert-success'>$success</div>"; ?>
  <form method="POST">
    <label>First Name</label><input type="text" name="first_name" required>
    <label>Last Name</label><input type="text" name="last_name" required>
    <label>Email</label><input type="email" name="email">
    <label>Phone</label><input type="text" name="phone">
    <label>Username</label><input type="text" name="username" required>
    <label>Password</label><input type="password" name="password" required>
    <input type="submit" value="Add Customer">
  </form>
</div>
</body>
</html>