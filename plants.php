<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
// Define the $user_role variable here
$user_role = $_SESSION['user_role'];
require 'php/connection/db_connection.php';

$plants = [];
$doctors = []; // Initialize doctors array
$page_title = "Our Plants Gallery";
$header_text = "Browse through our collection of plants and their associated diseases.";

// Check if a disease ID is provided in the URL
if (isset($_GET['disease_id'])) {
    $disease_id = $_GET['disease_id'];

    // Fetch the disease name to display
    $stmt_disease = $conn->prepare("SELECT name FROM disease WHERE id = ?");
    if ($stmt_disease) {
        $stmt_disease->bind_param("i", $disease_id);
        $stmt_disease->execute();
        $result_disease = $stmt_disease->get_result();
        $disease_info = $result_disease->fetch_assoc();
        $stmt_disease->close();

        if ($disease_info) {
            $page_title = "Plants for: " . htmlspecialchars($disease_info['name']);
            $header_text = "Plants associated with " . htmlspecialchars($disease_info['name']) . ".";

            // Query to get plants linked to a specific disease
            $sql = "
                SELECT 
                    p.id AS plant_id, 
                    p.name AS plant_name, 
                    p.description AS plant_description, 
                    p.image_url AS plant_image,
                    GROUP_CONCAT(d.name SEPARATOR ', ') AS diseases
                FROM plant p
                JOIN disease_plant dp ON p.id = dp.plant_id
                JOIN disease d ON dp.disease_id = d.id
                WHERE d.id = ?
                GROUP BY p.id
                ORDER BY p.name ASC
            ";

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $disease_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $plants[] = $row;
                    }
                }
                $stmt->close();
            }
        }
    }

    // Corrected query to get specialized doctors
    $sql_doctors = "
        SELECT 
            u.id AS doctor_id, 
            u.name AS doctor_name, 
            u.email AS doctor_email,
            u.phone AS doctor_phone,
            GROUP_CONCAT(d.name SEPARATOR ', ') AS specialties
        FROM user u
        JOIN doctor_disease dd ON u.id = dd.user_id
        JOIN disease d ON dd.disease_id = d.id
        WHERE dd.disease_id = ? AND u.role = 'doctor'
        GROUP BY u.id
    ";
    
    $stmt_doctors = $conn->prepare($sql_doctors);

    if ($stmt_doctors) { 
        $stmt_doctors->bind_param("i", $disease_id);
        $stmt_doctors->execute();
        $result_doctors = $stmt_doctors->get_result();

        if ($result_doctors && $result_doctors->num_rows > 0) {
            while ($row_doctor = $result_doctors->fetch_assoc()) {
                $doctors[] = $row_doctor;
            }
        }
        $stmt_doctors->close();
    }

} else {
    // Original query to fetch all plants
    $sql = "
        SELECT 
            p.id AS plant_id, 
            p.name AS plant_name, 
            p.description AS plant_description, 
            p.image_url AS plant_image,
            GROUP_CONCAT(d.name SEPARATOR ', ') AS diseases
        FROM plant p
        LEFT JOIN disease_plant dp ON p.id = dp.plant_id
        LEFT JOIN disease d ON dp.disease_id = d.id
        GROUP BY p.id
        ORDER BY p.name ASC
    ";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $plants[] = $row;
        }
    }
    
    // If no disease ID is specified, fetch all doctors with their specialties
    $sql_all_doctors = "
        SELECT 
            u.id AS doctor_id, 
            u.name AS doctor_name, 
            u.email AS doctor_email, 
            u.phone AS doctor_phone,
            GROUP_CONCAT(d.name SEPARATOR ', ') AS specialties
        FROM user u
        LEFT JOIN doctor_disease dd ON u.id = dd.user_id
        LEFT JOIN disease d ON dd.disease_id = d.id
        WHERE u.role = 'doctor'
        GROUP BY u.id
    ";
    
    $result_all_doctors = $conn->query($sql_all_doctors);
    if ($result_all_doctors && $result_all_doctors->num_rows > 0) {
        while ($row_doctor = $result_all_doctors->fetch_assoc()) {
            $doctors[] = $row_doctor;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/Plants.css">
</head>
<body>
    <div class="app-container">
        <?php require 'includes/navbar.php'; ?>
        
        <main class="content">
            <header class="header">
                <h1><?php echo $page_title; ?></h1>
                <p><?php echo $header_text; ?></p>
                <div class="search-bar">
                    <input type="text" placeholder="Search for a plant...">
                </div>
            </header>
            
            <div class="container">
                <h1 class="cards-title">
                    Plants for: <?php echo htmlspecialchars($disease_info['name'] ?? 'All Plants'); ?>
                </h1>

                <div class="plant-cards-container">
                    <?php if (!empty($plants)): ?>
                        <?php foreach ($plants as $plant): ?>
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($plant['plant_image']); ?>" alt="<?php echo htmlspecialchars($plant['plant_name']); ?>" class="plant-image">
                                <div class="card-content-visible">
                                    <h3 class="plant-name"><?php echo htmlspecialchars($plant['plant_name']); ?></h3>
                                    <div class="card-details">
                                        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($plant['plant_description'])); ?></p>
                                        <p><strong>Diseases:</strong> <?php echo htmlspecialchars($plant['diseases'] ?? 'N/A'); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-plants">No plants found for this category.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="container doctor-section">
                <h2 class="cards-title">Specialized Doctors</h2>
                <div class="doctor-cards-container">
                    <?php if (!empty($doctors)): ?>
                        <?php foreach ($doctors as $doctor): ?>
                            <div class="doctor-card">
                                <img src="<?php echo htmlspecialchars('assets/img/default_profile_picture.png'); ?>" alt="<?php echo htmlspecialchars($doctor['doctor_name']); ?>" class="doctor-image">
                                <div class="doctor-content">
                                    <h3 class="doctor-name"><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                                    <p class="doctor-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($doctor['doctor_email']); ?></p>
                                    <p class="doctor-phone"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor['doctor_phone'] ?? 'N/A'); ?></p>
                                    <p class="doctor-specialty"><strong>Specialties:</strong> <?php echo htmlspecialchars($doctor['specialties'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-doctors">No specialized doctors found for this category.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="js/navbar.js"></script>
</body>
</html>