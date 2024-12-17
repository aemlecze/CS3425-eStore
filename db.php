<?php
//this was used in the lab
function connectDB()
{
$config = parse_ini_file("/local/my_web_files/aemlecze/db.ini");
$dbh = new PDO($config['dsn'], $config['username'], $config['password']);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
return $dbh;
}

//authenticate the user for login
function authenticate($username, $password) {
    $conn = connectDB();
    //it needs to authenticate employees and customers, so first get data from employees.
    $stmt = $conn->prepare("SELECT employee_id, password_hash FROM employee WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    // use the password_verify to verify the password_hash.
    if ($employee && password_verify($password, $employee['password_hash'])) {
        // if the employee exists, and the password is verified, return employee data, and give employee access.
        return ['user_id' => $employee['employee_id'], 'is_employee' => true];
    }

    // otherwise, see if it is a potential customer.
    $stmt = $conn->prepare("SELECT customer_id, password_hash FROM customer WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($customer && password_verify($password, $customer['password_hash'])) {
        // return the customer data
        return ['user_id' => $customer['customer_id'], 'is_employee' => false];
    }

    // otherwise nothing was found, so return false.
    return false;
}


//update our product with the trigger
function updateProductPrice($product_id, $new_price) {
    $conn = connectDB(); //update the price, sql handles the logic and this was done in the first lab
    $username = $_SESSION['username']; // Assuming username is stored in the session

    $sql = "SELECT employee_id FROM employee WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $username, PDO::PARAM_STR);
    $stmt->execute();

if ($stmt->rowCount() > 0) {
    // Fetch employee ID and store it in the session
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['employee_id'] = $user['employee_id'];  // Store the employee_id in the session
} else {
    // Handle the case where the employee is not found
    echo "Employee not found!";
}

    $sql = "UPDATE product SET price = $new_price WHERE product_id = $product_id";
    //error handling
    if ($conn->query($sql) === TRUE) {
        return "Product price updated successfully.";
    } else {
        return "Error updating product price: ";
    }
}


function getAllProducts() {
    $conn = connectDB(); 

    $query = "SELECT p.product_id, p.name, p.description, p.price, p.actual_stock, p.stock_threshold, p.image_url, c.name AS category_name
              FROM product p
              JOIN category c ON p.category_id = c.category_id
              ORDER BY p.product_id";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);  

    if (count($products) > 0) {
        return $products;
    } else {
        return [];
    }
}
function restockProduct($productId, $restockAmount) {
    $conn = connectDB();  
    $username = $_SESSION['username']; 

    $sql = "SELECT employee_id FROM employee WHERE username = ?";
    $conn = connectDB();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $username, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['employee_id'] = $user['employee_id']; 
    } else {
        echo "Employee not found!";
    }

    $stmt = $conn->prepare("CALL restock_product(?, ?)");
    $stmt->bindValue(1, $productId, PDO::PARAM_INT);
    $stmt->bindValue(2, $restockAmount, PDO::PARAM_INT);  
    if ($stmt->execute()) {
        echo "Product restocked successfully!";
    } else {
        echo "Error restocking product.";
    }
}
?>