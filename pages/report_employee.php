<?php
session_start();
require '../config/database.php';

// fetch all employee sales for chart
$stmt = $pdo->query("
    SELECT e.first_name, e.last_name, l.name AS location_name,
           COUNT(s.sale_id) AS total_sales, SUM(s.sale_amount) AS total_amount
    FROM EMPLOYEE e
    LEFT JOIN SALE s ON e.employee_id = s.employee_id
    LEFT JOIN LOCATION l ON e.location_id = l.location_id
    GROUP BY e.employee_id ORDER BY total_sales DESC
");
$employeeData = $stmt->fetchAll();

$empLabels  = [];
$empSales   = [];
foreach ($employeeData as $row) {
    $empLabels[] = $row['first_name'].' '.$row['last_name'];
    $empSales[]  = $row['total_sales'];
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Employee Report - ShipMyWhip</title>
  <link rel="stylesheet" href="../style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
<header><h1>ShipMyWhip</h1><a href="../index.php">Back to Home</a></header>
<div class="container">
  <h2>Employee Report</h2>

  <h3>Employee of the Month</h3>
  <form method="GET" class="filter-form">
    <div><label>Month</label><input type="month" name="month" value="<?php echo $_GET['month'] ?? ''; ?>"></div>
    <input type="submit" value="Search">
  </form>

  <?php if (isset($_GET['month'])): ?>
    <?php
    $stmt = $pdo->prepare("
        SELECT e.first_name, e.last_name, COUNT(s.sale_id) AS total_sales, SUM(s.sale_amount) AS total_amount
        FROM SALE s JOIN EMPLOYEE e ON s.employee_id = e.employee_id
        WHERE DATE_FORMAT(s.sale_date, '%Y-%m') = ?
        GROUP BY e.employee_id ORDER BY total_sales DESC LIMIT 1
    ");
    $stmt->execute([$_GET['month']]);
    $top = $stmt->fetch();
    ?>
    <?php if ($top): ?>
      <div class="alert-success">
        <strong>Top Employee: <?php echo $top['first_name'].' '.$top['last_name']; ?></strong><br>
        Sales: <?php echo $top['total_sales']; ?> &nbsp;|&nbsp;
        Total: $<?php echo number_format($top['total_amount'], 2); ?>
      </div>
    <?php else: ?>
      <div class="alert-error">No sales found for that month.</div>
    <?php endif; ?>
  <?php endif; ?>

  <hr>
  <h3>Sales per Employee</h3>

  <?php if (count($employeeData) > 0): ?>
  <div style="background:white; padding:20px; border-radius:6px; border:1px solid #ddd; margin-bottom:20px;">
    <canvas id="empChart" height="100"></canvas>
  </div>
  <?php endif; ?>

  <table>
    <tr><th>Employee</th><th>Location</th><th>Total Sales</th><th>Total Amount</th></tr>
    <?php foreach ($employeeData as $row): ?>
    <tr>
      <td><?php echo $row['first_name'].' '.$row['last_name']; ?></td>
      <td><?php echo $row['location_name']; ?></td>
      <td><?php echo $row['total_sales']; ?></td>
      <td>$<?php echo number_format($row['total_amount'] ?? 0, 2); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<script>
const empLabels = <?php echo json_encode($empLabels); ?>;
const empSales  = <?php echo json_encode($empSales); ?>;

const ctx = document.getElementById('empChart');
if (ctx) {
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: empLabels,
      datasets: [{
        label: 'Total Sales',
        data: empSales,
        backgroundColor: '#16213e',
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
          ticks: { stepSize: 1 }
        }
      }
    }
  });
}
</script>
</body>
</html>