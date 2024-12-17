<?php 
require "db.php"; 
session_start();
//the same as the customer registration but for employees.
if (isset($_POST['register_employee'])) {
    //get the username, email, and password from the post
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    //hash the password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $conn = connectDB();  
    //prepare sql statement
    $stmt = $conn->prepare("INSERT INTO employee (username, email, password_hash) VALUES (:username, :email, :password_hash)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password_hash', $password_hash);
    //redirect to login if successful, or say error.
    try {
        $stmt->execute();
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        echo '<p style="color:red">Error: ' . $e->getMessage() . '</p>';
    }
}
?>
<!DOCTYPE html>
<h2>Register as Employee</h2>
<form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>

    <button type="submit" name="register_employee">Register</button>
</form>
<!-- allow the user to log back into their login if they have an account-->
<p>Already registered as an employee? <a href="login.php">Login here</a></p>
</body>
</html>