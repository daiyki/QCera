<?php
require_once 'db.php';

// same thing w the login, check if the rq method is post 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // declared and initalized the value + filtered and sanitized it to avoid unnecessary special characters
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST["address"], FILTER_SANITIZE_STRING);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // checks if the email OR username is already in the database
    $checkQuery = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        echo "Username or email already exists.";
    } else {
        
        $insertQuery = "INSERT INTO users (username, email, address, password) VALUES ('$username', '$email', '$address', '$password')";
        if (mysqli_query($conn, $insertQuery)) {
            echo "Registration successful!    Redirecting to login page...";
            header("Refresh: 5; url=login.php");
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles/forms.css">
</head>
<body>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>

        
        <input type="submit" value="Register"><br>

        <button onclick="location.href='login.php'">Log in</button>
    </form>
</body>
</html>