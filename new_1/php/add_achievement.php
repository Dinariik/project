<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION["user_id"];
    $semester = $_POST["semester"];
    $category = $_POST["category"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $date = $_POST["date"];
    $image_path = null;
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "../php/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES["image"]["name"]));
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            die("Файл не является изображением.");
        }
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            die("Допустимые форматы: JPG, JPEG, PNG, GIF.");
        }
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $image_name;
        } else {
            die("Ошибка при загрузке файла.");
        }
    }
    $sql = "INSERT INTO achievements (student_id, semester, category, title, description, date, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssss", $student_id, $semester, $category, $title, $description, $date, $image_path);

    if ($stmt->execute()) {
        header("Location: student.php");
        exit();
    } else {
        echo "Ошибка: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
