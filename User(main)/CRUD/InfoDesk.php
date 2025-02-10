<?php
// Database connection
$host = "localhost";
$username = "Sarabia";
$password = "1234";
$database = "crud";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$firstname = $middlename = $lastname = $contactnumber = $dateofbirth = "";
$user_id = "";

// Add Record
if (isset($_POST['add'])) {
    $firstname = $_POST['firstname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $contactnumber = $_POST['contactnumber'] ?? '';
    $dateofbirth = $_POST['dateofbirth'] ?? '';

    $sql_add = "INSERT INTO information (firstname, middlename, lastname, contactnumber, dateofbirth) 
                VALUES ('$firstname', '$middlename', '$lastname', '$contactnumber', '$dateofbirth')";

    if ($conn->query($sql_add) === TRUE) {
        echo "<script>alert('Record added successfully');</script>";
    } else {
        echo "<script>alert('Error adding record: " . $conn->error . "');</script>";
    }
}

// Edit Record (Redirect to Form)
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $sql_fetch = "SELECT * FROM information WHERE id=$user_id";
    $result = $conn->query($sql_fetch);
    $row = $result->fetch_assoc();

    $firstname = $row['firstname'];
    $middlename = $row['middlename'];
    $lastname = $row['lastname'];
    $contactnumber = $row['contactnumber'];
    $dateofbirth = $row['dateofbirth'];
}

