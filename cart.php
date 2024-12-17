<?php
include 'db.php';
session_start();
//check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
//get the id and retrieve their potential cart.
$user_id = $_SESSION['user_id'];
$conn = connectDB();
$cart_query = "SELECT * FROM shoppingCart WHERE customer_id = :customer_id";
$stmt = $conn->prepare($cart_query);
$stmt->execute([':customer_id' => $user_id]);
//get the cart for html
$cart = $stmt->fetch(PDO::FETCH_ASSOC);
if ($cart) {
    $cart_id = $cart['cart_id'];
} else {
    // Handle the case where no cart is found
    echo "No cart found for this user.";
    exit;  // You can redirect or handle this case as needed
}
//set up to find the item, id, quantity, from the cart item, and find the associated product.
$item_query = "SELECT c.cart_item_id, c.product_id, c.quantity, p.name, p.price, p.actual_stock 
               FROM cartItem c
               JOIN product p ON c.product_id = p.product_id
               WHERE c.cart_id = :cart_id";
//launch it based on the cart id.
$stmt = $conn->prepare($item_query);
$stmt->execute([':cart_id' => $cart_id]);
//get the cartItems for html
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
//initialize total price.
$total_price = 0;
// the quantity is able to update whilst in the cart tab.
//if the user wants to update the quantity
if (isset($_POST['update_quantity'])) {
    //get the item id, and the quantity from the post
    $cart_item_id = $_POST['cart_item_id'];
    $new_quantity = $_POST['quantity'];
    //retrieve the stock 
    $stock_query = "SELECT actual_stock FROM product WHERE product_id = 
                    (SELECT product_id FROM cartItem WHERE cart_item_id = :cart_item_id)";
    $stmt = $conn->prepare($stock_query);
    $stmt->execute([':cart_item_id' => $cart_item_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
   //if the new quantity is less than the stock, its valid, and we can proceed. 
    if ($product && $new_quantity <= $product['actual_stock']) {
        //update the stock in the database
        $update_query = "UPDATE cartItem SET quantity = :quantity WHERE cart_item_id = :cart_item_id";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([
            ':quantity' => $new_quantity,
            ':cart_item_id' => $cart_item_id
        ]);
    } else {

        // otherwise its invalid and we need to say not enough stock
        $_SESSION['error_message'] = "Not enough stock for this item.";
    }

    // send them back to cart for further action
    header("Location: cart.php");
    exit;
}

// remove items from the cart
if (isset($_POST['remove_from_cart'])) {
    //get the id, find it in the database, and remove.
    $cart_item_id = $_POST['cart_item_id'];
    $remove_query = "DELETE FROM cartItem WHERE cart_item_id = :cart_item_id";
    $stmt = $conn->prepare($remove_query);
    $stmt->execute([':cart_item_id' => $cart_item_id]);
    // send back to cart for further action
    header("Location: cart.php");
    exit;
}

// find the total price of the item, get the prive 
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Your Shopping Cart</h1>

    <a href="store.php" class="back-to-store-btn">Back to Store</a>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message" style="color: red; font-weight: bold;">
            <?= $_SESSION['error_message'] ?>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (count($cart_items) > 0): ?>
        <h2>Items in Your Cart:</h2>
        <form method="post" action="cart.php">
            <ul>
                <?php foreach ($cart_items as $item): ?>
                    <li>
                        <span><?= $item['name'] ?> - $<?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?></span>
                        
                        <!-- Quantity Update Form -->
                        <form method="post" action="cart.php" style="display:inline;">
                            <label for="quantity_<?= $item['cart_item_id'] ?>">Quantity:</label>
                            <input type="number" id="quantity_<?= $item['cart_item_id'] ?>" name="quantity" 
                                   value="<?= $item['quantity'] ?>" min="1" 
                                   max="<?= $item['actual_stock'] ?>" required>
                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                            <button type="submit" name="update_quantity">Update Quantity</button>
                        </form>

                        <!-- Remove Button -->
                        <form method="post" action="cart.php" style="display:inline;">
                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                            <button type="submit" name="remove_from_cart" class="remove-from-cart-btn">Remove</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total-price">
                <strong>Total: $<?= number_format($total_price, 2) ?></strong>
            </div>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</header>

</body>
</html>