<?php
include 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Participant") {
    header("Location: login.php");
    exit();
}

// Vérifier si l'ID du tournoi est présent
if (!isset($_GET['id'])) {
    header("Location: find_tournoi.php");
    exit();
}

$tournoi_id = $_GET['id'];

// Récupérer les infos du tournoi
$sql_tournoi = "SELECT t.*, u.prenom, u.nom 
                FROM Tournoi t
                JOIN Utilisateur u ON t.id_organisateur = u.id_utilisateur
                WHERE t.id_tournoi = ?";
$stmt = $conn->prepare($sql_tournoi);
$stmt->bind_param("i", $tournoi_id);
$stmt->execute();
$tournoi = $stmt->get_result()->fetch_assoc();

if (!$tournoi) {
    header("Location: find_tournoi.php");
    exit();
}

// Récupérer les équipes avec leur nombre de joueurs actuels
$sql_equipes = "SELECT e.id_equipe, e.nom, 
                COUNT(p.id_participation) as nb_joueurs,
                t.nombre_joueurs_par_equipe
                FROM Equipe e
                LEFT JOIN Participation p ON e.id_equipe = p.id_equipe
                JOIN Tournoi t ON e.id_tournoi = t.id_tournoi
                WHERE e.id_tournoi = ?
                GROUP BY e.id_equipe";
$stmt = $conn->prepare($sql_equipes);
$stmt->bind_param("i", $tournoi_id);
$stmt->execute();
$equipes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Vérifier si l'utilisateur est déjà inscrit à ce tournoi
$sql_check = "SELECT p.id_participation 
              FROM Participation p
              JOIN Equipe e ON p.id_equipe = e.id_equipe
              WHERE p.id_utilisateur = ? AND e.id_tournoi = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $_SESSION["user_id"], $tournoi_id);
$stmt->execute();
$deja_inscrit = $stmt->get_result()->fetch_assoc();

// Traitement du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$deja_inscrit) {
    $equipe_id = $_POST["equipe"];
    
    // Vérifier que l'équipe a encore de la place
    $sql_check_equipe = "SELECT COUNT(p.id_participation) as nb_joueurs,
                        t.nombre_joueurs_par_equipe
                        FROM Equipe e
                        LEFT JOIN Participation p ON e.id_equipe = p.id_equipe
                        JOIN Tournoi t ON e.id_tournoi = t.id_tournoi
                        WHERE e.id_equipe = ?
                        GROUP BY e.id_equipe";
    $stmt = $conn->prepare($sql_check_equipe);
    $stmt->bind_param("i", $equipe_id);
    $stmt->execute();
    $equipe_info = $stmt->get_result()->fetch_assoc();
    
    if ($equipe_info && $equipe_info['nb_joueurs'] < $equipe_info['nombre_joueurs_par_equipe']) {
        // Inscrire l'utilisateur
        $sql_inscription = "INSERT INTO Participation (id_utilisateur, id_equipe) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_inscription);
        $stmt->bind_param("ii", $_SESSION["user_id"], $equipe_id);
        
        if ($stmt->execute()) {
            header("Location: find_tournoi.php?success=1");
            exit();
        } else {
            $error = "Erreur lors de l'inscription: " . $conn->error;
        }
    } else {
        $error = "Cette équipe est déjà complète!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription au tournoi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .tournoi-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .equipe-list {
            margin: 20px 0;
        }
        .equipe-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 5px;
            align-items: center;
        }
        .equipe-name {
            font-weight: bold;
        }
        .equipe-status {
            color: #666;
        }
        .equipe-full {
            background: #ffebee;
            color: #e74c3c;
        }
        .equipe-available {
            background: #e8f5e9;
        }
        .btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin: 10px 0;
        }
        .success {
            color: #27ae60;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="find_tournoi.php" class="back-link">← Retour aux tournois</a>
        <h1>Inscription au tournoi</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Inscription réussie!</div>
        <?php endif; ?>
        
        <div class="tournoi-info">
            <div class="info-item"><strong>Tournoi:</strong> <?= htmlspecialchars($tournoi['nom']) ?></div>
            <div class="info-item"><strong>Lieu:</strong> <?= htmlspecialchars($tournoi['lieu']) ?></div>
            <div class="info-item"><strong>Date:</strong> <?= date('d/m/Y', strtotime($tournoi['date_heure'])) ?></div>
            <div class="info-item"><strong>Heure:</strong> <?= date('H:i', strtotime($tournoi['date_heure'])) ?></div>
            <div class="info-item"><strong>Joueurs par équipe:</strong> <?= $tournoi['nombre_joueurs_par_equipe'] ?></div>
        </div>
        
        <?php if ($deja_inscrit): ?>
            <div class="error">Vous êtes déjà inscrit à ce tournoi!</div>
        <?php else: ?>
            <form method="POST" action="inscription_tournoi.php?id=<?= $tournoi_id ?>">
                <h3>Choisissez une équipe:</h3>
                <div class="equipe-list">
                    <?php foreach ($equipes as $equipe): 
                        $is_full = $equipe['nb_joueurs'] >= $equipe['nombre_joueurs_par_equipe'];
                    ?>
                        <div class="equipe-item <?= $is_full ? 'equipe-full' : 'equipe-available' ?>">
                            <div>
                                <span class="equipe-name"><?= htmlspecialchars($equipe['nom']) ?></span>
                                <span class="equipe-status">
                                    (<?= $equipe['nb_joueurs'] ?>/<?= $equipe['nombre_joueurs_par_equipe'] ?> joueurs)
                                </span>
                            </div>
                            <div>
                                <?php if ($is_full): ?>
                                    <span>COMPLET</span>
                                <?php else: ?>
                                    <input type="radio" name="equipe" value="<?= $equipe['id_equipe'] ?>" required>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (count($equipes) > 0): ?>
                    <button type="submit" class="btn">Confirmer l'inscription</button>
                <?php else: ?>
                    <div class="error">Aucune équipe disponible pour ce tournoi</div>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>