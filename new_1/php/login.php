<?php
session_start(); 
require_once 'db_connect.php';

if (isset($_SESSION["role"])) {
    if ($_SESSION["role"] == "admin") {
        header("Location: admin.php");
        exit();
    } elseif ($_SESSION["role"] == "student") {
        header("Location: student.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"]);
    $password = trim($_POST["password"]);
    
    // Проверка формата логина (он должен быть email)
    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Ошибка: Логин должен быть в формате email (например, example@mail.com)";
    } else {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row["password"])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["role"] = $row["role"]; 

                if ($_SESSION["role"] == "admin") {
                    header("Location: admin.php");
                } else {
                    header("Location: student.php");
                }
                exit();
            } else {
                $error_message = "Ошибка: Неверный пароль.";
            }
        } else {
            $error_message = "Ошибка: Пользователь с таким логином не найден.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="../css/login_style.css">
</head>
<body>
    <header>
        <div class="logo">🎓 Учет достижений студентов специальности "Информационные системы и программирование"</div>
    </header>

    <div class="container">
        <h1>Авторизация</h1>
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="login">Логин (e-mail):</label>
            <input type="email" id="login" name="login" required placeholder="Введите ваш e-mail">

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required placeholder="Введите ваш пароль">

            <input type="submit" value="Войти">
        </form>
        
        <p>Еще не зарегистрированы? <a href="register.php">Зарегистрироваться</a></p>
        <p><a href="index.php">Вернуться на главную</a></p>
    </div>
    
    <div class="footer">Мои достижения 2025</div>
</body>
</html>
