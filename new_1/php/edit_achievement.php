<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

if (isset($_GET['id'])) {
    $achievement_id = $_GET['id'];

    $sql = "SELECT * FROM achievements WHERE id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $achievement_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $achievement = $result->fetch_assoc();
    } else {
        echo "<p class='error'>Достижение не найдено или у вас нет прав на его редактирование.</p>";
        exit;
    }
    $stmt->close();
} else {
    echo "<p class='error'>Не указан ID достижения.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $semester = $_POST["semester"];
    $category = $_POST["category"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $date = $_POST["date"];

    $sql = "UPDATE achievements SET semester = ?, category = ?, title = ?, description = ?, date = ? WHERE id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssii", $semester, $category, $title, $description, $date, $achievement_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        header("Location: student.php");
        exit();
    } else {
        echo "<p class='error'>Ошибка при обновлении достижения: " . $conn->error . "</p>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать достижение</title>
    <link rel="stylesheet" href="../css/student.css">
</head>
<body>
    <header>
        <div class="logo">🎓 Учет достижений студентов специальности "Информационные системы и программирование"</div>
        <nav>
            <a href="student.php" class="back">Назад</a>
            <a href="logout.php" class="logout">Выйти</a>
        </nav>
    </header>

    <div class="container">
        <h1>Редактировать достижение</h1>

        <form method="post" class="achievement-form">
            <label for="semester">Семестр:</label>
            <select name="semester" id="semester">
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php if ($achievement['semester'] == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <label for="category">Категория:</label>
            <select name="category" id="category">
                <option value="educational" <?php if ($achievement['category'] == 'educational') echo 'selected'; ?>>Уровень образовательной программы</option>
                <option value="professional" <?php if ($achievement['category'] == 'professional') echo 'selected'; ?>>Внеурочная деятельность профессиональной направленности</option>
                <option value="general" <?php if ($achievement['category'] == 'general') echo 'selected'; ?>>Внеурочная деятельность общей направленности</option>
            </select>

            <label for="title">Название:</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($achievement['title']); ?>" required>

            <label for="description">Описание:</label>
            <textarea name="description" id="description" required><?php echo htmlspecialchars($achievement['description']); ?></textarea>

            <label for="date">Дата:</label>
            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($achievement['date']); ?>" required>

            <input type="submit" value="Сохранить изменения" class="btn-save">
        </form>
    </div>
</body>
</html>
