<?php
include('db.php');
session_start();

//make sure the user is an employee
if (!isset($_SESSION['is_employee']) || $_SESSION['is_employee'] !== true) {
    echo 'You are not authorized to view this page.';
    exit();
}
//handle restock
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['restock_product'])) {
    restockProduct($_POST['product_id'], $_POST['restock_amount']);
}
//handle price update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_price'])) {
    updateProductPrice($_POST['product_id'], $_POST['new_price']);
}

//retrieve all the products for html.
$products = getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Store</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 8px 12px; text-align: left; border: 1px solid #ddd; }
        button { padding: 8px 12px; cursor: pointer; }
        .product-image { width: 100px; height: 100px; object-fit: cover; }
    </style>
</head>
<body>
    <h1>Employee Store</h1>

    <!-- Display the products in a table -->
    <table>
        <thead>
            <tr>
                <th>Product Image</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product) : ?>
            <tr>
                <td><img src="images/<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="product-image"></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td><?php echo $product['actual_stock']; ?></td>
                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                <td>
                    <!-- Form to restock product -->
                    <form method="POST" action="employeestore.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <label for="restock_amount_<?php echo $product['product_id']; ?>">Restock Amount:</label>
                        <input type="number" name="restock_amount" id="restock_amount_<?php echo $product['product_id']; ?>" min="1" required>
                        <button type="submit" name="restock_product">Restock</button>
                    </form>
                    
                    <!-- Form to update price -->
                    <form method="POST" action="employeestore.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <label for="new_price_<?php echo $product['product_id']; ?>">New Price:</label>
                        <input type="number" name="new_price" id="new_price_<?php echo $product['product_id']; ?>" min="0.01" step="0.01" required>
                        <button type="submit" name="update_price">Update Price</button>
                    </form>
                    
                    <!-- Button to view product history -->
                    <form method="get" action="viewProductHistory.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <button type="submit">View History</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <p>back to store <a href="store.php"><button type="button">store</button></a></p>
        <p>New Hire? <a href="employeeregister.php"><button type="button">Register as Employee</button></a></p>
        <a href="changepassword.php" class="change-password-btn">Change Password</a>

    </table>
</body>
</html>
