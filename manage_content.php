<?php
session_start();

// Redirect if the user is not logged in or is not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

require 'php/connection/db_connection.php';

// Fetch lists of diseases and plants to display
$diseases = [];
$plants = [];

$sql_diseases = "SELECT id, name FROM disease ORDER BY name";
$result_diseases = $conn->query($sql_diseases);
if ($result_diseases->num_rows > 0) {
    while($row = $result_diseases->fetch_assoc()) {
        $diseases[] = $row;
    }
}

$sql_plants = "SELECT id, name FROM plant ORDER BY name";
$result_plants = $conn->query($sql_plants);
if ($result_plants->num_rows > 0) {
    while($row = $result_plants->fetch_assoc()) {
        $plants[] = $row;
    }
}

$conn->close();

$user_role = $_SESSION['user_role'];
$page_title = "Manage Content";
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
                <h1>Manage Content</h1>
                <p>Add, delete, and manage diseases and their associated plants.</p>
            </header>

            <div class="container">
                <div class="forms-container">
                    <div class="form-card">
                        <h2>Add New Disease</h2>
                        <form action="php/process_content.php" method="POST" enctype="multipart/form-data">
                            <label for="disease_name">Disease Name:</label>
                            <input type="text" id="disease_name" name="disease_name" required>

                            <label for="disease_description">Description:</label>
                            <textarea id="disease_description" name="disease_description" rows="4" required></textarea>

                            <label for="disease_image">Image:</label>
                            <input type="file" id="disease_image" name="disease_image" accept="image/*" required>

                            <button type="submit" name="add_disease">Add Disease</button>
                        </form>
                    </div>

                    <div class="form-card">
                        <h2>Add New Plant</h2>
                        <form action="php/process_content.php" method="POST" enctype="multipart/form-data">
                            <label for="plant_name">Plant Name:</label>
                            <input type="text" id="plant_name" name="plant_name" required>

                            <label for="plant_description">Description:</label>
                            <textarea id="plant_description" name="plant_description" rows="4" required></textarea>

                            <label for="plant_image">Image:</label>
                            <input type="file" id="plant_image" name="plant_image" accept="image/*" required>

                            <button type="submit" name="add_plant">Add Plant</button>
                        </form>
                    </div>

                    <div class="form-card">
                        <h2>Link Plant to a Disease</h2>
                        <form action="php/process_content.php" method="POST">
                            <label for="link_disease_id">Select Disease:</label>
                            <select id="link_disease_id" name="disease_id" required>
                                <?php foreach ($diseases as $disease): ?>
                                    <option value="<?php echo htmlspecialchars($disease['id']); ?>">
                                        <?php echo htmlspecialchars($disease['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label for="link_plant_id">Select Plant:</label>
                            <select id="link_plant_id" name="plant_id" required>
                                <?php foreach ($plants as $plant): ?>
                                    <option value="<?php echo htmlspecialchars($plant['id']); ?>">
                                        <?php echo htmlspecialchars($plant['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" name="link_plant">Link Plant</button>
                        </form>
                    </div>
                </div>

                <div class="content-list">
                    <h2>Existing Diseases</h2>
                    <ul class="deletable-list">
                        <?php foreach ($diseases as $disease): ?>
                            <li>
                                <?php echo htmlspecialchars($disease['name']); ?>
                                <form action="php/process_content.php" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this disease?');">
                                    <input type="hidden" name="disease_id" value="<?php echo htmlspecialchars($disease['id']); ?>">
                                    <button type="submit" name="delete_disease" class="delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <h2>Existing Plants</h2>
                    <ul class="deletable-list">
                        <?php foreach ($plants as $plant): ?>
                            <li>
                                <?php echo htmlspecialchars($plant['name']); ?>
                                <form action="php/process_content.php" method="POST" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this plant?');">
                                    <input type="hidden" name="plant_id" value="<?php echo htmlspecialchars($plant['id']); ?>">
                                    <button type="submit" name="delete_plant" class="delete-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <script src="js/navbar.js"></script>
    <script src="js/popMessages.js"></script>
</body>
</html>