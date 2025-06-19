<?php
include 'config.php';


if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Organisateur") {
    header("Location: login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $lieu = $_POST["lieu"];
    $date = $_POST["date"];
    $heure = $_POST["heure"];
    $nb_equipes = $_POST["nb_equipes"];
    $nb_joueurs = $_POST["nb_joueurs"];
    $genre = $_POST["genre"];
    $frais = $_POST["frais"];
    
    $date_heure = $date . " " . $heure . ":00";
    
    $sql = "INSERT INTO Tournoi (nom, lieu, date_heure, nombre_equipes, nombre_joueurs_par_equipe, genre_participants, participation_fee, id_organisateur) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiissi", $nom, $lieu, $date_heure, $nb_equipes, $nb_joueurs, $genre, $frais, $_SESSION["user_id"]);
    
    if ($stmt->execute()) {
        header("Location: mes_tournois.php?success=1");
        exit();
    } else {
        $error = "Erreur: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Créer un tournoi</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #e74c3c; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .error { color: red; text-align: center; margin-bottom: 15px; }
        .back-link { display: inline-block; margin-top: 20px; color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Créer un nouveau tournoi</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="creer_tournoi.php" method="POST">
            <div class="form-group">
                <label for="nom">Nom du tournoi</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="lieu">Lieu</label>
                <input type="text" id="lieu" name="lieu" required>
            </div>
            
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            
            <div class="form-group">
                <label for="heure">Heure</label>
                <select id="heure" name="heure" required>
                    <option value="13:00">13:00</option>
                    <option value="14:00">14:00</option>
                    <option value="15:00">15:00</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                    <option value="18:00">18:00</option>
                    <option value="19:00">19:00</option>
                    <option value="20:00">20:00</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nb_equipes">Nombre d'équipes</label>
                <select id="nb_equipes" name="nb_equipes" required>
                    <option value="4">4</option>
                    <option value="8">8</option>
                    <option value="16">16</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nb_joueurs">Nombre de joueurs par équipe</label>
                <select id="nb_joueurs" name="nb_joueurs" required>
                    <option value="5">5</option>
                    <option value="7">7</option>
                    <option value="11">11</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Genre des participants</label>
                <div style="display: flex; gap: 15px;">
                    <label><input type="radio" name="genre" value="Masculin" checked> Masculin</label>
                    <label><input type="radio" name="genre" value="Féminin"> Féminin</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="frais">Frais de participation (DH)</label>
                <select id="frais" name="frais" required>
                    <option value="25">25</option>
                    <option value="30">30</option>
                    <option value="40">40</option>
                    <option value="50">50</option>
                    <option value="60">60</option>
                    <option value="70">70</option>
                    <option value="80">80</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Créer le tournoi</button>
            <a href="dashboard_org.php" class="back-link">← Retour au tableau de bord</a>
        </form>
    </div>

    <script>
        
        document.getElementById("date").min = new Date().toISOString().split("T")[0];
    </script>
</body>
</html>