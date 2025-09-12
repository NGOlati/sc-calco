<?php
session_start();

// Redirection si l'utilisateur n'est pas connecté ou n'est pas un admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

require 'php/connection/db_connection.php';

// Initialisation des tableaux pour stocker les utilisateurs
$patients = [];
$doctors = [];
$admins = [];

// Récupération de tous les patients
$sql_patients = "SELECT id, name, email FROM user WHERE role = 'patient' ORDER BY name";
$result_patients = $conn->query($sql_patients);
if ($result_patients->num_rows > 0) {
    while($row = $result_patients->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Récupération de tous les docteurs
$sql_doctors = "SELECT id, name, email FROM user WHERE role = 'doctor' ORDER BY name";
$result_doctors = $conn->query($sql_doctors);
if ($result_doctors->num_rows > 0) {
    while($row = $result_doctors->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Récupération de tous les administrateurs
$sql_admins = "SELECT id, name, email FROM user WHERE role = 'admin' ORDER BY name";
$result_admins = $conn->query($sql_admins);
if ($result_admins->num_rows > 0) {
    while($row = $result_admins->fetch_assoc()) {
        $admins[] = $row;
    }
}

$conn->close();

$user_role = $_SESSION['user_role'];
$page_title = "Manage Users";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/Dashboard.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/manage_content.css">
    <style>
        /* Styles spécifiques à la gestion des utilisateurs */
        .user-list {
            list-style: none;
            padding: 0;
        }
        .user-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 5px;
        }
        .user-info {
            flex-grow: 1;
        }
        .user-info h3 {
            margin: 0;
            font-size: 1.1em;
        }
        .user-info p {
            margin: 0;
            color: #555;
            font-size: 0.9em;
        }
        .user-actions form {
            display: inline-block;
            margin-left: 10px;
        }
        .user-actions .action-btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        .user-actions .delete-btn {
            background-color: #dc3545;
        }
        
        /* Styles pour les champs de formulaire */
        .form-card input[type="text"],
        .form-card input[type="email"],
        .form-card input[type="password"],
        .form-card select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* S'assure que le padding n'augmente pas la taille */
        }
    </style>
</head>
<body>

    <div id="popup-message" class="popup">
        <div class="popup-content">
            <span class="close-btn">&times;</span>
            <p id="popup-text"></p>
        </div>
    </div>

    <div class="app-container">
        <?php require 'includes/navbar.php'; ?>

        <main class="content">
            <header class="header">
                <h1>Manage Users</h1>
                <p>View, modify, and delete user accounts.</p>
            </header>

            <div class="container">
                <div class="forms-container">
                    <div class="form-card">
                        <h2>Add New User</h2>
                        <form action="php/process_users.php" method="POST">
                            <label for="new_user_name">Full Name:</label>
                            <input type="text" id="new_user_name" name="name" required>

                            <label for="new_user_email">Email:</label>
                            <input type="email" id="new_user_email" name="email" required>

                            <label for="new_user_password">Password:</label>
                            <input type="password" id="new_user_password" name="password" required>

                            <label for="new_user_role">Role:</label>
                            <select id="new_user_role" name="role" required>
                                <option value="patient">Patient</option>
                                <option value="doctor">Doctor</option>
                                <option value="admin">Admin</option>
                            </select>

                            <button type="submit" name="add_user">Add User</button>
                        </form>
                    </div>
                </div>

                <div class="content-list">
                    <h2>Existing Admins</h2>
                    <ul class="user-list">
                        <?php if (!empty($admins)): ?>
                            <?php foreach ($admins as $admin): ?>
                                <li>
                                    <div class="user-info">
                                        <h3><?php echo htmlspecialchars($admin['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($admin['email']); ?></p>
                                    </div>
                                    <div class="user-actions">
                                        <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                            <form action="php/process_users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin? This action cannot be undone.');">
                                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($admin['id']); ?>">
                                                <button type="submit" name="delete_user" class="delete-btn">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #666; font-style: italic;">(You)</span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No admins found.</li>
                        <?php endif; ?>
                    </ul>

                    <h2>Existing Doctors</h2>
                    <ul class="user-list">
                        <?php if (!empty($doctors)): ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <li>
                                    <div class="user-info">
                                        <h3><?php echo htmlspecialchars($doctor['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($doctor['email']); ?></p>
                                    </div>
                                    <div class="user-actions">
                                        <form action="php/process_users.php" method="POST" onsubmit="return confirm('Are you sure you want to demote this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($doctor['id']); ?>">
                                            <button type="submit" name="demote_user" class="action-btn">
                                                Demote to Patient
                                            </button>
                                        </form>
                                        <form action="php/process_users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($doctor['id']); ?>">
                                            <button type="submit" name="delete_user" class="delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No doctors found.</li>
                        <?php endif; ?>
                    </ul>

                    <h2>Existing Patients</h2>
                    <ul class="user-list">
                        <?php if (!empty($patients)): ?>
                            <?php foreach ($patients as $patient): ?>
                                <li>
                                    <div class="user-info">
                                        <h3><?php echo htmlspecialchars($patient['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($patient['email']); ?></p>
                                    </div>
                                    <div class="user-actions">
                                        <form action="php/process_users.php" method="POST" onsubmit="return confirm('Are you sure you want to promote this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($patient['id']); ?>">
                                            <button type="submit" name="promote_user" class="action-btn">
                                                Promote to Doctor
                                            </button>
                                        </form>
                                        <form action="php/process_users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($patient['id']); ?>">
                                            <button type="submit" name="delete_user" class="delete-btn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>No patients found.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <script src="js/navbar.js"></script>
    <script src="js/popMessages.js"></script>
</body>
</html>