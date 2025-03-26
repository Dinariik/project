<?php
require_once 'db_connect.php';

$error_message = ""; // Переменная для ошибки

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fio = trim($_POST["fio"]);
    $gruppa = trim($_POST["gruppa"]);
    $login = trim($_POST["login"]);
    $password = trim($_POST["password"]);

    // Проверка формата логина (логин должен содержать @)
    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Ошибка: Логин должен содержать символ '@' (например, example@mail.com)";
    } else {
        // Проверка, существует ли логин в базе
        $stmt = $conn->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Ошибка: Пользователь с таким логином уже существует!";
        } else {
            // Хешируем пароль
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (fio, gruppa, login, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $fio, $gruppa, $login, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Ошибка при регистрации: " . $stmt->error;
            }
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
    <title>Регистрация</title>
    <link rel="stylesheet" href="../css/reg.css">
</head>
<body>
    <header>
        <div class="logo">🎓 Учет достижений студентов специальности "Информационные системы и программирование"</div>
    </header>

    <div class="container">
        <h1>Регистрация</h1>

        <!-- Вывод ошибки -->
        <?php if (!empty($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="fio">ФИО:</label>
            <input type="text" id="fio" name="fio" required placeholder="Введите ваше полное имя">

            <label for="gruppa">Группа:</label>
            <input type="text" id="gruppa" name="gruppa" required placeholder="Например, ИСП-2025">

            <label for="login">Логин (e-mail):</label>
            <input type="email" id="login" name="login" required placeholder="Введите ваш e-mail">

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required placeholder="Придумайте пароль">

            <input type="submit" value="Зарегистрироваться">
        </form>

        <p>Уже зарегистрированы? <a href="login.php">Войти</a></p>
    </div>
    
    <div class="footer">Мои достижения 2025</div>
</body>
</html>
