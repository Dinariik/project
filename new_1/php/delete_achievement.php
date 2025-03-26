<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "student") {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

if (isset($_GET['id'])) {
    $achievement_id = $_GET['id'];

    $sql = "DELETE FROM achievements WHERE id = $achievement_id AND student_id = " . $_SESSION['user_id'];

    if ($conn->query($sql) === TRUE) {
        echo "Достижение успешно удалено!";
    } else {
        echo "Ошибка при удалении достижения: " . $conn->error;
    }

    $conn->close();
}

header("Location: student.php"); 
exit();
?>