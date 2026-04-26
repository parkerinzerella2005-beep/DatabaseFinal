<?php
session_start();
require '../config/database.php';

// fetch sales by location for chart
$stmt = $pdo->query("
    SELECT l.name, COUNT(s.sale_id) AS total_sales, SUM(s.sale_amount) AS total_amount
    FROM SALE s JOIN LOCATION l ON s.location_id = l.location_id
    GROUP BY l.location_id
");
$locationData = $stmt->fetchAll();

// build arrays for chart
$labels  = [];
$amounts = [];
foreach ($locationData as $row) {
    $labels[]  = $row['name'];
    $amounts[] = $row['total_amount'];
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Sales Report - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Sales Report</h2>

  <h3>Total Sales by Location</h3>

  <!-- Chart -->
  <?php if (count($locationData) > 0): ?>
  <div style="background:white; padding:20px; border-radius:6px; border:1px solid #ddd; margin-bottom:20px;">
    <canvas id="salesChart" height="100"></canvas>
  </div>
  <?php endif; ?>

  <!-- Table -->
  <table>
    <tr><th>Location</th><th>Total Sales</th><th>Total Amount</th></tr>
    <?php foreach ($locationData as $row): ?>
    <tr>
      <td><?php echo $row['name']; ?></td>
      <td><?php echo $row['total_sales']; ?></td>
      <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <hr>
  <h3>Sales by Time Period</h3>
  <form method="GET" class="filter-form">
    <div><label>From</label><input type="date" name="from" value="<?php echo $_GET['from'] ?? ''; ?>"></div>
    <div><label>To</label><input type="date" name="to" value="<?php echo $_GET['to'] ?? ''; ?>"></div>
    <input type="submit" value="Filter">
  </form>

  <?php if (isset($_GET['from']) && isset($_GET['to'])): ?>
  <table>
    <tr><th>Sale ID</th><th>Vehicle</th><th>Customer</th><th>Employee</th><th>Date</th><th>Amount</th><th>Delivery</th></tr>
    <?php
    $stmt = $pdo->prepare("
        SELECT s.*, v.make, v.model, v.year,
               c.first_name AS cust_first, c.last_name AS cust_last,
               e.first_name AS emp_first, e.last_name AS emp_last
        FROM SALE s
        JOIN VEHICLE v ON s.vehicle_id = v.vehicle_id
        JOIN CUSTOMER c ON s.customer_id = c.customer_id
        JOIN EMPLOYEE e ON s.employee_id = e.employee_id
        WHERE s.sale_date BETWEEN ? AND ?
    ");
    $stmt->execute([$_GET['from'], $_GET['to']]);
    foreach ($stmt->fetchAll() as $row): ?>
    <tr>
      <td><?php echo $row['sale_id']; ?></td>
      <td><?php echo $row['year'].' '.$row['make'].' '.$row['model']; ?></td>
      <td><?php echo $row['cust_first'].' '.$row['cust_last']; ?></td>
      <td><?php echo $row['emp_first'].' '.$row['emp_last']; ?></td>
      <td><?php echo $row['sale_date']; ?></td>
      <td>$<?php echo number_format($row['sale_amount'], 2); ?></td>
      <td><?php echo $row['delivery_used'] ? 'Yes' : 'No'; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<script>
const labels  = <?php echo json_encode($labels); ?>;
const amounts = <?php echo json_encode($amounts); ?>;

const ctx = document.getElementById('salesChart');
if (ctx) {
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Total Sales Amount ($)',
        data: amounts,
        backgroundColor: '#1a1a2e',
        borderRadius: 4,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function(value) { return '$' + value.toLocaleString(); }
          }
        }
      }
    }
  });
}
</script>
</body>
</html>