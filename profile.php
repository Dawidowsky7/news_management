<?php
require_once 'inc/functions.php';
require_once 'inc/auth.php';

if (!checkAuth()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if (!$user) {
    die('Użytkownik nie znaleziony.');
}

$feedback = handleProfileUpdate($userId);

$modalShown = $_SESSION['modal_shown'] ?? false;
if (!$modalShown) {
    $_SESSION['modal_shown'] = true;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Twój profil</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="container profile-container">
        <h1 class="profile-title">Twój profil</h1>

        <div class="profile-content">
            <!-- Avatar Section -->
            <div class="avatar-section">
                <img src="img/<?php echo htmlspecialchars($user['avatar'] ?? 'default-avatar.png'); ?>" alt="Avatar" class="avatar-img">
                <form method="POST" action="profile.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <h2 class="mt-3">Cześć! <?php echo htmlspecialchars($user['first_name']); ?></h2>
                        <input type="file" class="form-control-file" id="avatar" name="avatar">
                    </div>
                    <button type="submit" name="update_avatar" class="btn btn-primary">Zaktualizuj avatar</button>
                </form>
            </div>

            <!-- Password Reset Section -->
            <div class="password-section">
                <h2>Zmiana hasła</h2>
                <?php if (isset($feedback['passwordMessage'])): ?>
                    <div class="alert <?php echo $feedback['passwordMessageClass']; ?>" role="alert">
                        <?php echo htmlspecialchars($feedback['passwordMessage']); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="profile.php">
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-key"></i> Aktualne hasło
                        </label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i> Nowe hasło
                        </label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Potwierdź nowe hasło
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Zmień hasło</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Informacja</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Na tej stronie możesz zmienić swoje hasło oraz zdjęcie profilowe. Użyj formularzy poniżej, aby dokonać odpowiednich zmian.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

    <script src="js/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <script>
        $(document).ready(function() {
            <?php if (!$modalShown): ?>
                $('#infoModal').modal('show');
                <?php $_SESSION['modal_shown'] = true; ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
