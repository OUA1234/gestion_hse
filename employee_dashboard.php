<?php
session_start();
require_once("connexion.php");

// 1. حماية الصفحة (خاص بالموظف فقط)
if (!isset($_SESSION["id"]) || $_SESSION["role"] != "Employe") {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["id"];

// 2. جلب أحدث الإشعارات للموظف (حد أقصى 5)
$sql = "SELECT * FROM notifications WHERE utilisateur_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. عدد الإشعارات غير المقروءة
$sql = "SELECT COUNT(*) FROM notifications WHERE utilisateur_id = ? AND lu = 0";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$notifications_non_lues = $stmt->fetchColumn();

// 4. إحصائيات التصريحات الخاصة بالموظف
$stmt = $pdo->prepare("SELECT COUNT(*) FROM declarations WHERE utilisateur_id=?");
$stmt->execute([$id]);
$total = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM declarations WHERE utilisateur_id=? AND statut='Nouvelle'");
$stmt->execute([$id]);
$nouvelle = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM declarations WHERE utilisateur_id=? AND statut='En cours'");
$stmt->execute([$id]);
$encours = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM declarations WHERE utilisateur_id=? AND statut='Clôturée'");
$stmt->execute([$id]);
$cloturee = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Employé | HSE Experience</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f0f4f8;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            padding-bottom: 40px;
        }

        /* Hero Banner Container */
        .hero-banner {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border-radius: 24px;
            padding: 35px 30px;
            color: white;
            box-shadow: 0 20px 30px -10px rgba(2, 132, 199, 0.3);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        /* Glass Floating Navigation */
        .nav-floating {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 12px 24px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
            margin-bottom: 30px;
        }

        /* Interactive Action Buttons */
        .btn-action-main {
            background: #ffffff;
            color: #0284c7;
            font-weight: 700;
            border-radius: 14px;
            padding: 12px 24px;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-action-main:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            color: #0369a1;
        }

        /* App-like Feature Cards */
        .app-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .app-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.07);
            border-color: #cbd5e1;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            margin-bottom: 16px;
        }

        /* Stat Pills */
        .stat-pill {
            background: #ffffff;
            border-radius: 18px;
            padding: 16px 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .stat-pill .number {
            font-size: 1.8rem;
            font-weight: 800;
            line-height: 1;
        }

        /* Notification Styling */
        .notif-box {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            padding: 24px;
        }

        .notif-item-custom {
            padding: 14px 18px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            margin-bottom: 10px;
            transition: 0.2s;
        }

        .notif-item-custom:hover {
            background: #f1f5f9;
        }
    </style>
</head>

<body>

    <div class="container py-3">

        <!-- Top Navigation Bar -->
        <div class="nav-floating d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary text-white p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-shield-halved fs-5"></i>
                </div>
                <span class="fw-bold fs-5 text-slate-800">HSE Mobile Portal</span>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="notifications.php" class="btn btn-light rounded-circle position-relative p-2" style="width: 42px; height: 42px;">
                    <i class="fa-solid fa-bell text-secondary"></i>
                    <?php if ($notifications_non_lues > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle"></span>
                    <?php endif; ?>
                </a>

                <div class="vr my-1"></div>

                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-power-off me-1"></i> Quitter
                </a>
            </div>
        </div>

        <!-- Hero Welcome Card -->
        <div class="hero-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold mb-2">Espace Securité</span>
                <h2 class="fw-bold mb-1">Bonjour, <?= htmlspecialchars($_SESSION["nom"] ?? 'Employé') ?> 👋</h2>
                <p class="mb-0 text-white-50 fs-6">Votre vigilance garantit la sécurité de tous. Signalons ensemble كل المخاطر.</p>
            </div>
            <div>
                <a href="declaration_add.php" class="btn btn-action-main">
                    <i class="fa-solid fa-plus-circle me-2"></i>Signaler un incident
                </a>
            </div>
        </div>

        <!-- Quick Action Grid (3 Big Buttons) -->
        <h5 class="fw-bold mb-3 text-dark px-1"><i class="fa-solid fa-grid-2 me-2"></i>Services Rapides</h5>
        <div class="row g-4 mb-5">
            
            <div class="col-md-4">
                <div class="app-card">
                    <div>
                        <div class="icon-box bg-success-subtle text-success">
                            <i class="fa-solid fa-file-circle-plus"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Nouveau Signalement</h4>
                        <p class="text-muted small">Déclarez immédiatement un accident, incident ou risque détecté sur le terrain.</p>
                    </div>
                    <a href="declaration_add.php" class="btn btn-success rounded-3 w-100 fw-bold mt-3">
                        Créer une déclaration <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="app-card">
                    <div>
                        <div class="icon-box bg-primary-subtle text-primary">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Mes Déclarations</h4>
                        <p class="text-muted small">Suivez l'état d'avancement de vos signalements en temps réel.</p>
                    </div>
                    <a href="mes_declarations.php" class="btn btn-primary rounded-3 w-100 fw-bold mt-3">
                        Voir l'historique <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <div class="col-md-4">
                <div class="app-card">
                    <div>
                        <div class="icon-box bg-warning-subtle text-warning">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Mes Actions Assigned</h4>
                        <p class="text-muted small">Consultez et validez les actions correctives qui vous ont été assignées.</p>
                    </div>
                    <a href="mes_actions.php" class="btn btn-warning text-dark rounded-3 w-100 fw-bold mt-3">
                        Consulter mes actions <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- Live Statistics Row -->
        <h5 class="fw-bold mb-3 text-dark px-1"><i class="fa-solid fa-chart-simple me-2"></i>Aperçu de mes statistiques</h5>
        <div class="row g-3 mb-5">
            <div class="col-6 col-lg-3">
                <div class="stat-pill">
                    <div class="number text-dark"><?= $total ?></div>
                    <div class="text-muted small fw-semibold">Total<br>Déclarations</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-pill">
                    <div class="number text-warning"><?= $nouvelle ?></div>
                    <div class="text-muted small fw-semibold">En attente<br>(Nouvelles)</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-pill">
                    <div class="number text-info"><?= $encours ?></div>
                    <div class="text-muted small fw-semibold">Traitement<br>(En cours)</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-pill">
                    <div class="number text-success"><?= $cloturee ?></div>
                    <div class="text-muted small fw-semibold">Résolues<br>(Clôturées)</div>
                </div>
            </div>
        </div>

        <!-- Latest Notifications -->
        <div class="notif-box">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-regular fa-bell text-primary me-2"></i>Notifications Récentes
                </h5>
                <a href="notifications.php" class="btn btn-sm btn-light fw-bold rounded-pill px-3">
                    Voir tout
                </a>
            </div>

            <?php if (count($notifications) == 0): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fa-regular fa-face-smile fs-3 d-block mb-2 text-secondary"></i>
                    Aucune notification pour le moment.
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-item-custom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-circle-info text-primary fs-5"></i>
                            <div>
                                <p class="mb-0 text-dark fw-semibold small"><?= htmlspecialchars($n["message"]) ?></p>
                                <span class="text-muted" style="font-size: 0.75rem;"><i class="fa-regular fa-clock me-1"></i><?= $n["created_at"] ?></span>
                            </div>
                        </div>
                        <?php if (isset($n["lu"]) && $n["lu"] == 0): ?>
                            <span class="badge bg-danger rounded-pill">Nouveau</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>