// Handle the form submission to update the record
if (isset($_POST['edit']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $firstname = $_POST['firstname'] ?? '';
    $middlename = $_POST['middlename'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $contactnumber = $_POST['contactnumber'] ?? '';
    $dateofbirth = $_POST['dateofbirth'] ?? '';

    $sql_edit = "UPDATE information SET firstname='$firstname', middlename='$middlename', lastname='$lastname', 
                 contactnumber='$contactnumber', dateofbirth='$dateofbirth' WHERE id=$user_id";

    if ($conn->query($sql_edit) === TRUE) {
        echo "<script>alert('Record updated successfully'); window.location.href='/User/CRUD/InfoDesk.php';</script>";
    } else {
        echo "<script>alert('Error updating record: " . $conn->error . "');</script>";
    }
}

// Delete (Move to Archive)
if (isset($_POST['delete']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $sql_fetch = "SELECT * FROM information WHERE id=$user_id";
    $result = $conn->query($sql_fetch);
    $row = $result->fetch_assoc();

    $firstname = $row['firstname'];
    $middlename = $row['middlename'];
    $lastname = $row['lastname'];
    $contactnumber = $row['contactnumber'];
    $dateofbirth = $row['dateofbirth'];

    $sql_archive = "INSERT INTO archive (firstname, middlename, lastname, contactnumber, dateofbirth) 
                    VALUES ('$firstname', '$middlename', '$lastname', '$contactnumber', '$dateofbirth')";

    if ($conn->query($sql_archive) === TRUE) {
        $sql_delete = "DELETE FROM information WHERE id=$user_id";
        if ($conn->query($sql_delete) === TRUE) {
            echo "<script>alert('Record archived and deleted successfully');</script>";
        } else {
            echo "<script>alert('Error deleting record: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error archiving record: " . $conn->error . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information Desk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #3d4d5c;
            color: white;
            text-align: center;
        }
        h2 {
            color: #fedbc5;
            font-size: 30px;
        }
        .container {
            width: 50%;
            margin: auto;
            margin-top: 100px;
            padding: 20px;
            background: #4d6370;
            border: solid 4px #fedbc5;
            border-radius: 15px;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: solid 4px #fedbc5;
            border-radius: 5px;
        }
        button {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background-color: #E67E22;
            color: white;
            cursor: pointer;
        }
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #fedbc5;
            color: #fedbc5;
            padding: 10px;
        }
        .menu {
            background: #4d6370;
            border: solid 4px #fedbc5;
            border-radius: 15px;
            padding: 10px;
            text-align: right;
        }
        .menu a {
            color: #fedbc5;
            text-decoration: none;
            padding: 10px;
        }
        .menu a:hover {
            background: #E67E22;
        }
        button {
        width: 90%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 5px;
        background-color: #E67E22; /* Base color matching your theme */
        color: white;
        cursor: pointer;
        font-weight: bold;
        font-size: 16px;
        }

        button:hover {
        background-color: #D35400; /* Slightly darker color on hover */
        }

        button:focus {
        outline: none; /* Removes focus outline */
        }

        button.edit {
        background-color: #3498db; /* Edit button has a unique blue color */
        }

        button.edit:hover {
        background-color: #2980b9; /* Darker blue when hovered */
        }

    </style>
    <script>
        function validatePhoneNumber(event) {
            event.target.value = event.target.value.replace(/\D/g, '');
        }
    </script>
</head>
<body>

<div class="menu">
    <a href="/User/CRUD/InfoDesk.php">Home</a>
    <a href="#">Profile</a>
    <a href="?view=table">View Table</a>
    <a href="?view=archive">View Archive</a>
    <a href="/User/Login/login.php">Logout</a>
</div>

<?php if (isset($_GET["view"]) && $_GET["view"] == "table"): ?>
    <h2>Active Records</h2>
    <table>
        <tr>
            <th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Contact</th><th>Birth Date</th><th>Actions</th>
        </tr>
        <?php 
        $sql_users = "SELECT * FROM information";
        $users = $conn->query($sql_users);
        while ($row = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $row["firstname"] ?></td><td><?= $row["middlename"] ?></td><td><?= $row["lastname"] ?></td>
                <td><?= $row["contactnumber"] ?></td><td><?= $row["dateofbirth"] ?></td>
                <td>
                    <a href="/User/CRUD/InfoDesk.php?user_id=<?= $row['id'] ?>">
                        <button type="button" class="edit">Edit</button>
                    </a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $row["id"] ?>">
                        <button type="submit" name="delete">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php elseif (isset($_GET["view"]) && $_GET["view"] == "archive"): ?>
    <h2>Archived Records</h2>
    <table>
        <tr><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Contact</th><th>Birth Date</th></tr>
        <?php 
        $sql_archived_users = "SELECT * FROM archive";
        $archived_users = $conn->query($sql_archived_users);
        while ($row = $archived_users->fetch_assoc()): ?>
            <tr>
                <td><?= $row["firstname"] ?></td><td><?= $row["middlename"] ?></td><td><?= $row["lastname"] ?></td>
                <td><?= $row["contactnumber"] ?></td><td><?= $row["dateofbirth"] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php elseif (isset($_GET["user_id"])): ?>
    <h2>Edit Information</h2>
    <div class="container">
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
            <input type="text" name="firstname" placeholder="First Name" required value="<?= $firstname ?>">
            <input type="text" name="middlename" placeholder="Middle Name" value="<?= $middlename ?>">
            <input type="text" name="lastname" placeholder="Last Name" required value="<?= $lastname ?>">
            <input type="text" name="contactnumber" placeholder="Contact Number" required oninput="validatePhoneNumber(event)" value="<?= $contactnumber ?>">
            <input type="date" name="dateofbirth" max="<?= date('Y-m-d') ?>" required value="<?= $dateofbirth ?>">
            <button type="submit" name="edit">Update</button>
        </form>
    </div>
<?php else: ?>
    <div class="container">
        <h2>Information Desk</h2>
        <form method="POST">
            <input type="text" name="firstname" placeholder="First Name" required>
            <input type="text" name="middlename" placeholder="Middle Name">
            <input type="text" name="lastname" placeholder="Last Name" required>
            <input type="text" name="contactnumber" placeholder="Contact Number" required oninput="validatePhoneNumber(event)">
            <input type="date" name="dateofbirth" max="<?= date('Y-m-d') ?>" required>
            <button type="submit" name="add">Add</button>
        </form>
    </div>
<?php endif; ?>

</body>
</html>
