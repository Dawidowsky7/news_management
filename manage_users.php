<?php
require_once 'inc/functions.php';
require_once 'inc/auth.php';

if (!checkAuth()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $employeeNumber = $_POST['employee_number'] ?? '';
    $department = $_POST['department'] ?? '';
    $organizationalUnit = $_POST['organizational_unit'] ?? '';
    $role = $_POST['role'] ?? '';
    $accountCreationDate = $_POST['account_creation_date'] ?? '';
    $id = $_POST['id'] ?? null;

    if ($id) {
        updateUser($id, $username, $password, $firstName, $lastName, $employeeNumber, $department, $organizationalUnit, $role);
        header('Location: manage_users.php?user_edited=true');
        exit;
    
    } else {
        addUser($username, $password, $firstName, $lastName, $employeeNumber, $department, $organizationalUnit, $role, $accountCreationDate);
        header('Location: manage_users.php?user_added=true');
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $userId = $_GET['id'];
    deleteUser($userId);
    header('Location: manage_users.php?user_deleted=true');
    exit;
}

$user = null;
if (isset($_GET['id'])) {
    $user = getUserById($_GET['id']);
}

$filterUsername = $_GET['filter_username'] ?? '';
$filterFirstName = $_GET['filter_first_name'] ?? '';
$filterLastName = $_GET['filter_last_name'] ?? '';
$filterEmployeeNumber = $_GET['filter_employee_number'] ?? '';
$filterDepartment = $_GET['filter_department'] ?? '';
$filterOrganizationalUnit = $_GET['filter_organizational_unit'] ?? '';
$filterRole = $_GET['filter_role'] ?? '';
$filterAccountCreationDate = $_GET['filter_account_creation_date'] ?? '';

$users = getUsers($filterUsername, $filterFirstName, $filterLastName, $filterEmployeeNumber, $filterDepartment, $filterOrganizationalUnit, $filterRole, $filterAccountCreationDate);


// Pobierz listy filtrów
$departments = getDepartments(); // Funkcja do pobrania listy działów
$organizationalUnits = getUnits(); // Funkcja do pobrania jednostek organizacyjnych
$usernames = getUniqueUsernames(); // Funkcja do pobrania unikalnych nazw użytkowników
$firstNames = getUniqueFirstNames(); // Funkcja do pobrania unikalnych imion
$lastNames = getUniqueLastNames(); // Funkcja do pobrania unikalnych nazwisk
$employeeNumbers = getUniqueEmployeeNumbers(); // Funkcja do pobrania unikalnych numerów pracowników
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zarządzanie użytkownikami</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="container">
        <h1>Zarządzanie użytkownikami</h1>
        <hr>
        <h7><b>Filtruj po:</b></h7>
        <p></p>
        <form method="GET" action="manage_users.php" class="form-inline mb-4">
    <div class="row">
        <!-- Lewa strona filtrów -->
        <div class="col-md-6">
            <div class="form-group mb-2">
                <label for="filter_username" class="mr-2">Nazwa użytkownika:</label>
                <select class="form-control chosen-select" id="filter_username" name="filter_username">
                    <option value="">Wybierz</option>
                    <?php foreach ($usernames as $username): ?>
                        <option value="<?php echo htmlspecialchars($username['username'], ENT_QUOTES); ?>" <?php echo $filterUsername == $username['username'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($username['username'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="filter_first_name" class="mr-2">Imię:</label>
                <select class="form-control chosen-select" id="filter_first_name" name="filter_first_name">
                    <option value="">Wybierz</option>
                    <?php foreach ($firstNames as $firstName): ?>
                        <option value="<?php echo htmlspecialchars($firstName['first_name'], ENT_QUOTES); ?>" <?php echo $filterFirstName == $firstName['first_name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($firstName['first_name'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="filter_last_name" class="mr-2">Nazwisko:</label>
                <select class="form-control chosen-select" id="filter_last_name" name="filter_last_name">
                    <option value="">Wybierz</option>
                    <?php foreach ($lastNames as $lastName): ?>
                        <option value="<?php echo htmlspecialchars($lastName['last_name'], ENT_QUOTES); ?>" <?php echo $filterLastName == $lastName['last_name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lastName['last_name'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="filter_employee_number" class="mr-2">Numer pracownika:</label>
                <select class="form-control chosen-select" id="filter_employee_number" name="filter_employee_number">
                    <option value="">Wybierz</option>
                    <?php foreach ($employeeNumbers as $employeeNumber): ?>
                        <option value="<?php echo htmlspecialchars($employeeNumber['employee_number'], ENT_QUOTES); ?>" <?php echo $filterEmployeeNumber == $employeeNumber['employee_number'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($employeeNumber['employee_number'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2 mt-3">
                <button type="submit" class="btn btn-primary">Filtruj</button>
                <a href="manage_users.php" class="btn btn-secondary ml-2">Resetuj</a>
            </div>
        </div>

        <!-- Prawa strona filtrów -->
        <div class="col-md-6">
            <div class="form-group mb-2">
                <label for="filter_department" class="mr-2">Dział:</label>
                <select class="form-control chosen-select" id="filter_department" name="filter_department">
                    <option value="">Wybierz</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?php echo htmlspecialchars($department['id'], ENT_QUOTES); ?>" <?php echo $filterDepartment == $department['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($department['name'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="filter_organizational_unit" class="mr-2">Jednostka organizacyjna:</label>
                <select class="form-control chosen-select" id="filter_organizational_unit" name="filter_organizational_unit">
                    <option value="">Wybierz</option>
                    <?php foreach ($organizationalUnits as $unit): ?>
                        <option value="<?php echo htmlspecialchars($unit['id'], ENT_QUOTES); ?>" <?php echo $filterOrganizationalUnit == $unit['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($unit['name'], ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="filter_role" class="mr-2">Rola:</label>
                <select class="form-control chosen-select" id="filter_role" name="filter_role">
                    <option value="">Wybierz</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role, ENT_QUOTES); ?>" <?php echo $filterRole == $role ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($role, ENT_QUOTES); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-2">
                <label for="filter_account_creation_date" class="mr-2">Data założenia konta:</label>
                <input type="date" class="form-control" id="filter_account_creation_date" name="filter_account_creation_date" value="<?php echo htmlspecialchars($filterAccountCreationDate, ENT_QUOTES); ?>">
            </div>
        </div>
    </div>
</form>
        <form method="POST" action="manage_users.php">
            <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#addUserModal">
                Dodaj nowego użytkownika
            </button>
        </form>

<!-- Modal dodawania użytkownika -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Dodaj użytkownika</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addUserForm" method="POST" action="manage_users.php">
                    <div class="form-group">
                        <label for="username">Nazwa użytkownika</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Hasło</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div id="passwordStrengthContainer" class="mt-2">
                        <div id="passwordStrengthBar" class="progress">
                         <div id="passwordStrengthLevel" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                            </div>
                                    </div>
                    <div class="form-group">
                        <label for="first_name">Imię</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nazwisko</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="employee_number">Numer pracownika</label>
                        <input type="text" class="form-control" id="employee_number" name="employee_number" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Dział</label>
                        <select class="form-control chosen-select" id="department" name="department" required>
                            <option value="">Wybierz dział</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo htmlspecialchars($department['id'], ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($department['name'], ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="organizational_unit">Jednostka organizacyjna</label>
                        <select class="form-control chosen-select" id="organizational_unit" name="organizational_unit" required>
                            <option value="">Wybierz jednostkę</option>
                            <?php foreach ($organizationalUnits as $unit): ?>
                                <option value="<?php echo htmlspecialchars($unit['id'], ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($unit['name'], ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="role">Rola</label>
                        <select class="form-control chosen-select" id="role" name="role" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role, ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($role, ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Ukryte pole z datą założenia konta -->
                    <input type="hidden" id="account_creation_date" name="account_creation_date" value="<?php echo date('Y-m-d'); ?>">
                    <button type="submit" class="btn btn-primary">Dodaj użytkownika</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal wymagania hasła -->
<div class="modal fade" id="passwordRequirementsModal" tabindex="-1" role="dialog" aria-labelledby="passwordRequirementsModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordRequirementsModalLabel">Wymagania dotyczące hasła</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul id="passwordRequirements" class="list-unstyled mt-2">
                    <li>Minimum 8 znaków</li>
                    <li>Przynajmniej jedna mała litera</li>
                    <li>Przynajmniej jedna wielka litera</li>
                    <li>Przynajmniej jedna cyfra</li>
                    <li>Przynajmniej jeden znak specjalny</li>
                </ul>
            </div>
        </div>
    </div>
</div>
        <!-- Modal Potwierdzenie Dodania -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <div class="animated-checkmark">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                         <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14 27l7 7 16-16"/>
                        </svg>
                    </div>
                        <h5 class="mt-3">Użytkownik został pomyślnie dodany!</h5>
                    </div>
                </div>
            </div>
        </div>

<!-- Modal potwierdzenia usunięcia użytkownika -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Potwierdzenie usunięcia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p>Czy na pewno chcesz usunąć tego użytkownika? <br> Operacja jest nieodwracalna.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDeleteButton">Usuń</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal po usunięciu użytkownika -->
<div class="modal fade" id="deleteSuccessModal" tabindex="-1" role="dialog" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="animated-checkmark">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14 27l7 7 16-16"/>
                    </svg>
                </div>
                <h5 class="mt-3">Użytkownik został pomyślnie usunięty!</h5>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edytuj Użytkownika -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edycja użytkownika</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST" action="manage_users.php">
                    <input type="hidden" name="id" id="edit_user_id" value="">
                    <div class="form-group">
                        <label for="edit_username">Nazwa użytkownika</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_first_name">Imię</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Nazwisko</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_employee_number">Numer pracownika</label>
                        <input type="text" class="form-control" id="edit_employee_number" name="employee_number" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_department">Dział</label>
                        <select class="form-control chosen-select" id="edit_department" name="department" required>
                            <option value="">Wybierz dział</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo htmlspecialchars($department['id'], ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($department['name'], ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_organizational_unit">Jednostka organizacyjna</label>
                        <select class="form-control chosen-select" id="edit_organizational_unit" name="organizational_unit" required>
                            <option value="">Wybierz jednostkę</option>
                            <?php foreach ($organizationalUnits as $unit): ?>
                                <option value="<?php echo htmlspecialchars($unit['id'], ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($unit['name'], ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Rola</label>
                        <select class="form-control chosen-select" id="edit_role" name="role" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role, ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($role, ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal Pomyślna Edycja -->
<div class="modal fade" id="editSuccessModal" tabindex="-1" role="dialog" aria-labelledby="editSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="animated-checkmark">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark-check" fill="none" d="M14 27l7 7 16-16"/>
                    </svg>
                </div>
                <h5 class="mt-3">Użytkownik został pomyślnie edytowany!</h5>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edytuj Użytkownika -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edytuj Użytkownika</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST" action="manage_users.php">
                    <input type="hidden" name="id" id="edit_user_id" value="">
                    <div class="form-group">
                        <label for="edit_username">Nazwa użytkownika</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_first_name">Imię</label>
                        <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Nazwisko</label>
                        <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_employee_number">Numer pracownika</label>
                        <input type="text" class="form-control" id="edit_employee_number" name="employee_number" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_department">Dział</label>
                        <select class="form-control chosen-select" id="edit_department" name="department" required>
                            <option value="">Wybierz dział</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo htmlspecialchars($department['id'], ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($department['name'], ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_organizational_unit">Jednostka organizacyjna</label>
                        <select class="form-control chosen-select" id="edit_organizational_unit" name="organizational_unit" required>
                            <option value="">Wybierz jednostkę</option>
                            <?php foreach ($organizationalUnits as $unit): ?>
                                <option value="<?php echo htmlspecialchars($unit['id'], ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($unit['name'], ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Rola</label>
                        <select class="form-control chosen-select" id="edit_role" name="role" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role, ENT_QUOTES); ?>">
                                    <?php echo htmlspecialchars($role, ENT_QUOTES); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                </form>
            </div>
        </div>
    </div>
</div>
        <!-- Tabela wyświetlająca użytkowników -->
        <table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nazwa użytkownika</th>
            <th>Imię</th>
            <th>Nazwisko</th>
            <th>Numer pracownika</th>
            <th>Dział</th>
            <th>Jednostka organizacyjna</th>
            <th>Rola</th>
            <th>Data założenia konta</th>
            <th>Akcje</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id'], ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['username'] ?? '', ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['first_name'] ?? '', ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['last_name'] ?? '', ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['employee_number'] ?? '', ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['department_name'] ?? 'Brak danych', ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['unit_name'] ?? 'Brak danych', ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['role_name'] ?? 'Brak danych', ENT_QUOTES); ?></td>
                <td><?php echo htmlspecialchars($user['registration_date'] ?? 'Brak danych', ENT_QUOTES); ?></td>
                <td>
                    <div class="btn-group" role="group" aria-label="Akcje">
                    <a href="#" class="btn btn-warning btn-sm" data-id="<?php echo htmlspecialchars($user['id'], ENT_QUOTES); ?>">
                    <i class="fas fa-edit"></i> <!-- Ikona do edycji -->
                        </a>
                    <a href="manage_users.php?action=delete&id=<?php echo htmlspecialchars($user['id'], ENT_QUOTES); ?>" class="btn btn-danger btn-sm">
                     <i class="fas fa-trash-alt"></i> <!-- Ikona do usuwania -->
                        </a>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    </div>
    <script src="js/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script>
    $(document).ready(function() {
    $('.chosen-select').chosen({
        search_contains: true
    });

    <?php if (isset($_GET['user_edited']) && $_GET['user_edited'] == 'true'): ?>
        $('#editSuccessModal').modal('show');
        setTimeout(function() {
            $('#editSuccessModal').modal('hide');
        }, 3000);
    <?php endif; ?>
    
    <?php if (isset($_GET['user_added']) && $_GET['user_added'] == 'true'): ?>
        $('#confirmationModal').modal('show');
        setTimeout(function() {
            $('#confirmationModal').modal('hide');
        }, 3000);
    <?php endif; ?>

    <?php if (isset($_GET['user_deleted']) && $_GET['user_deleted'] == 'true'): ?>
        $('#deleteSuccessModal').modal('show');
        setTimeout(function() {
            $('#deleteSuccessModal').modal('hide');
        }, 3000);
    <?php endif; ?>

    $('#editUserModal').on('shown.bs.modal', function () {
    $('.chosen-select').chosen('destroy').chosen();
    });

    $('#addUserModal').on('shown.bs.modal', function () {
    $('.chosen-select').chosen('destroy').chosen();
    });

    $('a.btn-danger').on('click', function(e) {
        e.preventDefault(); 
        var url = $(this).attr('href');
        $('#deleteConfirmationModal').modal('show');
        $('#confirmDeleteButton').off('click').on('click', function() {
            window.location.href = url;
        });
    });

    $('#editSuccessModal').on('shown.bs.modal', function () {
        $('.animated-checkmark').addClass('show');
    });

    $('#editSuccessModal').on('hidden.bs.modal', function () {
        $('.animated-checkmark').removeClass('show');
    });

    $('#deleteSuccessModal').on('shown.bs.modal', function () {
        $('.animated-checkmark').addClass('show');
    });

    $('#deleteSuccessModal').on('hidden.bs.modal', function () {
        $('.animated-checkmark').removeClass('show');
    });
    
    $('a.btn-warning').on('click', function(e) {
    e.preventDefault(); 
    var userId = $(this).data('id');
    $.ajax({
        url: 'get_user.php',
        method: 'GET',
        data: { id: userId },
        success: function(response) {
            var user = JSON.parse(response);
            if (user.error) {
                alert(user.error);
            } else {
                $('#edit_user_id').val(user.id);
                $('#edit_username').val(user.username);
                $('#edit_first_name').val(user.first_name);
                $('#edit_last_name').val(user.last_name);
                $('#edit_employee_number').val(user.employee_number);
                $('#edit_department').val(user.department_id).trigger('chosen:updated');
                $('#edit_organizational_unit').val(user.unit_id).trigger('chosen:updated');
                $('#edit_role').val(user.role).trigger('chosen:updated');
                $('#editUserModal').modal('show');
            }
        },
        error: function() {
            alert('Wystąpił błąd podczas pobierania danych użytkownika.');
        }
    
    });
});

$(document).ready(function() {
    // Funkcja do analizy siły hasła
    function checkPasswordStrength(password) {
        let strength = 0;
        let errors = [];

        if (password.length > 7) strength += 1;
        else errors.push('Minimum 8 znaków');

        if (/[a-z]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jedna mała litera');

        if (/[A-Z]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jedna wielka litera');

        if (/[0-9]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jedna cyfra');

        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jeden znak specjalny');

        return { strength, errors };
    }

    function updatePasswordStrengthBar(strength) {
        let width = 0;
        let color = 'red';

        switch (strength) {
            case 5:
                width = 100;
                color = 'green';
                break;
            case 4:
                width = 80;
                color = 'lightgreen';
                break;
            case 3:
                width = 60;
                color = 'yellow';
                break;
            case 2:
                width = 40;
                color = 'orange';
                break;
            case 1:
                width = 20;
                color = 'red';
                break;
            default:
                width = 0;
                color = 'red';
                break;
        }

        $('#passwordStrengthLevel').css('width', width + '%').css('background-color', color);
    }

    $('#addUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const result = checkPasswordStrength(password);
        const { strength, errors } = result;

        if (errors.length > 0) {
            e.preventDefault(); // Zatrzymaj przesyłanie formularza

            $('#passwordRequirementsModal').find('ul').children().each(function() {
                const requirementText = $(this).text();
                if (errors.includes(requirementText)) {
                    $(this).css('color', 'red');
                } else {
                    $(this).css('color', 'green');
                }
            });

            $('#passwordRequirementsModal').modal('show');
            return;
        }

        updatePasswordStrengthBar(strength);
    });

    $('#password').on('input', function() {
        const password = $(this).val();
        const result = checkPasswordStrength(password);
        const { strength } = result;

        updatePasswordStrengthBar(strength);
    });

    $('#passwordRequirementsModal').on('hidden.bs.modal', function () {
        $('body').addClass('modal-open');
    });
});

});
</script>
</body>
</html>
