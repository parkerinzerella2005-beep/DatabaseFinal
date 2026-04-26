<?php
session_start();
require '../config/database.php';
if ($_SESSION['role'] !== 'employee') { header("Location: ../index.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
  <title>Customer Report - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Customer Report</h2>

  <h3>Repeat Customers (More than 1 Purchase)</h3>
  <table>
    <tr><th>Customer</th><th>Email</th><th>Total Purchases</th><th>Total Spent</th></tr>
    <?php
    $stmt = $pdo->query("
        SELECT c.first_name, c.last_name, c.email,
               COUNT(s.sale_id) AS total_purchases, SUM(s.sale_amount) AS total_spent
        FROM CUSTOMER c JOIN SALE s ON c.customer_id = s.customer_id
        GROUP BY c.customer_id HAVING total_purchases > 1
        ORDER BY total_purchases DESC
    ");
    foreach ($stmt->fetchAll() as $row): ?>
    <tr>
      <td><?php echo $row['first_name'].' '.$row['last_name']; ?></td>
      <td><?php echo $row['email']; ?></td>
      <td><?php echo $row['total_purchases']; ?></td>
      <td>$<?php echo number_format($row['total_spent'], 2); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <hr>
  <h3>All Customers</h3>
  <table>
    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Total Purchases</th></tr>
    <?php
    $stmt = $pdo->query("
        SELECT c.first_name, c.last_name, c.email, c.phone, COUNT(s.sale_id) AS total_purchases
        FROM CUSTOMER c LEFT JOIN SALE s ON c.customer_id = s.customer_id
        GROUP BY c.customer_id
    ");
    foreach ($stmt->fetchAll() as $row): ?>
    <tr>
      <td><?php echo $row['first_name'].' '.$row['last_name']; ?></td>
      <td><?php echo $row['email']; ?></td>
      <td><?php echo $row['phone']; ?></td>
      <td><?php echo $row['total_purchases']; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
</body>
</html>