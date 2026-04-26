<?php
session_start();
require '../config/database.php';

if ($_SESSION['role'] !== 'customer') { header("Location: ../index.php"); exit(); }

// get customer_id of logged in customer
$stmt = $pdo->prepare("SELECT customer_id FROM CUSTOMER WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$customer = $stmt->fetch();
$customer_id = $customer['customer_id'];

// get their purchases
$stmt = $pdo->prepare("
    SELECT s.*, v.make, v.model, v.year, v.trim,
           e.first_name AS emp_first, e.last_name AS emp_last,
           l.name AS location_name
    FROM SALE s
    JOIN VEHICLE v ON s.vehicle_id = v.vehicle_id
    JOIN EMPLOYEE e ON s.employee_id = e.employee_id
    JOIN LOCATION l ON s.location_id = l.location_id
    WHERE s.customer_id = ?
    ORDER BY s.sale_date DESC
");
$stmt->execute([$customer_id]);
$purchases = $stmt->fetchAll();

// get their pending requests
$stmt = $pdo->prepare("
    SELECT r.*, v.make, v.model, v.year
    FROM REQUEST r
    JOIN VEHICLE v ON r.vehicle_id = v.vehicle_id
    WHERE r.customer_id = ?
    ORDER BY r.request_date DESC
");
$stmt->execute([$customer_id]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Purchases - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>My Purchases</h2>

  <?php if (count($purchases) === 0): ?>
    <p style="color:#888;">You have no purchases yet.</p>
  <?php else: ?>
  <table>
    <tr>
      <th>Vehicle</th>
      <th>Location</th>
      <th>Sale Date</th>
      <th>Amount</th>
      <th>Delivery</th>
      <th>Handled By</th>
    </tr>
    <?php foreach ($purchases as $p): ?>
    <tr>
      <td><?php echo $p['year'].' '.$p['make'].' '.$p['model'].' '.$p['trim']; ?></td>
      <td><?php echo $p['location_name']; ?></td>
      <td><?php echo $p['sale_date']; ?></td>
      <td>$<?php echo number_format($p['sale_amount'], 2); ?></td>
      <td><?php echo $p['delivery_used'] ? 'Yes' : 'No'; ?></td>
      <td><?php echo $p['emp_first'].' '.$p['emp_last']; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <hr>
  <h3>My Requests</h3>

  <?php if (count($requests) === 0): ?>
    <p style="color:#888;">You have no pending requests.</p>
  <?php else: ?>
  <table>
    <tr>
      <th>Vehicle</th>
      <th>Date Requested</th>
      <th>Status</th>
    </tr>
    <?php foreach ($requests as $r): ?>
    <tr>
      <td><?php echo $r['year'].' '.$r['make'].' '.$r['model']; ?></td>
      <td><?php echo $r['request_date']; ?></td>
      <td><?php echo ucfirst($r['status']); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
</body>
</html>
