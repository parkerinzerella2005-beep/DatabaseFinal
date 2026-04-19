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
<head><title>Login - ShipMyWhip</title></head>
<body>

<h2>Login</h2>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
  Username: <input type="text" name="username"><br><br>
  Password: <input type="password" name="password"><br><br>
  <input type="submit" value="Login">
</form>

<p><a href="../index.php">Back to Home</a></p>
<p>No account? <a href="signup.php">Sign Up</a></p>
</body>
</html>