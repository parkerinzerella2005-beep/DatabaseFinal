<?php
session_start();
require '../config/database.php';
if ($_SESSION['role'] !== 'employee') { header("Location: ../index.php"); exit(); }

$error = "";
$success = "";
$locations = $pdo->query("SELECT location_id, name FROM LOCATION")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO VEHICLE (make, model, trim, year, mileage, price, type, status, deliver, location_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'available', ?, ?)");
    $stmt->execute([$_POST['make'], $_POST['model'], $_POST['trim'], $_POST['year'], $_POST['mileage'], $_POST['price'], $_POST['type'], isset($_POST['deliver']) ? 1 : 0, $_POST['location_id']]);
    $success = "Vehicle added successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Vehicle - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Add Vehicle</h2>
  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert-success'>$success</div>"; ?>
  <form method="POST">
    <label>Make</label><input type="text" name="make" required>
    <label>Model</label><input type="text" name="model" required>
    <label>Trim</label><input type="text" name="trim">
    <label>Year</label><input type="number" name="year" required>
    <label>Mileage</label><input type="number" name="mileage" required>
    <label>Price</label><input type="number" step="0.01" name="price" required>
    <label>Type</label>
    <select name="type">
      <option value="car">Car</option>
      <option value="truck">Truck</option>
      <option value="motorcycle">Motorcycle</option>
    </select>
    <label>Location</label>
    <select name="location_id">
      <?php foreach ($locations as $loc): ?>
        <option value="<?php echo $loc['location_id']; ?>"><?php echo $loc['name']; ?></option>
      <?php endforeach; ?>
    </select>
    <label><input type="checkbox" name="deliver" style="width:auto;"> Delivery Available</label>
    <input type="submit" value="Add Vehicle">
  </form>
</div>
</body>
</html>