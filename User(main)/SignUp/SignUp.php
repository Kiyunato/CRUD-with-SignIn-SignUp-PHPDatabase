<?php

// Database credentials for signup and user databases
$servername = "localhost";
$username = "Sarabia";
$password = "1234";
$dbname_signup = "signup"; // Signup database
$dbname_users = "login"; // User database

// Create connection for both databases
$conn_signup = new mysqli($servername, $username, $password, $dbname_signup);
$conn_users = new mysqli($servername, $username, $password, $dbname_users);

// Check connections
if ($conn_signup->connect_error) {
    die("Connection failed to signup database: " . $conn_signup->connect_error);
}
if ($conn_users->connect_error) {
    die("Connection failed to user database: " . $conn_users->connect_error);
}

// Process signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstname = $_POST["firstname"];
    $middlename = $_POST["middlename"]; // Added middlename field
    $lastname = $_POST["lastname"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmpassword = $_POST["confirmpassword"];

    // Input validation
    if ($password !== $confirmpassword) {
        $error = "Passwords do not match.";
    } elseif (empty($firstname) || empty($middlename) || empty($lastname) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        // Check if the username already exists in the signup database
        $stmt_signup = $conn_signup->prepare("SELECT id FROM register WHERE username = ? OR email = ?");
        $stmt_signup->bind_param("ss", $username, $email);
        $stmt_signup->execute();
        $stmt_signup->store_result();

        if ($stmt_signup->num_rows > 0) {
            // Username or Email already exists
            $error = "The username or email is already taken. Please choose a different one.";
        } else {
            // Insert data into the signup database (register table)
            $stmt_signup = $conn_signup->prepare("INSERT INTO register (firstname, middlename, lastname, username, email, password) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt_signup === false) {
                die("Error preparing the signup SQL statement: " . $conn_signup->error);
            }

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Bind the parameters and execute the insert statement
            $stmt_signup->bind_param("ssssss", $firstname, $middlename, $lastname, $username, $email, $hashedPassword);
            if ($stmt_signup->execute()) {
                // After inserting into the signup table, insert into the user table (login database)
                $stmt_users = $conn_users->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                if ($stmt_users === false) {
                    die("Error preparing the user SQL statement: " . $conn_users->error);
                }

                // Bind parameters for user table and execute
                $stmt_users->bind_param("ss", $username, $hashedPassword);
                if ($stmt_users->execute()) {
                    // Log confirmation email to a file (local simulation)
                    $log_message = "Registration successful for user: $firstname $middlename $lastname with username: $username\n";
                    $log_message .= "This would be the confirmation email sent to: $email\n\n";
                    file_put_contents("registration_log.txt", $log_message, FILE_APPEND);

                    // Display success message instead of sending an email
                    $success = "Registration successful! A confirmation message has been logged for $email.";
                } else {
                    $error = "Error inserting data into the user database: " . $stmt_users->error;
                }
                $stmt_users->close();
            } else {
                $error = "Error executing the query in the signup database: " . $stmt_signup->error;
            }

            $stmt_signup->close();
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
<title>Sign Up</title>
<style>
body {
  font-family: sans-serif;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #3f4c5c;
  margin: 0;
}

.signup-container {
  background-color: #4d6370;
  padding: 20px;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  width: 500px;
  max-width: 90%;
}

h1 {
  color: #fcdcc3;
  margin-bottom: 20px;
  text-align: center;
}

.error-message, .success-message {
  margin-bottom: 20px;
  text-align: center;
}

.error-message {
  color: lightcoral;
}

.success-message {
  color: lightgreen;
}

.form-group {
  margin-bottom: 20px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #fcdcc3;
  text-align: center; /* Align labels to the left */
}

input[type="text"],
input[type="password"],
input[type="email"] {
  width: calc(100% - 22px);
  padding: 10px 11px;
  border: 1px solid #ccc;
  background-color: #fcdcc3;
  border-radius: 3px;
  box-sizing: border-box;
  cursor: text;
  margin: 0 auto;
  display: block;
}

/* Styles for Sign Up button */
.signup-button {
  width: calc(50% - 22px);
  margin: 5px auto;
  display: block;
  padding: 10px 11px;
  border-radius: 3px;
  box-sizing: border-box;
  background-color: rgb(255, 196, 151);
  color: #3f4c5c;
  border: none;
  cursor: pointer;
  margin-top: 20px; /* Added margin for spacing */
  transition: background-color 0.5s ease;
}

.signup-button:hover {
  background-color: rgb(226, 138, 105);
}

/* Styles for Back to Login button */
.login-button {
  width: calc(50% - 22px);
  margin: 5px auto;
  display: block;
  padding: 10px 11px;
  border-radius: 3px;
  box-sizing: border-box;
  background-color: rgb(153, 204, 255); /* Different color */
  color: #3f4c5c;
  border: none;
  cursor: pointer;
  margin-top: 10px; /* Added margin for spacing */
  transition: background-color 0.5s ease;
}

.login-button:hover {
  background-color: rgb(102, 153, 255); /* Different hover color */
}

/* Media query for smaller screens */
@media (max-width: 400px) {
  .signup-container {
    width: 90%;
  }
}
</style>
</head>
<body>

<div class="signup-container">
  <h1>Create your Account</h1>

  <?php if (isset($error)): ?>
    <p class="error-message"><?php echo $error; ?></p>
  <?php endif; ?>

  <?php if (isset($success)): ?>
    <p class="success-message"><?php echo $success; ?></p>
  <?php endif; ?>

  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="form-group">
      <label for="firstname">First Name:</label>
      <input type="text" id="firstname" name="firstname" required>
    </div>
    <div class="form-group">
      <label for="middlename">Middle Name:</label> <!-- Added Middle Name -->
      <input type="text" id="middlename" name="middlename">
    </div>
    <div class="form-group">
      <label for="lastname">Last Name:</label>
      <input type="text" id="lastname" name="lastname" required>
    </div>
    <div class="form-group">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>
    </div>
    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
    </div>
    <div class="form-group">
      <label for="confirmpassword">Confirm Password:</label>
      <input type="password" id="confirmpassword" name="confirmpassword" required>
    </div>
    <input type="submit" value="Sign Up" class="signup-button">
  </form>

  <form action="/User/Login/login.php" method="get">
    <input type="submit" value="Back to Login" class="login-button">
  </form>

</div>

</body>
</html>

<?php
$conn_signup->close();
$conn_users->close();
?>
