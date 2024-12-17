
<?php
//this was largely taken from the lab.
require "db.php"; 
session_start();

//see if the user is already logged in and redirect them accordingly
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_employee']) && $_SESSION['is_employee'] === true) {
        header("Location: employeestore.php");
    } else {
        header("Location: store.php");
    }
    exit();
}

// handle the login
if (isset($_POST['login'])) {
    //get username and password
    $username = htmlspecialchars($_POST['username']); 
    $password = $_POST['password']; 
    //use authenticate, also from the lab, to check for the username and password
    $userData = authenticate($username, $password);
    if ($userData) {
        //store the username and user id (customer, or employee)
        $_SESSION['username'] = $username;  
        $_SESSION['user_id'] = $userData['user_id']; 
        // check for either employee or customer, and redirect accordingly
        if (isset($userData['is_employee']) && $userData['is_employee'] === true) {
            $_SESSION['is_employee'] = true; 
            header("Location: employeestore.php");
        } else {
            $_SESSION['is_employee'] = false;
            header("Location: store.php");
        }
        exit();
        //if authenticate doesnt work, print an incorrect message.
    } else {
        // Display an error message if the credentials are incorrect
        echo '<p style="color:red">Incorrect username or password</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<body>
    <form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit" name="login">Login</button>
    </form>
    <!-- let the user register-->
    <form action="register.php" method="get">
    <button type="submit">Register</button>
</body>
</html>