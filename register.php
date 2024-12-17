<?php 
require "db.php";
session_start();
//handle the registration
if (isset($_POST['register'])) {
    //get the variables 
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $shipping_address = htmlspecialchars($_POST['shipping_address']);

    //check for existing user in sql
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM customer WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    //disallow existing usernames or emails
    if ($existingUser) {
        echo '<p style="color:red">Username or email already taken.</p>';
    } else {
        // hash the password, this is used with php's hashing.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // insert the new customer and their hashed password into database.
        $stmt = $conn->prepare("INSERT INTO customer (username, password_hash, first_name, last_name, email, shipping_address) 
                                VALUES (:username, :password_hash, :first_name, :last_name, :email, :shipping_address)");
        $stmt->execute([
            'username' => $username,
            'password_hash' => $password_hash,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'shipping_address' => $shipping_address
        ]);

        // redirect user back to login when they successfully register.
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>

<h2>Create an Account</h2>
<form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>

    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" required><br><br>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="shipping_address">Shipping Address:</label>
    <textarea id="shipping_address" name="shipping_address" required></textarea><br><br>

    <button type="submit" name="register">Register</button>
</form>
<!-- allow the user to log back in with a button-->
<p>Already have an account? <a href="login.php" class="login-button">Login here</a></p>
</body>
</html>


