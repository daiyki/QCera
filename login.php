<?php
require_once 'db.php';

session_start();

// checks if the request method is set to post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['email'] = $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $_SESSION['password'] = $password = $_POST["password"];


    // looks for the info from the database
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    // checks if its valid 
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user["password"])) {
            session_regenerate_id(true);
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            header("Location: home.php");
            exit();
        } else {
            
            echo "Invalid email or password.";
        }
    } else {
        echo "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>