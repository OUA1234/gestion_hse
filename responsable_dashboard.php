<?php
session_start();
require_once("connexion.php");

// حماية الصفحة: المسموح لهم فقط المسؤولين (Responsable)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Responsable") {
    header("Location: login.php");
    exit();
}

// 1. الإحصائيات العامة
$total_declarations = $pdo->query("SELECT COUNT(*) FROM declarations")->fetchColumn();
$nouvelles          = $pdo->query("SELECT COUNT(*) FROM declarations WHERE statut='Nouvelle'")->fetchColumn();
$encours            = $pdo->query("SELECT COUNT(*) FROM declarations WHERE statut='En cours'")->fetchColumn();
$cloturees          = $pdo->query("SELECT COUNT(*) FROM declarations WHERE statut='Clôturée'")->fetchColumn();

// 2. جلب آخر 5 تصريحات مع أصحابها
$sql_declarations = "
    SELECT declarations.*, utilisateurs.nom, utilisateurs.prenom
    FROM declarations
    JOIN utilisateurs ON declarations.utilisateur_id = utilisateurs.id
    ORDER BY declarations.created_at DESC
    LIMIT 5
";
$declarations = $pdo->query($sql_declarations)->fetchAll(PDO::FETCH_ASSOC);

// 3. جلب الإشعارات الخاصة بالمسؤول
$stmt_notif = $pdo->prepare("SELECT * FROM notifications WHERE utilisateur_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt_notif->execute([$_SESSION["id"]]);
$notifications = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);

// 4. عدد الإشعارات غير المقروءة
$stmt_unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0");
$stmt_unread->execute([$_SESSION["id"]]);
$notifications_non_lues = $stmt_unread->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Responsable HSE | OCP</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #0f172a;
            --accent-color: #2563eb;
            --bg-light: #f8fafc;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #334155;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--primary-color);
            color: #f8fafc;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            padding-bottom: 20px;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 20px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .sidebar-link:hover, .sidebar-link.active {
            background-color: var(--accent-color);
            color: #ffffff;
        }

        /* Main Content Wrapper */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }

        /* Header Card */
        .header-card {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: white;
            border-radius: 18px;
            padding: 28px 32px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1);
        }

        /* Stat Cards */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px 24px;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
        }

        .stat-icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .stat-val {
            font-size: 2.1rem;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
            margin-top: 8px;
        }

        /* Section Cards */
        .content-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 28px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .content-card-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-custom th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding: 14px;
        }

        .table-custom td {
            padding: 14px;
            vertical-align: middle;
            color: #334155;
            font-size: 0.95rem;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 16px 8px;
            }
            .sidebar-brand span, .sidebar-link span, .btn-logout span {
                display: none;
            }
            .main-wrapper {
                margin-left: 80px;
                padding: 16px;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar القائمة الجانبية الرسمية -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-shield-halved text-primary fs-3"></i>
            <span>HSE Portal</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="indicateurs.php" class="sidebar-link active">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li>
                <a href="responsable_declarations.php" class="sidebar-link">
                    <i class="fa-solid fa-file-signature"></i>
                    <span>Déclarations</span>
                </a>
            </li>
            <li>
                <a href="actions.php" class="sidebar-link">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Actions</span>
                </a>
            </li>
            <li>
                <a href="notifications.php" class="sidebar-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa-solid fa-bell"></i>
                        <span class="ms-1">Notifications</span>
                    </div>
                    <?php if ($notifications_non_lues > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?= $notifications_non_lues ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="pt-3 border-top border-secondary">
            <a href="logout.php" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </aside>

    <!-- Main Wrapper المحتوى الرئيسي -->
    <main class="main-wrapper">

        <!-- Banner / Header -->
        <div class="header-card d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1">Espace Responsable HSE</h2>
                <p class="text-light opacity-75 mb-0">
                    Bienvenue, <strong><?= htmlspecialchars($_SESSION["nom"] ?? "Responsable") ?></strong>
                </p>
            </div>
            <div class="text-end">
                <div class="badge bg-primary fs-6 px-3 py-2 rounded-3 mb-1">
                    <i class="fa-regular fa-calendar-check me-1"></i> <?= date("d/m/Y") ?>
                </div>
                <div class="small text-light opacity-50">Système HSE OCP</div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">TOTAL DÉCLARATIONS</span>
                            <div class="stat-val"><?= $total_declarations ?></div>
                        </div>
                        <div class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">NOUVELLES</span>
                            <div class="stat-val text-warning"><?= $nouvelles ?></div>
                        </div>
                        <div class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">EN COURS</span>
                            <div class="stat-val text-info"><?= $encours ?></div>
                        </div>
                        <div class="stat-icon-wrapper bg-info bg-opacity-10 text-info">
                            <i class="fa-solid fa-spinner"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small fw-semibold">CLÔTURÉES</span>
                            <div class="stat-val text-success"><?= $cloturees ?></div>
                        </div>
                        <div class="stat-icon-wrapper bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Section: Notifications & Table -->
        <div class="row g-4">
            
            <!-- Notifications Column -->
            <div class="col-lg-5">
                <div class="content-card h-100 mb-0">
                    <div class="content-card-title">
                        <span><i class="fa-solid fa-bell text-warning me-2"></i>Dernières Notifications</span>
                        <a href="notifications.php" class="btn btn-sm btn-link text-decoration-none">Voir tout</a>
                    </div>

                    <?php if (count($notifications) > 0): ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($notifications as $n): ?>
                                <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <p class="mb-1 text-dark small fw-medium"><?= htmlspecialchars($n["message"]) ?></p>
                                        <span class="text-muted extra-small" style="font-size: 0.75rem;">
                                            <i class="fa-regular fa-clock me-1"></i><?= $n["created_at"] ?>
                                        </span>
                                    </div>
                                    <a href="declaration_details.php?id=<?= $n["declaration_id"] ?>" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                        Détails
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-center border text-muted my-3 py-4">
                            <i class="fa-regular fa-bell-slash fs-3 d-block mb-2"></i>
                            Aucune notification récente.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Declarations Column -->
            <div class="col-lg-7">
                <div class="content-card h-100 mb-0">
                    <div class="content-card-title">
                        <span><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Dernières Déclarations</span>
                        <a href="declarations.php" class="btn btn-sm btn-link text-decoration-none">Consulter tout</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Titre</th>
                                    <th>Gravité</th>
                                    <th>Statut</th>
                                    <th>Déclarant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($declarations) > 0): ?>
                                    <?php foreach ($declarations as $d): ?>
                                        <tr>
                                            <td class="fw-bold text-secondary">#<?= $d["id"] ?></td>
                                            <td class="fw-medium text-dark"><?= htmlspecialchars($d["titre"]) ?></td>
                                            <td>
                                                <?php 
                                                    $graviteClass = match($d["gravite"]) {
                                                        'Élevée', 'Grave', 'Haute' => 'bg-danger-subtle text-danger border-danger',
                                                        'Moyenne' => 'bg-warning-subtle text-warning-emphasis border-warning',
                                                        default => 'bg-secondary-subtle text-secondary border-secondary'
                                                    };
                                                ?>
                                                <span class="badge border <?= $graviteClass ?> px-2 py-1">
                                                    <?= htmlspecialchars($d["gravite"]) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($d["statut"]) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="fw-semibold text-dark">
                                                    <?= htmlspecialchars($d["nom"] . " " . $d["prenom"]) ?>
                                                </small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Aucune déclaration enregistrée.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>