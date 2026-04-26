<?php
session_start();
require '../config/database.php';

$error = "";
$success = "";

// handle customer request to buy
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_vehicle'])) {
    $vehicle_id  = $_POST['vehicle_id'];
    $customer_id = $_POST['customer_id'];

    // check if customer already has a pending request on this vehicle
    $stmt = $pdo->prepare("SELECT * FROM REQUEST WHERE vehicle_id = ? AND customer_id = ? AND status = 'pending'");
    $stmt->execute([$vehicle_id, $customer_id]);

    if ($stmt->fetch()) {
        $error = "You already have a pending request on this vehicle.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO REQUEST (vehicle_id, customer_id, status, request_date) VALUES (?, ?, 'pending', CURDATE())");
        $stmt->execute([$vehicle_id, $customer_id]);
        $success = "Request submitted! An employee will be in touch.";
    }
}

$locations = $pdo->query("SELECT location_id, name FROM LOCATION")->fetchAll();

// get customer_id if logged in as customer
$customer_id = null;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    $stmt = $pdo->prepare("SELECT customer_id FROM CUSTOMER WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $row = $stmt->fetch();
    $customer_id = $row['customer_id'];
}

// build filter query
$where  = [];
$params = [];

if (!empty($_GET['location_id'])) {
    $where[] = "v.location_id = ?";
    $params[] = $_GET['location_id'];
}

if (!empty($_GET['status'])) {
    $where[] = "v.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['deliver']) && $_GET['deliver'] !== '') {
    $where[] = "v.deliver = ?";
    $params[] = $_GET['deliver'];
}

$sql = "SELECT v.*, l.name AS location_name FROM VEHICLE v JOIN LOCATION l ON v.location_id = l.location_id";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Inventory - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Vehicle Inventory</h2>

  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert-success'>$success</div>"; ?>

  <form method="GET" class="filter-form">
    <div>
      <label>Location</label>
      <select name="location_id">
        <option value="">All Locations</option>
        <?php foreach ($locations as $loc): ?>
          <option value="<?php echo $loc['location_id']; ?>" <?php echo ($_GET['location_id'] ?? '') == $loc['location_id'] ? 'selected' : ''; ?>><?php echo $loc['name']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Status</label>
      <select name="status">
        <option value="">All</option>
        <option value="available"   <?php echo ($_GET['status'] ?? '') == 'available'   ? 'selected' : ''; ?>>Available</option>
        <option value="sold"        <?php echo ($_GET['status'] ?? '') == 'sold'        ? 'selected' : ''; ?>>Sold</option>
        <option value="transferred" <?php echo ($_GET['status'] ?? '') == 'transferred' ? 'selected' : ''; ?>>Transferred</option>
      </select>
    </div>
    <div>
      <label>Delivery</label>
      <select name="deliver">
        <option value="">All</option>
        <option value="1" <?php echo ($_GET['deliver'] ?? '') === '1' ? 'selected' : ''; ?>>Available</option>
        <option value="0" <?php echo ($_GET['deliver'] ?? '') === '0' ? 'selected' : ''; ?>>Not Available</option>
      </select>
    </div>
    <input type="submit" value="Filter">
    <a href="vehicles.php">Clear</a>
  </form>

  <table>
    <tr>
      <th>ID</th><th>Make</th><th>Model</th><th>Trim</th><th>Year</th>
      <th>Mileage</th><th>Price</th><th>Type</th><th>Status</th><th>Delivery</th><th>Location</th>
      <?php if ($customer_id): ?><th>Action</th><?php endif; ?>
    </tr>
    <?php foreach ($vehicles as $v): ?>
    <tr>
      <td><?php echo $v['vehicle_id']; ?></td>
      <td><?php echo $v['make']; ?></td>
      <td><?php echo $v['model']; ?></td>
      <td><?php echo $v['trim']; ?></td>
      <td><?php echo $v['year']; ?></td>
      <td><?php echo $v['mileage']; ?></td>
      <td>$<?php echo $v['price']; ?></td>
      <td><?php echo $v['type']; ?></td>
      <td><?php echo $v['status']; ?></td>
      <td><?php echo $v['deliver'] ? 'Yes' : 'No'; ?></td>
      <td><?php echo $v['location_name']; ?></td>
      <?php if ($customer_id): ?>
      <td>
        <?php if ($v['status'] === 'available'): ?>
          <form method="POST">
            <input type="hidden" name="vehicle_id" value="<?php echo $v['vehicle_id']; ?>">
            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
            <input type="submit" name="request_vehicle" value="Request to Buy">
          </form>
        <?php else: ?>
          <span style="color:#aaa;">Unavailable</span>
        <?php endif; ?>
      </td>
      <?php endif; ?>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</body>
</html>