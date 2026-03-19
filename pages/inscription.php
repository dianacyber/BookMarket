<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// Variable pour stocker les erreurs
$erreurs = [];
// Variable pour stocker les anciennes valeurs du formulaire
$anciens_valeurs = [];

// On vérifie si le formulaire a été soumis
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On récupère et nettoie les données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm_mdp = $_POST['confirm_mdp'];
    $id_ville = $_POST['id_ville'];
    
    // On garde les anciennes valeurs au cas où il y a des erreurs
    $anciens_valeurs = ['nom' => $nom, 'prenom' => $prenom, 'email' => $email];
    
    // --- VALIDATIONS ---
    
    // Vérification nom
    if(empty($nom)) {
        $erreurs[] = "Le nom est obligatoire";
    }
    
    // Vérification prénom
    if(empty($prenom)) {
        $erreurs[] = "Le prénom est obligatoire";
    }
    
    // Vérification email
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'email est invalide";
    }
    
    // Vérification complexité mot de passe 
    if(strlen($mot_de_passe) < 12) {
        $erreurs[] = "Le mot de passe doit contenir au moins 12 caractères";
    }
    if(!preg_match('/[A-Z]/', $mot_de_passe)) {
        $erreurs[] = "Le mot de passe doit contenir au moins une majuscule";
    }
    if(!preg_match('/[0-9]/', $mot_de_passe)) {
        $erreurs[] = "Le mot de passe doit contenir au moins un chiffre";
    }
    if(!preg_match('/[^a-zA-Z0-9]/', $mot_de_passe)) {
        $erreurs[] = "Le mot de passe doit contenir au moins un caractère spécial";
    }
    
    // Vérification confirmation mot de passe
    if($mot_de_passe !== $confirm_mdp) {
        $erreurs[] = "Les mots de passe ne correspondent pas";
    }
    
    // Vérification email déjà utilisé
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->fetch()) {
        $erreurs[] = "Cet email est déjà utilisé";
    }
    
    // Si pas d'erreurs on insère en base
    if(empty($erreurs)) {
        // On chiffre le mot de passe
        $mdp_chiffre = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        // On insère l'utilisateur
        $stmt = $pdo->prepare("INSERT INTO utilisateur 
            (nom, prenom, email, mot_de_passe, id_ville) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $mdp_chiffre, $id_ville]);
        
        // On redirige vers la page connexion
        header('Location: /bookmarket/pages/connexion.php');
        exit();
    }
}

// On récupère les villes pour le formulaire
$villes = $pdo->query("SELECT * FROM ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="text-center mb-4">Inscription</h2>
        
        <!-- Affichage des erreurs -->
        <?php if(!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($erreurs as $erreur): ?>
                        <li><?= htmlspecialchars($erreur) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire inscription -->
        <div class="card p-4">
            <form method="POST" action="">
                
                <!-- Nom -->
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom *</label>
                    <input type="text" 
                           class="form-control" 
                           id="nom" 
                           name="nom" 
                           value="<?= htmlspecialchars($anciens_valeurs['nom'] ?? '') ?>"
                           required>
                </div>
                
                <!-- Prénom -->
                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom *</label>
                    <input type="text" 
                           class="form-control" 
                           id="prenom" 
                           name="prenom" 
                           value="<?= htmlspecialchars($anciens_valeurs['prenom'] ?? '') ?>"
                           required>
                </div>
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($anciens_valeurs['email'] ?? '') ?>"
                           required>
                </div>
                
                <!-- Mot de passe -->
                <div class="mb-3">
                    <label for="mot_de_passe" class="form-label">Mot de passe *</label>
                    <input type="password" 
                           class="form-control" 
                           id="mot_de_passe" 
                           name="mot_de_passe"
                           required>
                    <!-- Indications complexité mot de passe -->
                    <div class="form-text">
                        Le mot de passe doit contenir : 12 caractères minimum, 
                        une majuscule, un chiffre, un caractère spécial
                    </div>
                </div>
                
                <!-- Confirmation mot de passe -->
                <div class="mb-3">
                    <label for="confirm_mdp" class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_mdp" 
                           name="confirm_mdp"
                           required>
                </div>
                
                <!-- Ville -->
                <div class="mb-3">
                    <label for="id_ville" class="form-label">Ville *</label>
                    <select class="form-select" id="id_ville" name="id_ville" required>
                        <option value="">Choisir une ville...</option>
                        <?php foreach($villes as $ville): ?>
                            <option value="<?= $ville['id_ville'] ?>">
                                <?= htmlspecialchars($ville['nom_ville']) ?> 
                                (<?= htmlspecialchars($ville['code_postal']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Bouton inscription -->
                <button type="submit" class="btn btn-primary w-100">
                    S'inscrire
                </button>
                
            </form>
            
            <!-- Lien connexion -->
            <p class="text-center mt-3">
                Déjà un compte ? 
                <a href="/bookmarket/pages/connexion.php">Se connecter</a>
            </p>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>