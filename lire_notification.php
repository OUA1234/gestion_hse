<?php

session_start();
require_once("connexion.php");

// حماية الصفحة
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: notifications.php");
    exit();
}

$notification_id = (int)$_GET["id"];
$user_id = $_SESSION["id"];

// جلب رابط الإشعار
$sql = "SELECT lien
        FROM notifications
        WHERE id = ? AND utilisateur_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$notification_id, $user_id]);

$notification = $stmt->fetch(PDO::FETCH_ASSOC);

// جعل الإشعار مقروءًا
$sql = "UPDATE notifications
        SET lu = 1
        WHERE id = ? AND utilisateur_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$notification_id, $user_id]);

// الانتقال للرابط إذا كان موجودًا
if ($notification && !empty($notification["lien"])) {

    header("Location: " . $notification["lien"]);

} else {

    header("Location: notifications.php");

}

exit();