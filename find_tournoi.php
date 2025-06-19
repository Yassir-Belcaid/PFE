<?php
include 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Participant") {
    header("Location: login.php");
    exit();
}

// Récupérer tous les tournois disponibles
$sql = "SELECT t.*, u.prenom, u.nom 
        FROM Tournoi t
        JOIN Utilisateur u ON t.id_organisateur = u.id_utilisateur
        ORDER BY t.date_heure DESC";
$result = $conn->query($sql);
$tournois = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trouver un Tournoi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #3498db;
            text-decoration: none;
        }
        .tournoi-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .tournoi-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .tournoi-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .tournoi-info {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .info-item {
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
        }
        .organisateur {
            font-style: italic;
            color: #7f8c8d;
        }
        .btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .btn:hover {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard_joueur.php" class="back-link">← Retour au tableau de bord</a>
        <h1>Tournois Disponibles</h1>
        
        <?php if (empty($tournois)): ?>
            <p>Aucun tournoi disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach ($tournois as $tournoi): ?>
                <div class="tournoi-card">
                    <div class="tournoi-title"><?= htmlspecialchars($tournoi['nom']) ?></div>
                    <div class="tournoi-info">
                        <span class="info-item">Lieu: <?= htmlspecialchars($tournoi['lieu']) ?></span>
                        <span class="info-item">Date: <?= date('d/m/Y', strtotime($tournoi['date_heure'])) ?></span>
                        <span class="info-item">Heure: <?= date('H:i', strtotime($tournoi['date_heure'])) ?></span>
                        <span class="info-item">Équipes: <?= $tournoi['nombre_equipes'] ?></span>
                        <span class="info-item">Joueurs/équipe: <?= $tournoi['nombre_joueurs_par_equipe'] ?></span>
                    </div>
                    <div class="organisateur">
                        Organisé par: <?= htmlspecialchars($tournoi['prenom']) ?> <?= htmlspecialchars($tournoi['nom']) ?>
                    </div>
                    <a href="inscription_tournoi.php?id=<?= $tournoi['id_tournoi'] ?>" class="btn">S'inscrire</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>