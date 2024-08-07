<?php
require_once 'db.php';

function addNews($title, $content, $photo = null) {
    global $pdo;
    $sql = "INSERT INTO news (title, content, photo) VALUES (:title, :content, :photo)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['title' => $title, 'content' => $content, 'photo' => $photo]);
}


function updateNews($id, $title, $content, $photo = null) {
    global $pdo;
    $sql = "UPDATE news SET title = :title, content = :content, photo = :photo WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id, 'title' => $title, 'content' => $content, 'photo' => $photo]);
}

function deleteNews($id) {
    global $pdo;

    $sql = "SELECT photo FROM news WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $news = $stmt->fetch();

    if ($news && $news['photo'] && file_exists($news['photo'])) {
        unlink($news['photo']);
    }

    $sql = "DELETE FROM news WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
}

function getNews($sort = 'date', $filter = []) {
    global $pdo;

    $query = "SELECT * FROM news WHERE 1=1";

    if (!empty($filter['title'])) {
        $titles = implode(',', array_fill(0, count($filter['title']), '?'));
        $query .= " AND title IN ($titles)";
    }
    if (!empty($filter['content'])) {
        $query .= " AND content LIKE ?";
    }
    if (!empty($filter['dateFrom']) && !empty($filter['dateTo'])) {
        $query .= " AND DATE(created_at) BETWEEN ? AND ?";
    } elseif (!empty($filter['dateFrom'])) {
        $query .= " AND DATE(created_at) >= ?";
    } elseif (!empty($filter['dateTo'])) {
        $query .= " AND DATE(created_at) <= ?";
    }

    $query .= " ORDER BY " . ($sort === 'date' ? 'created_at' : 'title') . " DESC";

    $stmt = $pdo->prepare($query);
    
    $params = [];
    if (!empty($filter['title'])) {
        $params = array_merge($params, $filter['title']);
    }
    if (!empty($filter['content'])) {
        $params[] = "%{$filter['content']}%";
    }
    if (!empty($filter['dateFrom']) && !empty($filter['dateTo'])) {
        $params[] = $filter['dateFrom'];
        $params[] = $filter['dateTo'];
    } elseif (!empty($filter['dateFrom'])) {
        $params[] = $filter['dateFrom'];
    } elseif (!empty($filter['dateTo'])) {
        $params[] = $filter['dateTo'];
    }

    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRoles() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, name FROM roles");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getNewsById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addUser($first_name, $last_name, $username, $password, $employee_number, $department, $unit, $role_id, $registration_date) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, username, password, employee_number, department_id, unit_id, role_id, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$first_name, $last_name, $username, $password, $employee_number, $department, $unit, $role_id, $registration_date]);
}



function updateUser($id, $username, $password, $firstName, $lastName, $employeeNumber, $department, $organizationalUnit, $role_id) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, first_name = ?, last_name = ?, employee_number = ?, department_id = ?, unit_id = ?, role_id = ? WHERE id = ?");
    $stmt->execute([$username, $hashedPassword, $firstName, $lastName, $employeeNumber, $department, $organizationalUnit, $role_id, $id]);
}


function deleteUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

