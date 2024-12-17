<?php
// Check if the form is submitted
if (isset($_POST["action"])) {
    $action = $_POST["action"];
    switch ($action) {
        case "remove":
            echo "You clicked REMOVE for question " . $_POST['q_id'];
            break;
        case "update":
            echo "You clicked UPDATE for question " . $_POST['q_id'];
            break;
        default:
            echo "The action $action has no processing code yet";
            break;
    }
} else {
?>
<!DOCTYPE html>
<html>
<body>
<p>Q1: The pace of this course</p>
<form method="post">
        <input type="hidden" name="q_id" value="1">
        <button type="submit" name="action" value="remove">Remove</button>
        <button type="submit" name="action" value="update">Update</button>
    </form>
<p>Q2: The feedback from the homework assignment grading </p>
<form method="post">
        <input type="hidden" name="q_id" value="2">
        <button type="submit" name="action" value="remove">Remove</button>
        <button type="submit" name="action" value="update">Update</button>
    </form>
<p>Q3: Anything you like about the teaching of this course </p>
<form method="post">
        <input type="hidden" name="q_id" value="3">
        <button type="submit" name="action" value="remove">Remove</button>
        <button type="submit" name="action" value="update">Update</button>
    </form>
</body>
</html>
<?php
}
?>