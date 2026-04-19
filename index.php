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
<head><title>ShipMyWhip</title></head>
<body>

<h1>ShipMyWhip</h1>

<?php if (isset($_SESSION['user'])): ?>
  <p>Welcome, <?php echo $_SESSION['user']; ?> (<?php echo $_SESSION['role']; ?>)</p>
  <form method="POST">
    <input type="submit" name="logout" value="Logout">
  </form>
<?php else: ?>
  <a href="authentication/login.php">Login</a>
<?php endif; ?>

<hr>
<?php if (isset($_SESSION['user'])): ?>
<nav>
  <a href="pages/vehicles.php">Inventory</a> |
  <a href="pages/add_vehicle.php">Add Vehicle</a> |
  <a href="pages/add_sale.php">New Sale</a> |
  <a href="pages/add_customer.php">Add Customer</a> |
  <a href="pages/transfers.php">Transfers</a> |
  <a href="pages/edit_vehicle.php">Edit/Delete</a> |
  <a href="pages/report_sales.php">Sales Report</a> |
  <a href="pages/report_employee.php">Employee Report</a> |
  <a href="pages/report_customers.php">Customer Report</a>
</nav>
<?php endif; ?>

</body>
</html>