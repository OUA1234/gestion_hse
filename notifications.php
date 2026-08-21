<?php

session_start();
require_once("connexion.php");

// حماية الصفحة
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["id"];

// عدد الإشعارات غير المقروءة
$sql = "SELECT COUNT(*)
        FROM notifications
        WHERE utilisateur_id = ?
        AND lu = 0";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$non_lues = $stmt->fetchColumn();

// جلب جميع الإشعارات
$sql = "SELECT *
        FROM notifications
        WHERE utilisateur_id = ?
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// رابط الرجوع حسب الدور
$retour = ($_SESSION["role"] == "Responsable")
    ? "responsable_dashboard.php"
    : "employee_dashboard.php";

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<title>Notifications</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

</style>

</head>

<body>

<div class="container mt-5">

<div class="card">

<div class="card-header bg-warning d-flex justify-content-between align-items-center">

<h3 class="mb-0">
🔔 Mes Notifications
</h3>

<?php if($non_lues > 0){ ?>

<span class="badge bg-danger fs-6">

<?= $non_lues ?>

</span>

<?php } ?>

</div>


<div class="card-body">

<?php if(count($notifications)==0){ ?>

<div class="alert alert-info">

Aucune notification.

</div>

<?php }else{ ?>

<?php foreach($notifications as $n){ ?>

<div class="alert <?= $n["lu"]==0 ? "alert-warning" : "alert-secondary" ?>">

<strong>

<?= htmlspecialchars($n["message"]) ?>

</strong>

<br>

<small class="text-muted">

<?= $n["created_at"] ?>

</small>

<?php if($n["lu"]==0){ ?>

<br><br>

<a href="lire_notification.php?id=<?= $n["id"] ?>"
class="btn btn-primary btn-sm">

Ouvrir

</a>

<?php } ?>

</div>

<?php } ?>

<?php } ?>

<hr>

<a href="<?= $retour ?>"
class="btn btn-secondary">

Retour

</a>

</div>

</div>

</div>

</body>

</html>