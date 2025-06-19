<?php
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_POST["type"])) {
        $error = "Vous devez choisir un type: Joueur ou Organisateur!";
    } else {
        $prenom = $_POST["prenom"];
        $nom = $_POST["nom"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $type = $_POST["type"];
        
        if ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas!";
        } else {
            $photoPath = null;

            
            if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
                $targetDir = "uploads/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
                $photoPath = $targetDir . $photoName;

                $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                if (in_array($_FILES["photo"]["type"], $allowedTypes)) {
                    move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath);
                } else {
                    $error = "Seuls les fichiers JPG, PNG et GIF sont autorisés";
                }
            }

            
            if (empty($error)) {
                
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                
                $sql = "INSERT INTO Utilisateur (prenom, nom, mot_de_passe, type, photo) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $prenom, $nom, $hashedPassword, $type, $photoPath);

                if ($stmt->execute()) {
                    header("Location: login.php?success=1");
                    exit();
                } else {
                    $error = "Erreur: " . $conn->error;
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .container { max-width: 400px; margin: 50px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .type-selection { margin-bottom: 10px; }
        .type-buttons { display: flex; gap: 10px; margin-bottom: 5px; }
        .type-btn { 
            flex: 1; 
            padding: 12px; 
            border: 2px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
            transition: all 0.3s;
        }
        .type-btn.selected {
            border-color: #2ecc71;
            background: #eafaf1;
        }
        .error-message { 
            color: red; 
            text-align: center; 
            margin: 5px 0 15px;
            font-size: 14px;
            min-height: 20px;
        }
        .btn-submit { 
            background: #e74c3c; 
            color: white; 
            width: 100%; 
            padding: 12px; 
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .login-link { text-align: center; margin-top: 20px; }
        .photo-upload { text-align: center; margin: 15px 0; }
        .photo-preview { 
            width: 120px; 
            height: 120px; 
            border-radius: 50%; 
            object-fit: cover;
            border: 3px solid #eee;
            margin: 0 auto 10px;
            display: none;
        }
        .upload-label {
            display: inline-block;
            padding: 8px 15px;
            background: #3498db;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-label:hover {
            background: #2980b9;
        }
        #photoInput { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Créer un compte</h2>
        
        <form action="register.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: bold;">Vous êtes:</label>
                <div class="type-buttons">
                    <button type="button" class="type-btn" data-type="Participant">Joueur</button>
                    <button type="button" class="type-btn" data-type="Organisateur">Organisateur</button>
                </div>
                <input type="hidden" name="type" id="userType" required>
                <div class="error-message" id="typeError">
                    <?php if ($error && !isset($_POST["type"])) echo $error; ?>
                </div>
            </div>
            
            
            <div class="photo-upload">
                <img id="photoPreview" class="photo-preview" alt="Aperçu photo">
                <label for="photoInput" class="upload-label">Choisir une photo</label>
                <input type="file" name="photo" id="photoInput" accept="image/*">
                <div class="error-message" id="photoError"></div>
            </div>
            
            <div class="form-group">
                <input type="text" name="prenom" placeholder="Prénom" required>
            </div>
            
            <div class="form-group">
                <input type="text" name="nom" placeholder="Nom" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirmer mot de passe" required>
                <div class="error-message" id="passwordError">
                    <?php if ($error && $error !== "Vous devez choisir un type: Joueur ou Organisateur!") echo $error; ?>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">S'inscrire</button>
        </form>
        
        <div class="login-link">
            Déjà un compte? <a href="login.php">Se connecter</a>
        </div>
    </div>

    <script>
        
        const typeButtons = document.querySelectorAll('.type-btn');
        const userTypeInput = document.getElementById('userType');
        const typeError = document.getElementById('typeError');
        const passwordError = document.getElementById('passwordError');
        const photoError = document.getElementById('photoError');
        const photoInput = document.getElementById('photoInput');
        const photoPreview = document.getElementById('photoPreview');
        
        typeButtons.forEach(button => {
            button.addEventListener('click', function() {
                
                typeButtons.forEach(btn => btn.classList.remove('selected'));
                
               
                this.classList.add('selected');
                
                
                userTypeInput.value = this.getAttribute('data-type');
                
                
                typeError.textContent = '';
            });
        });
        
        
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    photoPreview.src = event.target.result;
                    photoPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
                photoError.textContent = '';
            }
        });
        
        
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            
            if (!userTypeInput.value) {
                typeError.textContent = 'Vous devez choisir un type (Joueur ou Organisateur) !';
                isValid = false;
            }
            
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                passwordError.textContent = 'Les mots de passe ne correspondent pas!';
                isValid = false;
            } else {
                passwordError.textContent = '';
            }
            
            
            if (photoInput.files.length > 0) {
                const file = photoInput.files[0];
                const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                if (!allowedTypes.includes(file.type)) {
                    photoError.textContent = 'Seuls les fichiers JPG, PNG et GIF sont autorisés';
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>