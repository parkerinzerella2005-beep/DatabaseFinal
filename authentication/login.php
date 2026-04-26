<?php
session_start();
require '../config/database.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM EMPLOYEE WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = 'employee';
        header("Location: ../index.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM CUSTOMER WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = 'customer';
        header("Location: ../index.php");
        exit();
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<header><h1>ShipMyWhip</h1></header>

<div class="container">
  <h2>Login</h2>

  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>

  <form method="POST">
    <label>Username</label>
    <input type="text" name="username" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <input type="submit" value="Login">
  </form>

  <p>No account? <a href="signup.php">Sign Up</a></p>
</div>

</body>
</html>