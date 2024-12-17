<?php
include 'db.php';
session_start();

//make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$conn = connectDB();
//get the id, and load data such as id, date and time, and status.
$user_id = $_SESSION['user_id'];
$sql = "SELECT order_id, order_date, order_status, total_amount 
        FROM orders 
        WHERE customer_id = :customer_id 
        ORDER BY order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([':customer_id' => $user_id]);

// retrieve the orders for html
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<body>

<header>
    <h1>Your Order History</h1>
    <a href="store.php" class="back-to-store">Back to Store</a>
</header>

<div class="container">
    <h2>Your Orders</h2>
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Total Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="order-row">
                        <td>#<?= $order['order_id'] ?></td>
                        <td><?= $order['order_date'] ?></td>
                        <td><?= $order['order_status'] ?></td>
                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>
</body>
</html>