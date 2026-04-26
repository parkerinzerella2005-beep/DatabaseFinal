<?php
session_start();
require '../config/database.php';
if ($_SESSION['role'] !== 'employee') { header("Location: ../index.php"); exit(); }

$error   = "";
$success = "";
$locations = $pdo->query("SELECT location_id, name FROM LOCATION")->fetchAll();

if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM VEHICLE WHERE vehicle_id = ?");
    $stmt->execute([$_POST['vehicle_id']]);
    $success = "Vehicle deleted.";
}

if (isset($_POST['update'])) {
    $stmt = $pdo->prepare("UPDATE VEHICLE SET make=?, model=?, trim=?, year=?, mileage=?, price=?, type=?, status=?, deliver=?, location_id=? WHERE vehicle_id=?");
    $stmt->execute([$_POST['make'], $_POST['model'], $_POST['trim'], $_POST['year'], $_POST['mileage'], $_POST['price'], $_POST['type'], $_POST['status'], isset($_POST['deliver']) ? 1 : 0, $_POST['location_id'], $_POST['vehicle_id']]);
    $success = "Vehicle updated.";
}

$vehicles = $pdo->query("SELECT v.*, l.name AS location_name FROM VEHICLE v JOIN LOCATION l ON v.location_id = l.location_id")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit/Delete - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    .vehicle-form {
      background: white;
      border: 1px solid #ddd;
      border-radius: 6px;
      padding: 15px 20px;
      margin-bottom: 12px;
    }
    .vehicle-form .row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: flex-end;
      margin-bottom: 10px;
    }
    .vehicle-form .field {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }
    .vehicle-form .field label {
      font-size: 0.75rem;
      color: #888;
      width: auto;
      margin: 0;
    }
    .vehicle-form .field input,
    .vehicle-form .field select {
      width: auto;
      margin: 0;
      padding: 6px 8px;
      font-size: 0.85rem;
    }
    .vehicle-form .field.sm input  { width: 80px; }
    .vehicle-form .field.md input  { width: 120px; }
    .vehicle-form .field.lg input  { width: 160px; }
    .vehicle-form .actions {
      display: flex;
      gap: 8px;
      margin-top: 5px;
    }
  </style>
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Edit / Delete Vehicles</h2>

  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert-success'>$success</div>"; ?>

  <?php foreach ($vehicles as $v): ?>
  <div class="vehicle-form">
    <form method="POST">
      <input type="hidden" name="vehicle_id" value="<?php echo $v['vehicle_id']; ?>">

      <div class="row">
        <div class="field md">
          <label>Make</label>
          <input type="text" name="make" value="<?php echo $v['make']; ?>">
        </div>
        <div class="field md">
          <label>Model</label>
          <input type="text" name="model" value="<?php echo $v['model']; ?>">
        </div>
        <div class="field md">
          <label>Trim</label>
          <input type="text" name="trim" value="<?php echo $v['trim']; ?>">
        </div>
        <div class="field sm">
          <label>Year</label>
          <input type="number" name="year" value="<?php echo $v['year']; ?>">
        </div>
        <div class="field sm">
          <label>Mileage</label>
          <input type="number" name="mileage" value="<?php echo $v['mileage']; ?>">
        </div>
        <div class="field sm">
          <label>Price</label>
          <input type="number" step="0.01" name="price" value="<?php echo $v['price']; ?>">
        </div>
      </div>

      <div class="row">
        <div class="field">
          <label>Type</label>
          <select name="type">
            <option value="car"        <?php echo $v['type']=='car'?'selected':''; ?>>Car</option>
            <option value="truck"      <?php echo $v['type']=='truck'?'selected':''; ?>>Truck</option>
            <option value="motorcycle" <?php echo $v['type']=='motorcycle'?'selected':''; ?>>Motorcycle</option>
          </select>
        </div>
        <div class="field">
          <label>Status</label>
          <select name="status">
            <option value="available"   <?php echo $v['status']=='available'?'selected':''; ?>>Available</option>
            <option value="sold"        <?php echo $v['status']=='sold'?'selected':''; ?>>Sold</option>
            <option value="transferred" <?php echo $v['status']=='transferred'?'selected':''; ?>>Transferred</option>
          </select>
        </div>
        <div class="field">
          <label>Location</label>
          <select name="location_id">
            <?php foreach ($locations as $loc): ?>
              <option value="<?php echo $loc['location_id']; ?>" <?php echo $v['location_id']==$loc['location_id']?'selected':''; ?>><?php echo $loc['name']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field" style="justify-content:flex-end;">
          <label>Delivery</label>
          <input type="checkbox" name="deliver" style="width:auto; margin:0;" <?php echo $v['deliver']?'checked':''; ?>>
        </div>
      </div>

      <div class="actions">
        <input type="submit" name="update" value="Update">
        <input type="submit" name="delete" value="Delete" class="danger" onclick="return confirm('Are you sure?')">
      </div>
    </form>
  </div>
  <?php endforeach; ?>
</div>
</body>
</html>