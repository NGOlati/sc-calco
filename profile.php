<?php
session_start();

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
// Define the $user_role variable here
$user_role = $_SESSION['user_role'];

require 'php/connection/db_connection.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? null;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    // Retrieve the current hashed password for verification
    $stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    $stmt->close();
    $hashed_password_from_db = $user_data['password'];

    // Check if the user wants to change their password
    if (!empty($new_password)) {
        // Verify if the current password is correct
        if (!password_verify($current_password, $hashed_password_from_db)) {
            $message = "<div class='alert error'>Incorrect current password.</div>";
        } else {
            // Update the password with a new hash
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE user SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssssi", $name, $email, $phone, $hashed_new_password, $user_id);
            if ($update_stmt->execute()) {
                $message = "<div class='alert success'>Profile and password updated successfully!</div>";
                // Update the session with the new name if necessary
                $_SESSION['user_name'] = $name;
            } else {
                $message = "<div class='alert error'>Error updating profile: " . $conn->error . "</div>";
            }
            $update_stmt->close();
        }
    } else {
        // Update information without changing the password
        $update_stmt = $conn->prepare("UPDATE user SET name = ?, email = ?, phone = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        if ($update_stmt->execute()) {
            $message = "<div class='alert success'>Profile updated successfully!</div>";
            $_SESSION['user_name'] = $name;
        } else {
            $message = "<div class='alert error'>Error updating profile: " . $conn->error . "</div>";
        }
        $update_stmt->close();
    }
}

// Retrieve user information to pre-fill the form
$stmt = $conn->prepare("SELECT name, email, phone, role FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    // Handle the case where the user is not found in the database
    session_destroy();
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-header h1 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .password-fields {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .btn-update {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-update:hover {
            background-color: #45a049;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <?php require 'includes/navbar.php'; ?>
        
        <main class="content">
            <div class="profile-container">
                <div class="profile-header">
                    <h1>Manage My Profile</h1>
                    <p>Update your personal information.</p>
                </div>

                <?php echo $message; ?>

                <form action="profile.php" method="POST">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>

                    <div class="password-fields">
                        <p>To change your password, fill out the fields below.</p>
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                    </div>

                    <button type="submit" class="btn-update">Update Profile</button>
                </form>
            </div>
        </main>
    </div>
    <script src="js/navbar.js"></script>
</body>
</html>