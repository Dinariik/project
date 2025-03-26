<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

// Получение списка групп
$sql_groups = "SELECT DISTINCT gruppa FROM users WHERE role = 'student'"; 
$groups_result = $conn->query($sql_groups);
$groups = [];
if ($groups_result->num_rows > 0) {
    while ($row = $groups_result->fetch_assoc()) {
        $groups[] = $row['gruppa'];
    }
}

$students = [];
$student_info = null;
$achievements = [];

if (isset($_GET['group'])) {
    $group = $_GET['group'];

    // Получение списка студентов в выбранной группе
    $sql_students = $conn->prepare("SELECT id, fio, gruppa, login FROM users WHERE role = 'student' AND gruppa = ?");
    $sql_students->bind_param("s", $group);
    $sql_students->execute();
    $students_result = $sql_students->get_result();

    if ($students_result->num_rows > 0) {
        while ($row = $students_result->fetch_assoc()) {
            $students[] = $row;
        }
    }

    // Получение информации о студенте и его достижениях
    if (isset($_GET['student_id'])) {
        $student_id = $_GET['student_id'];

        // Информация о студенте
        $sql_student_info = $conn->prepare("SELECT fio, gruppa, login FROM users WHERE id = ?");
        $sql_student_info->bind_param("i", $student_id);
        $sql_student_info->execute();
        $student_info_result = $sql_student_info->get_result();
        if ($student_info_result->num_rows > 0) {
            $student_info = $student_info_result->fetch_assoc();
        }

        // Достижения студента
        $sql_achievements = $conn->prepare("SELECT * FROM achievements WHERE student_id = ?");
        $sql_achievements->bind_param("i", $student_id);
        $sql_achievements->execute();
        $achievements_result = $sql_achievements->get_result();

        if ($achievements_result->num_rows > 0) {
            while ($row = $achievements_result->fetch_assoc()) {
                $achievements[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница администратора</title>
    <link rel="stylesheet" href="../css/adminn.css">
</head>
<body>
    <header>
        <div class="logo">🎓 Панель администратора</div>
        <nav>
            <a href="logout.php" class="logout">Выйти</a>
        </nav>
    </header>

    <div class="container">
        <h2>Выберите группу и студента</h2>
        <form method="get" action="">
            <label for="group">Группа:</label>
            <select name="group" id="group" required>
                <option value="">Выберите группу</option>
                <?php foreach ($groups as $group_item): ?>
                    <option value="<?php echo htmlspecialchars($group_item); ?>" <?php if (isset($_GET['group']) && $_GET['group'] == $group_item) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($group_item); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Выбрать">
        </form>

        <?php if (isset($_GET['group'])): ?>
            <h3>Студенты в группе "<?php echo htmlspecialchars($group); ?>"</h3>
            <form method="get" action="">
                <input type="hidden" name="group" value="<?php echo htmlspecialchars($group); ?>">
                <label for="student_id">Студент:</label>
                <select name="student_id" id="student_id" required>
                    <option value="">Выберите студента</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" <?php if (isset($_GET['student_id']) && $_GET['student_id'] == $student['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($student['fio']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="Показать">
            </form>
        <?php endif; ?>

        <?php if (isset($_GET['student_id']) && $student_info): ?>
            <h3>Информация о студенте</h3>
            <p><strong>ФИО:</strong> <?php echo htmlspecialchars($student_info['fio']); ?></p>
            <p><strong>Группа:</strong> <?php echo htmlspecialchars($student_info['gruppa']); ?></p>
            <p><strong>Логин:</strong> <?php echo htmlspecialchars($student_info['login']); ?></p>

            <h3>Достижения студента</h3>
            <?php if (!empty($achievements)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Семестр</th>
                            <th>Категория</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Дата</th>
                            <th>Изображение</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($achievements as $achievement): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($achievement['semester']); ?></td>
                                <td><?php echo htmlspecialchars($achievement['category']); ?></td>
                                <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                                <td><?php echo htmlspecialchars($achievement['description']); ?></td>
                                <td><?php echo htmlspecialchars($achievement['date']); ?></td>
                                <td>
                                    <?php if (!empty($achievement['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($achievement['image']); ?>" alt="Достижение" width="100">
                                    <?php else: ?>
                                        Нет изображения
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>У студента нет достижений.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 Учет достижений студентов</p>
    </footer>
</body>
</html>

<?php $conn->close(); ?>