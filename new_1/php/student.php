<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$student_id = $_SESSION["user_id"];

$sql = "SELECT fio, gruppa, birth_date, phone, additional_info, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($fio, $gruppa, $birth_date, $phone, $additional_info, $profile_image);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $new_fio = trim($_POST["fio"]);
    $new_gruppa = trim($_POST["gruppa"]);
    $new_birth_date = trim($_POST["birth_date"]);
    $new_phone = trim($_POST["phone"]);
    $new_additional_info = trim($_POST["additional_info"]);

    if (!empty($_FILES["profile_image"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file);
        $profile_image = $target_file;
    }
    $sql = "UPDATE users SET fio=?, gruppa=?, birth_date=?, phone=?, additional_info=?, profile_image=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $new_fio, $new_gruppa, $new_birth_date, $new_phone, $new_additional_info, $profile_image, $student_id);

    if ($stmt->execute()) {
        header("Location: student.php");
        exit();
    }

    $stmt->close();
}
$sql = "SELECT * FROM achievements WHERE student_id = ? ORDER BY semester";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$achievements = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $achievements[$row['semester']][$row['category']][] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница студента</title>
    <link rel="stylesheet" href="../css/student_style.css">
</head>
<body>
    <header>
        <div class="logo">🎓 Учет достижений студентов специальности "Информационные системы и программирование"</div>
        <nav>
            <a href="logout.php" class="logout">Выйти</a>
        </nav>
    </header>
    <div class="main-content">
    <div class="profile-box">
        <h1>Здравствуйте, <?php echo htmlspecialchars($fio); ?>!</h1>
        <p><strong>Группа:</strong> <?php echo htmlspecialchars($gruppa); ?></p>
        <p><strong>Дата рождения:</strong> <?php echo htmlspecialchars($birth_date); ?></p>
        <p><strong>Номер телефона:</strong> <?php echo htmlspecialchars($phone); ?></p>
        <p><strong>Доп. информация:</strong> <?php echo htmlspecialchars($additional_info); ?></p>
        <?php if (!empty($profile_image)): ?>
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Фото профиля" class="profile-image">
        <?php endif; ?>
        <h2>Редактировать профиль</h2>
        <form action="student.php" method="post" enctype="multipart/form-data">
            <label for="fio">ФИО:</label>
            <input type="text" name="fio" id="fio" value="<?php echo htmlspecialchars($fio); ?>" required>

            <label for="gruppa">Группа:</label>
            <input type="text" name="gruppa" id="gruppa" value="<?php echo htmlspecialchars($gruppa); ?>" required>

            <label for="birth_date">Дата рождения:</label>
            <input type="date" name="birth_date" id="birth_date" value="<?php echo htmlspecialchars($birth_date); ?>">

            <label for="phone">Номер телефона:</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>">

            <label for="additional_info">Доп. информация:</label>
            <textarea name="additional_info" id="additional_info"><?php echo htmlspecialchars($additional_info); ?></textarea>

            <label for="profile_image">Фото профиля:</label>
            <input type="file" name="profile_image" id="profile_image" accept="image/*">

            <input type="submit" name="update_profile" value="Сохранить изменения">
        </form>
    </div>
    <div class="achievement-form-container">
        <h1>Добавить достижение</h1>
        <form action="add_achievement.php" method="post" enctype="multipart/form-data">
            <label for="semester">Семестр:</label>
            <select name="semester" id="semester">
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>

            <label for="category">Категория:</label>
            <select name="category" id="category">
                <option value="educational">Уровень образовательной программы</option>
                <option value="professional">Внеурочная деятельность профессиональной направленности</option>
                <option value="general">Внеурочная деятельность общей направленности</option>
            </select>

            <label for="title">Название:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Описание:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="date">Дата:</label>
            <input type="date" name="date" id="date" required>

            <label for="image">Изображение достижения:</label>
            <input type="file" name="image" id="image" accept="image/*">

            <input type="submit" value="Добавить">
        </form>
    </div>
</div>
    <div class="achievements-container">
        <h1>Ваши достижения</h1>
        <div class="achievements">
            <?php for ($semester = 1; $semester <= 8; $semester++): ?>
                <div class="semester-card">
                    <h2>Семестр <?php echo $semester; ?></h2>

                    <?php foreach (["educational" => "Уровень образовательной программы", "professional" => "Внеурочная деятельность профессиональной направленности", "general" => "Внеурочная деятельность общей направленности"] as $category => $category_title): ?>
                        <h3><?php echo $category_title; ?></h3>
                        <?php if (isset($achievements[$semester][$category])): ?>
                            <ul>
                                <?php foreach ($achievements[$semester][$category] as $achievement): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($achievement['title']); ?></strong> - 
                                        <?php echo htmlspecialchars($achievement['description']); ?> 
                                        (<?php echo htmlspecialchars($achievement['date']); ?>)
                                        <?php if (!empty($achievement['image'])): ?>
                                            <br><img src="<?php echo htmlspecialchars($achievement['image']); ?>" alt="Изображение достижения" style="max-width:200px;">
                                        <?php endif; ?>
                                        <br>
                                        <a href="edit_achievement.php?id=<?php echo $achievement['id']; ?>">✏</a>
                                        <a href="delete_achievement.php?id=<?php echo $achievement['id']; ?>">❌</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>Нет достижений.</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
