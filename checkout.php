<?php
// Include the db.php file to handle the database connection
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = connectDB();

// Fetch the user's shopping cart
$cart_query = "SELECT * FROM shoppingCart WHERE customer_id = :customer_id";
$stmt = $conn->prepare($cart_query);
$stmt->execute([':customer_id' => $user_id]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {
    // If the user has no cart, redirect to store page
    header("Location: store.php");
    exit;
}

$cart_id = $cart['cart_id'];

// Fetch the items in the cart
$item_query = "SELECT ci.cart_item_id, ci.product_id, ci.quantity, p.name, p.price, p.actual_stock 
               FROM cartItem ci
               JOIN product p ON ci.product_id = p.product_id
               WHERE ci.cart_id = :cart_id";
$stmt = $conn->prepare($item_query);
$stmt->execute([':cart_id' => $cart_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_price = 0;
$insufficient_stock = false;

// Check if there is enough stock for each item
foreach ($cart_items as $item) {
    if ($item['quantity'] > $item['actual_stock']) {
        $insufficient_stock = true;
        break;
    }
    $total_price += $item['price'] * $item['quantity'];
}

if ($insufficient_stock) {
    $_SESSION['error_message'] = "Not enough stock for one or more items in your cart.";
    header("Location: cart.php");
    exit;
}

// Create the order
$order_query = "INSERT INTO orders (customer_id, order_status, total_amount) 
                VALUES (:customer_id, 'pending', :total_amount)";
$stmt = $conn->prepare($order_query);
$stmt->execute([
    ':customer_id' => $user_id,
    ':total_amount' => $total_price
]);

$order_id = $conn->lastInsertId();

// Insert the order items
foreach ($cart_items as $item) {
    $order_item_query = "INSERT INTO orderItem (order_id, product_id, quantity, price) 
                         VALUES (:order_id, :product_id, :quantity, :price)";
    $stmt = $conn->prepare($order_item_query);
    $stmt->execute([
        ':order_id' => $order_id,
        ':product_id' => $item['product_id'],
        ':quantity' => $item['quantity'],
        ':price' => $item['price']
    ]);

    // Update product stock
    $update_stock_query = "UPDATE product SET actual_stock = actual_stock - :quantity WHERE product_id = :product_id";
    $stmt = $conn->prepare($update_stock_query);
    $stmt->execute([
        ':quantity' => $item['quantity'],
        ':product_id' => $item['product_id']
    ]);
}

// Clear the cart after successful checkout
$clear_cart_query = "DELETE FROM cartItem WHERE cart_id = :cart_id";
$stmt = $conn->prepare($clear_cart_query);
$stmt->execute([':cart_id' => $cart_id]);

// Redirect to success page
$_SESSION['order_message'] = "Your order has been successfully placed!";
header("Location: success.php");
exit;
?>