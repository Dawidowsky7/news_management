<?php
require_once 'inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (authenticate($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Błędna nazwa użytkownika lub hasło.';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container" style="background-image: url('img/background.jpg'); background-size: cover;">
        <div class="row justify-content-end">
            <div class="col-lg-5">
                <div class="card mt-5">
                    <div class="text-center">
                        <img src="img/logo.png" style="width: 100px;" alt="Logo" class="logo">
                    </div>
                    <div class="card-body">
                        <form method="POST" action="login.php">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label for="username"><i class="fas fa-user"></i> Nazwa użytkownika</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password"><i class="fas fa-lock"></i> Hasło</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Zaloguj się</button>
                        </form>
                        <p class="mt-3 text-center">Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal o ciasteczkach -->
    <div class="modal fade" id="cookieModal" tabindex="-1" role="dialog" aria-labelledby="cookieModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cookieModalLabel">Informacja o plikach cookies</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="cookie-icon">
                        <i class="fas fa-cookie-bite"></i>
                    </div>
                    <p>Nasza strona używa plików cookies, aby zapewnić najlepsze wrażenia z przeglądania. Kontynuując przeglądanie strony, wyrażasz zgodę na używanie plików cookies.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="acceptCookies">Akceptuję</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        console.log('Document is ready');

        // Sprawdź, czy cookies są już zaakceptowane
        if (document.cookie.indexOf('cookies-accepted=true') === -1) {
            console.log('Showing cookie modal');
            $('#cookieModal').modal('show');
        }

        $('#acceptCookies').click(function() {
            console.log('Accepting cookies');
            // Ustaw cookie na 30 dni
            document.cookie = "cookies-accepted=true; max-age=" + (30*24*60*60) + "; path=/";
            $('#cookieModal').modal('hide');
        });
    });
    </script>
</body>
</html>
