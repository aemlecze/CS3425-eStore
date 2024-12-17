<?php
include('db.php');
session_start();
//check for login
if (!isset($_SESSION['is_employee']) || $_SESSION['is_employee'] !== true) {
    echo 'You are not authorized to view this page.';
    exit();
}

// get the product id from the url bye echoing it in the url on view product history action
$product_id = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

$conn = connectDB();
$stmt = $conn->prepare("SELECT action, action_timestamp, employee_id FROM productHistory WHERE product_id = :product_id ORDER BY action_timestamp DESC");
$stmt->bindParam(':product_id', $product_id);
$stmt->execute();
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<body>

<h1>Product History</h1>

<table>
    <thead>
        <tr>
            <th>Action</th>
            <th>Timestamp</th>
            <th>Employee ID</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($history as $entry): ?>
        <tr>
            <td><?php echo htmlspecialchars($entry['action']); ?></td>
            <td><?php echo htmlspecialchars($entry['action_timestamp']); ?></td>
            <td><?php echo htmlspecialchars($entry['employee_id']); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Button to go back to the store -->
<a href="employeestore.php" class="back-button">Back to Employee Store</a>

</body>
</html>