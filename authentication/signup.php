<?php
ob_start();
session_start();
require '../config/database.php';

$error = "";
$success = "";

// fetch locations for the dropdown
$locations = $pdo->query("SELECT location_id, name FROM LOCATION")->fetchAll();

$ADMIN_KEY = "shipmywhip123"; // change this to whatever you want

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $role       = $_POST['role'];

    // check if username is taken in either table
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
<head><title>Sign Up - ShipMyWhip</title></head>
<body>

<h2>Sign Up</h2>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

<form method="POST">
  First Name: <input type="text" name="first_name"><br><br>
  Last Name:  <input type="text" name="last_name"><br><br>
  Username:   <input type="text" name="username"><br><br>
  Password:   <input type="password" name="password"><br><br>

  Account Type:
  <select name="role" id="role" onchange="toggleEmployee()">
    <option value="customer">Customer</option>
    <option value="employee">Employee</option>
  </select><br><br>

  <div id="employee_fields" style="display:none;">
    Admin Key: <input type="password" name="admin_key"><br><br>
    Location:
    <select name="location_id">
      <?php foreach ($locations as $loc): ?>
        <option value="<?php echo $loc['location_id']; ?>"><?php echo $loc['name']; ?></option>
      <?php endforeach; ?>
    </select><br><br>
  </div>

  <input type="submit" value="Sign Up">
</form>

<p>Already have an account? <a href="login.php">Login</a></p>

<script>
function toggleEmployee() {
  var role = document.getElementById('role').value;
  document.getElementById('employee_fields').style.display = role === 'employee' ? 'block' : 'none';
}
</script>

</body>
</html>