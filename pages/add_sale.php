<?php
session_start();
require '../config/database.php';
if ($_SESSION['role'] !== 'employee') { header("Location: ../index.php"); exit(); }

$error = "";
$success = "";
$vehicles  = $pdo->query("SELECT vehicle_id, make, model, year FROM VEHICLE WHERE status = 'available'")->fetchAll();
$customers = $pdo->query("SELECT customer_id, first_name, last_name FROM CUSTOMER")->fetchAll();
$employees = $pdo->query("SELECT employee_id, first_name, last_name FROM EMPLOYEE")->fetchAll();
$locations = $pdo->query("SELECT location_id, name FROM LOCATION")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO SALE (vehicle_id, customer_id, employee_id, location_id, sale_date, sale_amount, delivery_used) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['vehicle_id'], $_POST['customer_id'], $_POST['employee_id'], $_POST['location_id'], $_POST['sale_date'], $_POST['sale_amount'], isset($_POST['delivery_used']) ? 1 : 0]);
    $stmt = $pdo->prepare("UPDATE VEHICLE SET status = 'sold' WHERE vehicle_id = ?");
    $stmt->execute([$_POST['vehicle_id']]);
    $success = "Sale recorded successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>New Sale - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Record a Sale</h2>
  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert-success'>$success</div>"; ?>
  <form method="POST">
    <label>Vehicle</label>
    <select name="vehicle_id">
      <?php foreach ($vehicles as $v): ?>
        <option value="<?php echo $v['vehicle_id']; ?>"><?php echo $v['year'].' '.$v['make'].' '.$v['model']; ?></option>
      <?php endforeach; ?>
    </select>
    <label>Customer</label>
    <select name="customer_id">
      <?php foreach ($customers as $c): ?>
        <option value="<?php echo $c['customer_id']; ?>"><?php echo $c['first_name'].' '.$c['last_name']; ?></option>
      <?php endforeach; ?>
    </select>
    <label>Employee</label>
    <select name="employee_id">
      <?php foreach ($employees as $e): ?>
        <option value="<?php echo $e['employee_id']; ?>"><?php echo $e['first_name'].' '.$e['last_name']; ?></option>
      <?php endforeach; ?>
    </select>
    <label>Location</label>
    <select name="location_id">
      <?php foreach ($locations as $loc): ?>
        <option value="<?php echo $loc['location_id']; ?>"><?php echo $loc['name']; ?></option>
      <?php endforeach; ?>
    </select>
    <label>Sale Date</label><input type="date" name="sale_date" required>
    <label>Sale Amount</label><input type="number" step="0.01" name="sale_amount" required>
    <label><input type="checkbox" name="delivery_used" style="width:auto;"> Delivery Used</label>
    <input type="submit" value="Record Sale">
  </form>
</div>
</body>
</html>