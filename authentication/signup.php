<?php
ob_start();
session_start();
require '../config/database.php';

$error = "";
$success = "";

$locations = $pdo->query("SELECT location_id, name FROM LOCATION")->fetchAll();
$ADMIN_KEY = "shipmywhip123";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $role       = $_POST['role'];

    $stmt = $pdo->prepare("SELECT * FROM EMPLOYEE WHERE username = ?");
    $stmt->execute([$username]);
    $taken = $stmt->fetch();

    if (!$taken) {
        $stmt = $pdo->prepare("SELECT * FROM CUSTOMER WHERE username = ?");
        $stmt->execute([$username]);
        $taken = $stmt->fetch();
    }

    if ($taken) {
        $error = "Username already taken.";
    } elseif ($role == 'employee') {
        $admin_key   = $_POST['admin_key'];
        $location_id = $_POST['location_id'];

        if ($admin_key !== $ADMIN_KEY) {
            $error = "Invalid admin key.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO EMPLOYEE (first_name, last_name, username, password, location_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $username, $password, $location_id]);
            header("Location: ../index.php");
            exit();
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO CUSTOMER (first_name, last_name, username, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $username, $password]);
        header("Location: ../index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Sign Up - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>

<header><h1>ShipMyWhip</h1></header>

<div class="container">
  <h2>Sign Up</h2>

  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>

  <form method="POST">
    <label>First Name</label>
    <input type="text" name="first_name" required>
    <label>Last Name</label>
    <input type="text" name="last_name" required>
    <label>Username</label>
    <input type="text" name="username" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <label>Account Type</label>
    <select name="role" id="role" onchange="toggleEmployee()">
      <option value="customer">Customer</option>
      <option value="employee">Employee</option>
    </select>

    <div id="employee_fields" style="display:none;">
      <label>Admin Key</label>
      <input type="password" name="admin_key">
      <label>Location</label>
      <select name="location_id">
        <?php foreach ($locations as $loc): ?>
          <option value="<?php echo $loc['location_id']; ?>"><?php echo $loc['name']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <input type="submit" value="Sign Up">
  </form>

  <p>Already have an account? <a href="login.php">Login</a></p>
</div>

<script>
function toggleEmployee() {
  var role = document.getElementById('role').value;
  document.getElementById('employee_fields').style.display = role === 'employee' ? 'block' : 'none';
}
</script>

</body>
</html>