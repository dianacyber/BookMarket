<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// Variable pour stocker les erreurs
$erreurs = [];

// On vérifie si le formulaire a été soumis
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // On récupère les données du formulaire
    $email = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];
    
    // Vérification que les champs sont remplis
    if(empty($email) || empty($mot_de_passe)) {
        $erreurs[] = "Tous les champs sont obligatoires";
    } else {
        // On cherche l'utilisateur par son email
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // On vérifie si l'utilisateur existe ET si le mot de passe est correct
        if($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            
            // On stocke les infos de l'utilisateur en session
            $_SESSION['user_id'] = $utilisateur['id_utilisateur'];
            $_SESSION['user_nom'] = $utilisateur['nom'];
            $_SESSION['user_prenom'] = $utilisateur['prenom'];
            
            // On redirige vers la page d'accueil
            header('Location: /bookmarket/index.php');
            exit();
            
        } else {
            // Email ou mot de passe incorrect
            $erreurs[] = "Email ou mot de passe incorrect";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="text-center mb-4">Connexion</h2>
        
        <!-- Affichage des erreurs -->
        <?php if(!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <?php foreach($erreurs as $erreur): ?>
                    <p class="mb-0"><?= htmlspecialchars($erreur) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire connexion -->
        <div class="card p-4">
            <form method="POST" action="">
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email"
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
                </div>
                
                <!-- Bouton connexion -->
                <button type="submit" class="btn btn-primary w-100">
                    Se connecter
                </button>
                
            </form>
            
            <!-- Lien inscription -->
            <p class="text-center mt-3">
                Pas encore de compte ? 
                <a href="/bookmarket/pages/inscription.php">S'inscrire</a>
            </p>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>