<?php
session_start();
require_once("connexion.php");

// 1. حماية الصفحة (الوصول للمسؤول فقط)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Responsable") {
    header("Location: login.php");
    exit();
}

// 2. معالجة تعديل حالة التصريح
if (isset($_POST["modifier"])) {
    $id = $_POST["id"];
    $statut = $_POST["statut"];

    $sql_update = "UPDATE declarations SET statut = :statut WHERE id = :id";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([
        ':statut' => $statut,
        ':id'     => $id
    ]);

    // إعادة التوجيه بنفس الرابط للحفاظ على كلمة البحث وتجنب تكرار الإرسال
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

// 3. استلام وتنظيف نص البحث
$search_input = isset($_GET["search"]) ? trim($_GET["search"]) : "";

// 4. بناء استعلام SQL
$sql = "
    SELECT d.*, u.nom, u.prenom
    FROM declarations d
    INNER JOIN utilisateurs u ON d.utilisateur_id = u.id
";

$params = [];

if ($search_input !== "") {
    $sql .= " WHERE (
        d.id LIKE :search
        OR d.titre LIKE :search 
        OR d.description LIKE :search 
        OR d.lieu LIKE :search 
        OR d.statut LIKE :search 
        OR d.type LIKE :search
        OR d.gravite LIKE :search
        OR u.nom LIKE :search 
        OR u.prenom LIKE :search
    )";
    $params[':search'] = "%" . $search_input . "%";
}

$sql .= " ORDER BY d.created_at DESC";

// 5. تنفيذ الاستعلام
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$declarations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des déclarations HSE</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="bg-light">

    <div class="container my-5">
        <div class="card shadow border-0">
            
            <!-- هيدر الكارت -->
            <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fs-4"><i class="fa-solid fa-file-signature me-2"></i>Gestion des déclarations HSE</h3>
                <span class="badge bg-light text-primary fs-6">
                    Total: <?= count($declarations) ?>
                </span>
            </div>

            <div class="card-body p-4">

                <!-- نموذج البحث -->
                <form method="GET" action="responsable_declarations.php" class="mb-4">
                    <div class="input-group input-group-lg">
                        <input 
                            type="text" 
                            name="search" 
                            class="form-control fs-6" 
                            placeholder="Rechercher par ID, titre, lieu, statut, ou nom du déclarant..." 
                            value="<?= htmlspecialchars($search_input) ?>"
                        >
                        
                        <button class="btn btn-primary fs-6 px-4" type="submit">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>Rechercher
                        </button>

                        <?php if ($search_input !== ""): ?>
                            <a href="responsable_declarations.php" class="btn btn-outline-secondary fs-6">
                                <i class="fa-solid fa-rotate-left me-1"></i>Réinitialiser
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- جدول عرض التصريحات -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Gravité</th>
                                <th>Lieu</th>
                                <th>Déclarant</th>
                                <th>Photo</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($declarations) > 0): ?>
                                <?php foreach ($declarations as $d): ?>
                                    <tr>
                                        <td class="fw-bold text-center">#<?= $d["id"] ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($d["titre"]) ?></td>
                                        <td><?= htmlspecialchars($d["description"]) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d["type"]) ?></span></td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($d["gravite"]) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($d["lieu"]) ?></td>
                                        
                                        <!-- عرض صاحب التصريح بشكل آمن -->
                                        <td class="fw-semibold">
                                            <?= htmlspecialchars(($d["nom"] ?? '') . " " . ($d["prenom"] ?? '')) ?>
                                        </td>

                                        <!-- الصورة -->
                                        <td class="text-center">
                                            <?php if (!empty($d["photo"])): ?>
                                                <a href="uploads/<?= htmlspecialchars($d["photo"]) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                                                    <i class="fa-solid fa-image me-1"></i>Voir
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- تغيير الحالة والتعديل -->
                                        <td>
                                            <form method="POST" action="">
                                                <input type="hidden" name="id" value="<?= $d["id"] ?>">
                                                <select name="statut" class="form-select form-select-sm">
                                                    <option value="Nouvelle" <?= $d["statut"] == "Nouvelle" ? "selected" : "" ?>>Nouvelle</option>
                                                    <option value="En cours" <?= $d["statut"] == "En cours" ? "selected" : "" ?>>En cours</option>
                                                    <option value="Clôturée" <?= $d["statut"] == "Clôturée" ? "selected" : "" ?>>Clôturée</option>
                                                </select>
                                        </td>

                                        <td class="text-center">
                                                <button type="submit" name="modifier" class="btn btn-success btn-sm">
                                                    <i class="fa-solid fa-check me-1"></i>Modifier
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-magnifying-glass fs-2 d-block mb-2"></i>
                                        Aucune déclaration ne correspond à votre recherche.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- أزرار الأسفل -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="responsable_dashboard.php" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left me-1"></i> Retour Dashboard
                    </a>

                    <a href="actions.php" class="btn btn-primary">
                        Actions Correctives <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>