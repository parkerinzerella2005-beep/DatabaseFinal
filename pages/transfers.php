<?php
session_start();
require '../config/database.php';
if ($_SESSION['role'] !== 'employee') { header("Location: ../index.php"); exit(); }

$error = "";
$success = "";
$vehicles  = $pdo->query("SELECT vehicle_id, make, model, year FROM VEHICLE WHERE status != 'sold'")->fetchAll();
$locations = $pdo->query("SELECT location_id, name FROM LOCATION")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['from_location_id'] == $_POST['to_location_id']) {
        $error = "From and To locations cannot be the same.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO TRANSFER (vehicle_id, from_location_id, to_location_id, transfer_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['vehicle_id'], $_POST['from_location_id'], $_POST['to_location_id'], $_POST['transfer_date']]);
        $stmt = $pdo->prepare("UPDATE VEHICLE SET location_id = ?, status = 'transferred' WHERE vehicle_id = ?");
        $stmt->execute([$_POST['to_location_id'], $_POST['vehicle_id']]);
        $success = "Vehicle transferred successfully!";
    }
}

$transfers = $pdo->query("
    SELECT t.*, v.make, v.model, v.year,
           l1.name AS from_name, l2.name AS to_name
    FROM TRANSFER t
    JOIN VEHICLE v ON t.vehicle_id = v.vehicle_id
    JOIN LOCATION l1 ON t.from_location_id = l1.location_id
    JOIN LOCATION l2 ON t.to_location_id = l2.location_id
    ORDER BY t.transfer_date DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Transfers - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Transfer a Vehicle</h2>
  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert-success'>$success</div>"; ?>
  <form method="POST">
    <label>Vehicle</label>
    <select name="vehicle_id">
      <?php foreach ($vehicles as $v): ?>
        <option value="<?php echo $v['vehicle_id']; ?>"><?php echo $v['year'].' '.$v['make'].' '.$v['model']; ?></option>
      <?php endforeach; ?>
    </select>
    <label>From Location</label>
    <select name="from_location_id">
      <?php foreach ($locations as $loc): ?>
        <option value="<?php echo $loc['location_id']; ?>"><?php echo $loc['name']; ?></option>
      <?php endforeach; ?>
    </select>
    <label>To Location</label>
    <select name="to_location_id">
      <?php foreach ($locations as $loc): ?>
        <option value="<?php echo $loc['location_id']; ?>"><?php echo $loc['name']; ?></option>
      <?php endforeach; ?>
    </select>
    <label>Transfer Date</label><input type="date" name="transfer_date" required>
    <input type="submit" value="Transfer Vehicle">
  </form>
  <hr>
  <h3>Transfer History</h3>
  <table>
    <tr><th>Vehicle</th><th>From</th><th>To</th><th>Date</th></tr>
    <?php foreach ($transfers as $t): ?>
    <tr>
      <td><?php echo $t['year'].' '.$t['make'].' '.$t['model']; ?></td>
      <td><?php echo $t['from_name']; ?></td>
      <td><?php echo $t['to_name']; ?></td>
      <td><?php echo $t['transfer_date']; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</body>
</html>