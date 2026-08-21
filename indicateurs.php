<?php
session_start();
require_once("connexion.php");

if (!isset($_SESSION["id"]) || ($_SESSION["role"] != "Admin" && $_SESSION["role"] != "Responsable")) {
    header("Location: login.php");
    exit();
}

// 1. المؤشرات الرقمية الرئيسية
$total_declarations = $pdo->query("SELECT COUNT(*) FROM declarations")->fetchColumn();
$nouvelles          = $pdo->query("SELECT COUNT(*) FROM declarations WHERE statut='Nouvelle'")->fetchColumn();
$total_actions      = $pdo->query("SELECT COUNT(*) FROM actions_correctives")->fetchColumn();
$actions_terminees  = $pdo->query("SELECT COUNT(*) FROM actions_correctives WHERE statut='Terminé'")->fetchColumn();

// نسبة إغلاق الإجراءات
$taux_cloture = ($total_actions > 0) ? round(($actions_terminees / $total_actions) * 100) : 0;

// 2. جلب البيانات للرسوم البيانية (مفصلة ومصلحة)
$statuts  = $pdo->query("SELECT statut, COUNT(*) as total FROM declarations GROUP BY statut")->fetchAll(PDO::FETCH_ASSOC);
$lieux    = $pdo->query("SELECT lieu, COUNT(*) AS total FROM declarations GROUP BY lieu ORDER BY total DESC LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);
$gravites = $pdo->query("SELECT gravite, COUNT(*) as total FROM declarations GROUP BY gravite")->fetchAll(PDO::FETCH_ASSOC);

// 3. جدول آخر 5 تصريحات
$stmt_last = $pdo->query("SELECT titre, type, gravite, statut, created_at FROM declarations ORDER BY created_at DESC LIMIT 5");
$dernieres_declarations = $stmt_last->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord HSE - Indicateurs</title>

    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f4f7fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .header-dashboard {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
            padding: 25px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(25, 135, 84, 0.2);
        }

        .card-stat {
            border: none;
            border-radius: 16px;
            padding: 20px 25px;
            color: white;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            height: 100%;
        }

        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .bg-gradient-blue   { background: linear-gradient(135deg, #0d6efd, #0dcaf0); }
        .bg-gradient-orange { background: linear-gradient(135deg, #fd7e14, #ffc107); }
        .bg-gradient-purple { background: linear-gradient(135deg, #6f42c1, #a855f7); }
        .bg-gradient-green  { background: linear-gradient(135deg, #198754, #20c997); }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.85;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .chart-card h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 20px;
        }

        .chart-container {
            position: relative;
            flex-grow: 1;
            min-height: 250px;
            max-height: 320px;
        }

        .table-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        .custom-table thead {
            background-color: #f8f9fa;
        }

        .custom-table th {
            color: #6c757d;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
    </style>
</head>

<body>

<div class="container py-4">

    <!-- الهيدر العلوي -->
    <div class="header-dashboard d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1"><i class="fa-solid fa-shield-halved me-2"></i>Tableau de bord HSE</h2>
            <p class="mb-0 opacity-75">Bienvenue <strong><?= htmlspecialchars($_SESSION["role"]) ?></strong></p>
        </div>
        <div class="text-end">
            <span class="badge bg-light text-dark fs-6 px-3 py-2 rounded-pill">
                <i class="fa-regular fa-calendar-days me-1"></i> <?= date("d/m/Y") ?>
            </span>
        </div>
    </div>

    <!-- الصف الأول: البطاقات الرقمية 5 بطاقات متناسقة -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card-stat bg-gradient-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold">Déclarations</small>
                        <div class="stat-number mt-1"><?= $total_declarations ?></div>
                    </div>
                    <i class="fa-solid fa-file-lines stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card-stat bg-gradient-orange">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold">Nouvelles</small>
                        <div class="stat-number mt-1"><?= $nouvelles ?></div>
                    </div>
                    <i class="fa-solid fa-bell stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card-stat bg-gradient-purple">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold">Actions</small>
                        <div class="stat-number mt-1"><?= $total_actions ?></div>
                    </div>
                    <i class="fa-solid fa-list-check stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6">
            <div class="card-stat bg-gradient-green">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold">Terminées</small>
                        <div class="stat-number mt-1"><?= $actions_terminees ?></div>
                    </div>
                    <i class="fa-solid fa-circle-check stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-12">
            <div class="card-stat bg-gradient-green">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-uppercase fw-semibold">Taux de clôture</small>
                        <div class="stat-number mt-1"><?= $taux_cloture ?>%</div>
                    </div>
                    <i class="fa-solid fa-chart-line stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- الصف الثاني: الرسوم البيانية منظم في شبكة 3 أعمدة متناسقة -->
    <div class="row g-4 mb-4">
        
        <!-- رسم 1: Gravité -->
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <h5><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Gravité des incidents</h5>
                <div class="chart-container">
                    <canvas id="graviteChart"></canvas>
                </div>
            </div>
        </div>

        <!-- رسم 2: Statut -->
        <div class="col-lg-4 col-md-6">
            <div class="chart-card">
                <h5><i class="fa-solid fa-chart-pie text-primary me-2"></i>Statut des déclarations</h5>
                <div class="chart-container">
                    <canvas id="statutChart"></canvas>
                </div>
            </div>
        </div>

        <!-- رسم 3: Lieux -->
        <div class="col-lg-4 col-md-12">
            <div class="chart-card">
                <h5><i class="fa-solid fa-location-dot text-danger me-2"></i>Incidents par lieu</h5>
                <div class="chart-container">
                    <canvas id="lieuChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- الصف الثالث: الجدول -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <h5 class="mb-3"><i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Dernières déclarations</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table mb-0">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Gravité</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($dernieres_declarations) > 0): ?>
                                <?php foreach ($dernieres_declarations as $d): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($d['titre']) ?></td>
                                        <td><?= htmlspecialchars($d['type']) ?></td>
                                        <td>
                                            <?php 
                                                $badgeGravite = match($d['gravite']) {
                                                    'Élevée', 'Haute', 'Grave' => 'bg-danger',
                                                    'Moyenne' => 'bg-warning text-dark',
                                                    default => 'bg-secondary'
                                                };
                                            ?>
                                            <span class="badge <?= $badgeGravite ?>"><?= htmlspecialchars($d['gravite']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?= htmlspecialchars($d['statut']) ?></span>
                                        </td>
                                        <td><i class="fa-regular fa-clock me-1 text-muted"></i><?= date("d/m/Y", strtotime($d['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Aucune déclaration trouvée.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- إعداد الرسوم البيانية Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Chart: Gravité
    const graviteLabels = <?= json_encode(array_column($gravites, 'gravite')) ?>;
    const graviteValues = <?= json_encode(array_column($gravites, 'total')) ?>;

    new Chart(document.getElementById('graviteChart'), {
        type: 'doughnut',
        data: {
            labels: graviteLabels,
            datasets: [{
                data: graviteValues,
                backgroundColor: ['#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 2. Chart: Statut
    const statutLabels = <?= json_encode(array_column($statuts, 'statut')) ?>;
    const statutValues = <?= json_encode(array_column($statuts, 'total')) ?>;

    new Chart(document.getElementById('statutChart'), {
        type: 'bar',
        data: {
            labels: statutLabels,
            datasets: [{
                label: 'Nombre',
                data: statutValues,
                backgroundColor: '#0d6efd',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 3. Chart: Lieux
    const lieuLabels = <?= json_encode(array_column($lieux, 'lieu')) ?>;
    const lieuValues = <?= json_encode(array_column($lieux, 'total')) ?>;

    new Chart(document.getElementById('lieuChart'), {
        type: 'bar',
        data: {
            labels: lieuLabels,
            datasets: [{
                label: 'Incidents',
                data: lieuValues,
                backgroundColor: '#20c997',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>

</body>
</html>