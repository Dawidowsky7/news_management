<?php
require_once 'inc/functions.php';
require_once 'inc/auth.php';

if (!checkAuth()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

$news = getNews();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Aktualności</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-home"></i> Aktualności
        </a>
        <?php if (isAdmin()): ?>
            <a class="nav-link" href="manage_news.php">
                <i class="fas fa-newspaper"></i> Zarządzaj aktualnościami
            </a>
            <a class="nav-link" href="manage_users.php">
                <i class="fas fa-users"></i> Zarządzaj użytkownikami
            </a>
        <?php endif; ?>
        <div class="ml-auto d-flex align-items-center">
            <!-- Dropdown Avatar -->
            <div class="dropdown">
                <a href="#" class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="img/<?php echo htmlspecialchars($user['avatar'] ?? 'default-avatar.png'); ?>" alt="Avatar" class="avatar-img-navbar">
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="profile.php">
                        <i class="fas fa-user"></i> Mój profil
                    </a>
                    <a class="dropdown-item" href="login.php">
                        <i class="fas fa-sign-out-alt"></i> Wyloguj
                    </a>
                </div>
            </div>
        </div>
    </header>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
