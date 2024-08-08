<?php
require_once 'inc/functions.php';

$departments = getDepartments();
$units = getUnits();

$error_message = '';

$response = [
    'success' => false,
    'error_message' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $employee_number = $_POST['employee_number'];
    $department = $_POST['department'];
    $unit = $_POST['unit'];
    $role = 'Employee';
    $registration_date = date('Y-m-d H:i:s');

    if (isEmployeeNumberTaken($employee_number)) {
        $response['error_message'] = 'Numer pracownika jest już zajęty.';
    } elseif (isUsernameTaken($username)) {
        $response['error_message'] = 'Nazwa użytkownika jest już zajęta.';
    } else {
        addUser($first_name, $last_name, $username, $password, $employee_number, $department, $unit, $role, $registration_date);
        $response['success'] = true;
    }
    
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<!-- Formularz rejestracji -->
<div class="container" style="background-image: url('img/background.jpg'); background-size: cover;">
    <div class="row justify-content-end">
        <div class="col-lg-5">
            <div class="card mt-5">
                <div class="text-center">
                    <img src="img/logo.png" style="width: 100px;" alt="Logo" class="logo">
                </div>
                <div class="card-body">
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    <form id="registrationForm" method="POST">
                        <div class="form-group">
                            <label for="first_name"><i class="fas fa-user"></i> Imię</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last_name"><i class="fas fa-user"></i> Nazwisko</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user"></i> Nazwa użytkownika</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Hasło</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div id="password-strength" class="mt-2"></div>
                            <span></span>
                        </div>
                        <div class="form-group">
                            <label for="employee_number"><i class="fas fa-id-badge"></i> Numer pracownika</label>
                            <input type="text" class="form-control" id="employee_number" name="employee_number" required>
                        </div>
                        <div class="form-group">
                            <label for="department"><i class="fas fa-building"></i> Dział</label>
                            <select class="form-control chosen-select" id="department" name="department" required>
                                <option value="">Wybierz dział</option>
                                <?php foreach ($departments as $department): ?>
                                    <option value="<?php echo htmlspecialchars($department['id']); ?>"><?php echo htmlspecialchars($department['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="unit"><i class="fas fa-sitemap"></i> Jednostka organizacyjna</label>
                            <select class="form-control chosen-select" id="unit" name="unit" required>
                                <option value="">Wybierz jednostkę</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo htmlspecialchars($unit['id']); ?>"><?php echo htmlspecialchars($unit['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Zarejestruj się</button>
                    </form>
                    <p class="mt-3 text-center">Masz już konto? <a href="login.php">Zaloguj się</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal potwierdzenia rejestracji -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Rejestracja zakończona</h5>
            </div>
            <div class="modal-body">
                <div class="tick-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <p>Pomyślnie zarejestrowano!</p>
                <div class="countdown">Przekierowanie za <span id="countdown">4</span> s na stronę logowania.</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal z informacją o złożoności hasła -->
<div class="modal fade" id="passwordStrengthModal" tabindex="-1" role="dialog" aria-labelledby="passwordStrengthModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordStrengthModalLabel">Złożoność hasła</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Twoje hasło musi spełniać następujące wymogi:</p>
                <ul>
                    <li id="lengthRequirement">Minimum 8 znaków</li>
                    <li id="lowercaseRequirement">Przynajmniej jedna mała litera</li>
                    <li id="uppercaseRequirement">Przynajmniej jedna wielka litera</li>
                    <li id="numberRequirement">Przynajmniej jedna cyfra</li>
                    <li id="specialCharRequirement">Przynajmniej jeden znak specjalny</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
