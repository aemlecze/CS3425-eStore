<?php
include 'db.php';
session_start();

// check for log in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = connectDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $user_id = $_SESSION['user_id'];
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    try {
        if (isset($_SESSION['is_employee']) && $_SESSION['is_employee'] === true) {
            $stmt = $conn->prepare("UPDATE employee SET password_hash = :password WHERE employee_id = :user_id");
            $stmt->execute([
                ':password' => $hashed_password,
                ':user_id' => $user_id
            ]);
        } else {
            $stmt = $conn->prepare("UPDATE customer SET password_hash = :password WHERE customer_id = :user_id");
            $stmt->execute([
                ':password' => $hashed_password,
                ':user_id' => $user_id
            ]);
        }
        $_SESSION['success_message'] = "Your password has been successfully updated.";
        header("Location: login.php");
        exit;

    } catch (Exception $e) {
        // Error handling
        $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<head>
</head>
<body>

<div class="container">
    <h1>Change Password</h1>

    <!-- Display success or error message -->
    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<p class="message success">' . $_SESSION['success_message'] . '</p>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<p class="message error">' . $_SESSION['error_message'] . '</p>';
        unset($_SESSION['error_message']);
    }
    ?>

    <!-- Change Password Form -->
    <form method="POST" action="changepassword.php">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>


        <button type="submit" class="submit-btn">Change Password</button>
    </form>
</div>

</body>
</html>