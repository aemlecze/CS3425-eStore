<?php
include 'db.php';
session_start();
//hard code to store the first employee, 
$conn = connectDB();
$username = 'j_smith';
$updated_stmt = $conn->prepare("SELECT password_updated FROM employee WHERE username = :username");
$updated_stmt->bindParam(':username', $username);
$updated_stmt->execute();
$result = $updated_stmt->fetch(PDO::FETCH_ASSOC);
if ($result && $result['password_updated'] == 1) {
  //done
} 
else {
$password = 'tempJohn123'; 
$passwordJohn = password_hash('tempJohn123', PASSWORD_DEFAULT);
$hashed_password = "SELECT password_hash FROM employee WHERE username = :username";
//since you start with one employee, just update the one thats in the table to the new hash so 
//password verification works.
$stmt = $conn->prepare("UPDATE employee SET employee_id = 1, password_hash = :password_hash, password_updated = 1 WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $passwordJohn);
    $stmt->execute();
    
}
//select all categories.
$sql = "SELECT * FROM category";
$stmt = $conn->prepare($sql);
$stmt->execute();

// get all categories as an array
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// select all the products.
$sql = "SELECT * FROM product";
$filter_params = [];
// Handle the category drop down.
if (isset($_GET['category_id']) && $_GET['category_id'] != 'all') {
    //if a category is selected filter products by category
    $category_id = $_GET['category_id'];
    $sql .= " WHERE category_id = :category_id";
    $filter_params[':category_id'] = $category_id;
}

// execute the stored sql statement
$stmt = $conn->prepare($sql);
$stmt->execute($filter_params);

// get all the products in an array
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// handle adding to cart.
if (isset($_POST['add_to_cart'])) {
    //get the product id, and set the default quantity to a single object
    $product_id = $_POST['product_id'];
    $quantity = 1; 

    // make sure the user is logged in, an unlogged user cannot add items to a cart
    if (isset($_SESSION['user_id'])) {
        //get the user id
        $user_id = $_SESSION['user_id'];
        try {
            // load our sql statements, and execute to find all products price and stock 
            $product_query = "SELECT price, actual_stock FROM product WHERE product_id = :product_id";
            $stmt = $conn->prepare($product_query);
            $stmt->execute([':product_id' => $product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            //as long as the product exists, and the stock is higher than the users quantity, we can add it to cart
            if ($product && $product['actual_stock'] >= $quantity) {
                // find a users potential cart
                $cart_query = "SELECT cart_id FROM shoppingCart WHERE customer_id = :customer_id";
                $stmt = $conn->prepare($cart_query);
                $stmt->execute([':customer_id' => $user_id]);
                $cart = $stmt->fetch(PDO::FETCH_ASSOC);
                //if the user has a cart we can add it to the pre existing cart
                if ($cart) {    
                    //get the cartId
                    $cart_id = $cart['cart_id'];
                    //insert the item
                    $item_query = "INSERT INTO cartItem (cart_id, product_id, quantity, price) 
                                   VALUES (:cart_id, :product_id, :quantity, :price)";
                    $stmt = $conn->prepare($item_query);
                    $stmt->execute([ 
                        ':cart_id' => $cart_id,
                        ':product_id' => $product_id,
                        ':quantity' => $quantity,
                        ':price' => $product['price']
                    ]);
                } else {
                    //if the user doesn't have a cart, create one with the customerID
                    $stmt = $conn->prepare("INSERT INTO shoppingCart (customer_id) VALUES (:customer_id)");
                    $stmt->execute([':customer_id' => $user_id]);
                    $cart_id = $conn->lastInsertId();

                    //insert the items into the new cart
                    $item_query = "INSERT INTO cartItem (cart_id, product_id, quantity, price) 
                                   VALUES (:cart_id, :product_id, :quantity, :price)";
                    $stmt = $conn->prepare($item_query);
                    $stmt->execute([ 
                        ':cart_id' => $cart_id,
                        ':product_id' => $product_id,
                        ':quantity' => $quantity,
                        ':price' => $product['price']
                    ]);
                }

                // set session message for the user
                $_SESSION['cart_message'] = "Item added to cart!";
            } else {
                // set session message for the user when there isn't enough stock
                $_SESSION['cart_error'] = "Sorry, not enough stock for this item.";
            }
        } catch (Exception $e) {
            //if there are any error it will print, but this was mainly for debugging
            error_log($e->getMessage());
            $_SESSION['cart_error'] = "An error occurred. Please try again later.";
        }
    } else {
        // if the user isn't logged in print the session variable
        $_SESSION['cart_error'] = "You must be logged in to add items to the cart.";
    }
    //load back into store with the new items   
    header("Location: store.php");
    exit;
}
//print the potential session messages.
$cart_message = isset($_SESSION['cart_message']) ? $_SESSION['cart_message'] : null;
$cart_error = isset($_SESSION['cart_error']) ? $_SESSION['cart_error'] : null;
//clear the messages for later use
if ($cart_message) { 
    unset($_SESSION['cart_message']);
}
if ($cart_error) {
    unset($_SESSION['cart_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop</title>
    <link rel="stylesheet" href="style.css"> 
    <style>
        body {
            background-color: grey;
        }
        header {
            background-color: lightgreen;
            padding: 18px;
            border-left: 10px solid darkgreen;
            border-right: 10px solid darkgreen;
            text-align: center;
        }
        .cart-message {
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome to Our Online Shop</h1>
    <!--category dropdown menu, uses php to loop through the categories and their ids.-->
    <form method="get" action="">
        <label for="category">Filter by Category:</label>
        <select name="category_id" id="category" onchange="this.form.submit()">
            <option value="all">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category_id'] ?>" <?= (isset($_GET['category_id']) && $_GET['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                    <?= $category['name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
            <!-- determine if the user is logged in-->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-info">
    <!-- display the users name-->
            <span>Hello, <?= $_SESSION['username'] ?> <!-- Assuming 'username' is stored in session -->
        </div>
        <!-- If logged in, show logout button -->
        <form action="logout.php" method="post">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
        <!-- show the cart button that sends you to your cart, and a change password button that sends you to changepassword if the user is logged in-->
        <a href="cart.php" class="view-cart-btn">View Cart</a>
        <a href="changepassword.php" class="change-password-btn">Change Password</a>
        <a href="success.php" class="view-checkout-history">check order history</a>;
    <?php else: ?>
        <!-- If not logged in, show login button -->
        <a href="login.php" class="login-btn">Login</a>
    <?php endif; ?>
</header>

<div class="container">
    <?php
    if ($cart_message) {
        echo '<p class="cart-message">' . $cart_message . '</p>';
    }
    if ($cart_error) {
        echo '<p class="cart-error">' . $cart_error . '</p>';
    }

    // Check if there are any products in the database
    if (count($products) > 0) {
        // Loop through each product and display it
        foreach ($products as $product) {
            echo '<div class="product-card">
                    <div class="product-image">
                        <img src="' . $product["image_url"] . '" alt="' . $product["name"] . '">
                    </div>
                    <div class="product-name">' . $product["name"] . '</div>
                    <div class="product-description">' . $product["description"] . '</div>
                    <div class="product-price">$' . number_format($product["price"], 2) . '</div>
                <div class="product-stock">Stock: ' . number_format($product["actual_stock"]) . ' units</div>';

            echo '<!-- Add to Cart Form -->
                    <form method="post" action="' . htmlentities($_SERVER['PHP_SELF']) . '">
                        <input type="hidden" name="product_id" value="' . $product['product_id'] . '">
                        <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
                    </form>
                  </div>';
        }
    } else {
        echo "<p>No products found</p>";
    }
    ?>
</div>

</body>
</html>