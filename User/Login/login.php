<?php
  session_start();

  $servername = "localhost";
  $username = "Sarabia";
  $password = "1234";
  $dbname = "login";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Sanitize inputs (crucial for security, but needs improvement for production)
    $username = $conn->real_escape_string($username);
    $password = $conn->real_escape_string($password);

    // Get the hashed password from the database
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      // Verify the entered password against the hashed password in the database
      if (password_verify($password, $row['password'])) {
        $_SESSION["username"] = $username;
        header("Location: /User/CRUD/InfoDesk.php"); // Redirect to InfoDesk.php after successful login
        exit();
      } else {
        $error = "Incorrect username or password.";
      }
    } else {
      $error = "Incorrect username or password.";
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
<title>Login Page</title>
<style>
body {
  font-family: sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #3f4c5c;
  margin: 0;
}

.login-container {
  background-color: #4d6370;
  padding: 20px;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  width: 300px;
  max-width: 90%;
}

h2 {
  color: #fcdcc3;
  margin-bottom: 20px;
  text-align: center;
}

.error-message {
  color: rgb(165, 0, 0);
  margin-bottom: 20px;
  text-align: center;
}

.form-group {
  margin-bottom: 20px;
  text-align: center;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #fcdcc3;
  text-align: center;
}

input[type="text"],
input[type="password"] {
  width: calc(100% - 22px);
  padding: 10px 11px;
  border: 1px solid #ccc;
  border-radius: 3px;
  box-sizing: border-box;
  background-color: #fcdcc3;
  cursor: text;
  margin: 0 auto;
  display: block;
}

/* Styles for Login button */
input[type="submit"].login-button {
  width: 150px;
  margin: 5px auto;
  display: block;
  padding: 10px 11px;
  border-radius: 3px;
  box-sizing: border-box;
  background-color: rgb(255, 196, 151);
  color: #3f4c5c;
  border: none;
  cursor: pointer;
  transition: background-color 0.5s ease;
}

input[type="submit"].login-button:hover {
  background-color: rgb(226, 138, 105);
}


/* Styles for Sign Up button */
input[type="submit"].signup-button {
  width: 150px;
  margin: 5px auto;
  display: block;
  padding: 10px 11px;
  border-radius: 3px;
  box-sizing: border-box;
  background-color: rgb(153, 204, 255); /* Different background color */
  color: #3f4c5c;
  border: none;
  cursor: pointer;
  transition: background-color 0.5s ease;
}

input[type="submit"].signup-button:hover {
  background-color: rgb(102, 153, 255); /* Different hover color */
}

/* Media query for smaller screens */
@media (max-width: 400px) {
  .login-container {
    width: 90%;
  }
}
</style>
</head>
<body>

<div class="login-container">
  <h2>Login</h2>

  <?php if (isset($error)): ?>
    <p class="error-message"><?php echo $error; ?></p>
  <?php endif; ?>

  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="form-group">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
    </div>
    <input type="submit" value="Login" class="login-button">
  </form>

  <form action="/User/SignUp/SignUp.php" method="get">
    <input type="submit" value="Sign Up" class="signup-button">
  </form>
</div>

</body>
</html>