function getUsers($username = '', $firstName = '', $lastName = '', $employeeNumber = '', $department = '', $organizationalUnit = '', $role_id = '', $accountCreationDate = '') {
    global $pdo;

    $query = "
        SELECT 
            users.id, 
            users.username, 
            users.first_name, 
            users.last_name, 
            users.employee_number, 
            users.role_id, 
            departments.name AS department_name, 
            units.name AS unit_name, 
            roles.name AS role_name, 
            users.registration_date
        FROM users
        LEFT JOIN departments ON users.department_id = departments.id
        LEFT JOIN units ON users.unit_id = units.id
        LEFT JOIN roles ON users.role_id = roles.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($username)) {
        $query .= " AND users.username = ?";
        $params[] = $username;
    }

    if (!empty($firstName)) {
        $query .= " AND users.first_name = ?";
        $params[] = $firstName;
    }

    if (!empty($lastName)) {
        $query .= " AND users.last_name = ?";
        $params[] = $lastName;
    }

    if (!empty($employeeNumber)) {
        $query .= " AND users.employee_number = ?";
        $params[] = $employeeNumber;
    }

    if (!empty($department)) {
        $query .= " AND users.department_id = ?";
        $params[] = $department;
    }

    if (!empty($organizationalUnit)) {
        $query .= " AND users.unit_id = ?";
        $params[] = $organizationalUnit;
    }

    if (!empty($role_id)) {
        $query .= " AND users.role_id = ?";
        $params[] = $role_id;
    }

    if (!empty($accountCreationDate)) {
        $query .= " AND users.registration_date = ?";
        $params[] = $accountCreationDate;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getDepartments() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, name FROM departments");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnits() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, name FROM units");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUniqueUsernames() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT username FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUniqueFirstNames() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT first_name FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUniqueLastNames() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT last_name FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUniqueEmployeeNumbers() {
    global $pdo;
    $stmt = $pdo->query("SELECT DISTINCT employee_number FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateProfile($id, $avatar) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $stmt->execute([$avatar, $id]);
}


function isEmployeeNumberTaken($employee_number) {
    global $pdo; // Załóżmy, że masz zmienną $pdo dla PDO
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE employee_number = ?");
    $stmt->execute([$employee_number]);
    return $stmt->fetchColumn() > 0;
}

function isUsernameTaken($username) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetchColumn() > 0;
}

// Funkcje logowania i rejestracji
function authenticate($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function getRoleNameById($roleId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
    $stmt->execute([$roleId]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    return $role ? $role['name'] : null;
}

function getUserRole($userId) {
    global $pdo;
    
    // Get the role_id for the user
    $stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $roleId = $user['role_id'];
        
        // Get the role name using the role_id
        $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ?");
        $stmt->execute([$roleId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $role ? $role['name'] : null;
    }
    
    return null;
}

function isAdmin() {
    return checkRole('Admin');
}

function isEmployee() {
    return checkRole('Employee');
}

function checkRole($role) {
    if (!checkAuth()) {
        return false;
    }
    
    $userId = $_SESSION['user_id'];
    $userRole = getUserRole($userId);
    
    return $userRole === $role;
}

function checkAuth() {
    return isset($_SESSION['user_id']);
}


function updatePassword($id, $password) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$password, $id]);
}

// Mój Profil
function handleProfileUpdate($userId) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_avatar'])) {
            handleAvatarUpdate($userId);
        } elseif (isset($_POST['change_password'])) {
            handlePasswordChange($userId);
        }
    }
}

function handleAvatarUpdate($userId) {
    $avatar = $_FILES['avatar']['name'] ?? '';
    $target = 'img/' . basename($avatar);

    if ($avatar) {
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            $avatar = basename($avatar);
        } else {
            $avatar = getUserById($userId)['avatar'];
        }
    } else {
        $avatar = getUserById($userId)['avatar'];
    }

    updateProfile($userId, $avatar);
    header('Location: profile.php');
    exit;
}

function handlePasswordChange($userId) {
    global $user;
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (password_verify($currentPassword, $user['password'] ?? '')) {
        if ($newPassword === $confirmPassword) {
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                updatePassword($userId, $hashedPassword);
                $passwordMessage = "Hasło zostało zaktualizowane!";
                $passwordMessageClass = "alert-success";
            } else {
                $passwordMessage = "Nowe hasło nie może być puste!";
                $passwordMessageClass = "alert-danger";
            }
        } else {
            $passwordMessage = "Nowe hasło i potwierdzenie hasła nie pasują do siebie!";
            $passwordMessageClass = "alert-danger";
        }
    } else {
        $passwordMessage = "Aktualne hasło jest niepoprawne!";
        $passwordMessageClass = "alert-danger";
    }
    return compact('passwordMessage', 'passwordMessageClass');
}
// Mój Profil - Koniec

?>
