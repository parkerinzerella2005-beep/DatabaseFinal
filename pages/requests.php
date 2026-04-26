<?php
session_start();
require '../config/database.php';
if ($_SESSION['role'] !== 'employee') { header("Location: ../index.php"); exit(); }

$error   = "";
$success = "";

// get employee_id of logged in employee
$stmt = $pdo->prepare("SELECT employee_id, location_id FROM EMPLOYEE WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$employee = $stmt->fetch();
$employee_id = $employee['employee_id'];
$location_id = $employee['location_id'];

// handle approve
if (isset($_POST['approve'])) {
    $request_id  = $_POST['request_id'];
    $vehicle_id  = $_POST['vehicle_id'];
    $customer_id = $_POST['customer_id'];
    $sale_amount = $_POST['sale_amount'];
    $delivery_used = isset($_POST['delivery_used']) ? 1 : 0;

    // assign employee to request and approve it
    $stmt = $pdo->prepare("UPDATE REQUEST SET employee_id = ?, status = 'approved' WHERE request_id = ?");
    $stmt->execute([$employee_id, $request_id]);

    // create the sale
    $stmt = $pdo->prepare("INSERT INTO SALE (vehicle_id, customer_id, employee_id, location_id, sale_date, sale_amount, delivery_used) VALUES (?, ?, ?, ?, CURDATE(), ?, ?)");
    $stmt->execute([$vehicle_id, $customer_id, $employee_id, $location_id, $sale_amount, $delivery_used]);

    // mark vehicle as sold
    $stmt = $pdo->prepare("UPDATE VEHICLE SET status = 'sold' WHERE vehicle_id = ?");
    $stmt->execute([$vehicle_id]);

    // reject all other pending requests for same vehicle
    $stmt = $pdo->prepare("UPDATE REQUEST SET status = 'rejected' WHERE vehicle_id = ? AND status = 'pending'");
    $stmt->execute([$vehicle_id]);

    $success = "Sale approved and recorded!";
}

// handle reject
if (isset($_POST['reject'])) {
    $stmt = $pdo->prepare("UPDATE REQUEST SET employee_id = ?, status = 'rejected' WHERE request_id = ?");
    $stmt->execute([$employee_id, $_POST['request_id']]);
    $success = "Request rejected.";
}

// fetch all pending requests
$requests = $pdo->query("
    SELECT r.*, v.make, v.model, v.year, v.price,
           c.first_name AS cust_first, c.last_name AS cust_last
    FROM REQUEST r
    JOIN VEHICLE v ON r.vehicle_id = v.vehicle_id
    JOIN CUSTOMER c ON r.customer_id = c.customer_id
    WHERE r.status = 'pending'
    ORDER BY r.request_date ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Purchase Requests - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Pending Purchase Requests</h2>

  <?php if ($error) echo "<div class='alert-error'>$error</div>"; ?>
  <?php if ($success) echo "<div class='alert-success'>$success</div>"; ?>

  <?php if (count($requests) === 0): ?>
    <p style="color:#888;">No pending requests at this time.</p>
  <?php else: ?>
    <?php foreach ($requests as $r): ?>
    <div style="background:white; border:1px solid #ddd; border-radius:6px; padding:20px; margin-bottom:15px;">
      <p><strong>Vehicle:</strong> <?php echo $r['year'].' '.$r['make'].' '.$r['model']; ?> — $<?php echo number_format($r['price'], 2); ?></p>
      <p><strong>Customer:</strong> <?php echo $r['cust_first'].' '.$r['cust_last']; ?></p>
      <p><strong>Requested:</strong> <?php echo $r['request_date']; ?></p>

      <form method="POST" style="background:none; border:none; padding:0; margin-top:10px;">
        <input type="hidden" name="request_id"  value="<?php echo $r['request_id']; ?>">
        <input type="hidden" name="vehicle_id"  value="<?php echo $r['vehicle_id']; ?>">
        <input type="hidden" name="customer_id" value="<?php echo $r['customer_id']; ?>">

        <label>Sale Amount</label>
        <input type="number" step="0.01" name="sale_amount" value="<?php echo $r['price']; ?>" required style="width:200px;">
        <label><input type="checkbox" name="delivery_used" style="width:auto;"> Delivery Used</label>

        <div style="display:flex; gap:10px; margin-top:10px;">
          <input type="submit" name="approve" value="Approve & Process Sale">
          <input type="submit" name="reject"  value="Reject" class="danger">
        </div>
      </form>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
</body>
</html>
