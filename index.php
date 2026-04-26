<?php
session_start();
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>ShipMyWhip</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <h1>ShipMyWhip</h1>
  <?php if (isset($_SESSION['user'])): ?>
    <span>Welcome, <?php echo $_SESSION['user']; ?> (<?php echo $_SESSION['role']; ?>)</span>
  <?php endif; ?>
</header>

<?php if (isset($_SESSION['user'])): ?>
<nav>
  <a href="pages/vehicles.php">Inventory</a>
  <?php if ($_SESSION['role'] === 'employee'): ?>
    <a href="pages/requests.php">Purchase Requests</a>
    <a href="pages/add_vehicle.php">Add Vehicle</a>
    <a href="pages/add_sale.php">New Sale</a>
    <a href="pages/add_customer.php">Add Customer</a>
    <a href="pages/transfers.php">Transfers</a>
    <a href="pages/edit_vehicle.php">Edit/Delete</a>
    <a href="pages/report_sales.php">Sales Report</a>
    <a href="pages/report_employee.php">Employee Report</a>
    <a href="pages/report_customers.php">Customer Report</a>
  <?php else: ?>
    <a href="pages/my_purchases.php">My Purchases</a>
  <?php endif; ?>
</nav>
<?php endif; ?>

<div class="container">
  <?php if (isset($_SESSION['user'])): ?>
    <h2>Welcome back, <?php echo $_SESSION['user']; ?>!</h2>
    <form method="POST" style="background:none; border:none; padding:0;">
      <input type="submit" name="logout" value="Logout" class="danger">
    </form>
  <?php else: ?>
    <h2>Welcome to ShipMyWhip</h2>
    <p style="margin-bottom:15px;">Louisiana's statewide car dealership.</p>
    <a href="authentication/login.php"><input type="button" value="Login"></a>
  <?php endif; ?>
</div>

</body>
</html>