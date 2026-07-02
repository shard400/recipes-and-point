<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Регистрация
    if ($action === 'register') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);

        if (empty($email) || empty($password) || empty($confirm) || empty($first_name) || empty($last_name)) {
            $error = 'Все поля обязательны для заполнения.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Введите корректный email.';
        } elseif (strlen($password) < 6) {
            $error = 'Пароль должен быть не менее 6 символов.';
        } elseif ($password !== $confirm) {
            $error = 'Пароли не совпадают.';
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = 'Этот email уже зарегистрирован.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (user_email, user_password_hash, user_first_name, user_last_name, user_role) VALUES (?, ?, ?, ?, 'user')");
                $stmt->bind_param("ssss", $email, $hash, $first_name, $last_name);
                if ($stmt->execute()) {
                    $success = 'Регистрация успешна. Теперь вы можете войти.';
                } else {
                    $error = 'Ошибка регистрации. Попробуйте позже.';
                }
            }
            $stmt->close();
        }
    }

    // Авторизация
    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $error = 'Заполните все поля.';
        } else {
            $stmt = $conn->prepare("SELECT user_id, user_password_hash, user_role FROM users WHERE user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id, $hash, $role);
                $stmt->fetch();
                if (password_verify($password, $hash)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_role'] = $role;
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Неверный пароль.';
                }
            } else {
                $error = 'Пользователь с таким email не найден.';
            }
            $stmt->close();
        }
    }
}

require_once 'header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-wrapper">
            <div class="auth-box">
                <h2>Вход</h2>
                <?php if ($error): ?><div style="color:red; margin-bottom:10px;"><?= $error ?></div><?php endif; ?>
                <?php if ($success): ?><div style="color:green; margin-bottom:10px;"><?= $success ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="user@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" placeholder="••••••••" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary full-width">Войти</button>
                </form>
                <p class="auth-switch">Нет аккаунта? <a href="#register" onclick="document.getElementById('register-form').scrollIntoView();">Зарегистрироваться</a></p>
            </div>

            <div class="auth-box" id="register-form">
                <h2>Регистрация</h2>
                <form method="post">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" name="first_name" placeholder="Иван" required>
                    </div>
                    <div class="form-group">
                        <label>Фамилия</label>
                        <input type="text" name="last_name" placeholder="Иванов" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="user@example.com" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль (мин. 6 символов)</label>
                        <input type="password" name="password" placeholder="••••••••" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Подтверждение пароля</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary full-width">Зарегистрироваться</button>
                </form>
                <p class="auth-switch">Уже есть аккаунт? <a href="#login">Войти</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>