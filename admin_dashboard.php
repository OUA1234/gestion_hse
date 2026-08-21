<?php
session_start();
require_once("connexion.php");

// 1. حماية الصفحة (سماح للأدمن Admin فقط)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Admin") {
    header("Location: login.php");
    exit();
}

// 2. جلب عدد الإشعارات غير المقروءة (إن وجدت)
$notifications_non_lues = 0;
try {
    $stmt_unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0");
    $stmt_unread->execute([$_SESSION["id"]]);
    $notifications_non_lues = $stmt_unread->fetchColumn();
} catch (Exception $e) {
    // في حال عدم وجود جدول الإشعارات لا نوقف الصفحة
}

// 3. إحصائيات لوحة التحكم (KPIs)
$total_declarations = $pdo->query("SELECT COUNT(*) FROM declarations")->fetchColumn();
$nouvelles          = $pdo->query("SELECT COUNT(*) FROM declarations WHERE statut = 'Nouvelle'")->fetchColumn();
$en_cours           = $pdo->query("SELECT COUNT(*) FROM declarations WHERE statut = 'En cours'")->fetchColumn();
$cloturees          = $pdo->query("SELECT COUNT(*) FROM declarations WHERE statut = 'Clôturée'")->fetchColumn();

// 4. جلب أحدث 5 تصريحات للجدول السريع
$stmt_recent = $pdo->query("
    SELECT declarations.*, utilisateurs.nom, utilisateurs.prenom 
    FROM declarations 
    JOIN utilisateurs ON declarations.utilisateur_id = utilisateurs.id 
    ORDER BY declarations.created_at DESC 
    LIMIT 5
");
$recent_declarations = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | HSE</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        /* KPI Cards */
        .kpi-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .kpi-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        /* Content Cards */
        .content-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        .table-custom th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            padding: 14px;
        }

        .table-custom td {
            padding: 14px;
            vertical-align: middle;
            color: #334155;
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

    <!-- القائمة الجانبية (Sidebar Admin) -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-shield-halved text-primary fs-3"></i>
            <span>HSE Portal</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="admin_dashboard.php" class="sidebar-link active">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="admin_declarations.php" class="sidebar-link">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Déclarations</span>
                </a>
            </li>
            <li>
                <a href="admin_actions.php" class="sidebar-link">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Actions</span>
                </a>
            </li>
            <li>
                <a href="indicateurs.php" class="sidebar-link">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Indicateurs</span>
                </a>
            </li>
            <li>
                <a href="utilisateurs.php" class="sidebar-link">
                    <i class="fa-solid fa-users"></i>
                    <span>Utilisateurs</span>
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

    <!-- المحتوى الرئيسي -->
    <main class="main-wrapper">

        <!-- هيدر لوحة التحكم -->
        <div class="header-card d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="fa-solid fa-user-gear me-2"></i>Bienvenue, <?= htmlspecialchars($_SESSION["nom"]) ?></h2>
                <p class="text-light opacity-75 mb-0">
                    Espace Administrateur HSE - Vue d'ensemble du système.
                </p>
            </div>
            <div>
                <span class="badge bg-light text-dark fs-6 px-3 py-2 rounded-3">
                    <i class="fa-solid fa-shield-halved me-2 text-primary"></i>Administrateur
                </span>
            </div>
        </div>

        <!-- بطاقات الإحصائيات (KPIs) -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <span class="text-muted small fw-semibold">Total Déclarations</span>
                        <h3 class="fw-bold my-1 text-dark"><?= $total_declarations ?></h3>
                    </div>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <span class="text-muted small fw-semibold">Nouvelles</span>
                        <h3 class="fw-bold my-1 text-warning"><?= $nouvelles ?></h3>
                    </div>
                    <div class="kpi-icon bg-warning-subtle text-warning">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <span class="text-muted small fw-semibold">Actions en cours</span>
                        <h3 class="fw-bold my-1 text-info"><?= $en_cours ?></h3>
                    </div>
                    <div class="kpi-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-spinner"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="kpi-card">
                    <div>
                        <span class="text-muted small fw-semibold">Clôturées</span>
                        <h3 class="fw-bold my-1 text-success"><?= $cloturees ?></h3>
                    </div>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- الرسم البياني والجدول السريع -->
        <div class="row g-4">
            <!-- Chart Section -->
            <div class="col-lg-5">
                <div class="content-card h-100">
                    <h5 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Statistiques HSE</h5>
                    <div style="max-height: 280px;" class="d-flex justify-content-center">
                        <canvas id="statutChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="col-lg-7">
                <div class="content-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Dernières Déclarations HSE</h5>
                        <a href="admin_declarations.php" class="btn btn-sm btn-link text-decoration-none">
                            Voir tout <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Gravité</th>
                                    <th>Statut</th>
                                    <th>Déclarant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_declarations) > 0): ?>
                                    <?php foreach ($recent_declarations as $d): ?>
                                        <tr>
                                            <td class="fw-bold text-secondary">#<?= $d["id"] ?></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($d["titre"]) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d["type"] ?? 'Incident') ?></span></td>
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
                                                <?php 
                                                    $statutClass = match($d["statut"]) {
                                                        'Nouvelle' => 'bg-warning text-dark',
                                                        'En cours' => 'bg-info text-dark',
                                                        'Clôturée' => 'bg-success text-white',
                                                        default => 'bg-light text-dark border'
                                                    };
                                                ?>
                                                <span class="badge <?= $statutClass ?>">
                                                    <?= htmlspecialchars($d["statut"]) ?>
                                                </span>
                                            </td>
                                            <td><small class="fw-semibold"><?= htmlspecialchars($d["nom"] . " " . $d["prenom"]) ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Aucune déclaration récente.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Chart.js Script -->
    <script>
        const ctx = document.getElementById('statutChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Nouvelles', 'En cours', 'Clôturées'],
                datasets: [{
                    data: [<?= $nouvelles ?>, <?= $en_cours ?>, <?= $cloturees ?>],
                    backgroundColor: ['#f59e0b', '#06b6d4', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>