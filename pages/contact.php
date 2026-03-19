<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// Si l'utilisateur n'est pas connecté on redirige
if(!isset($_SESSION['user_id'])) {
    header('Location: /bookmarket/pages/connexion.php');
    exit();
}

// On récupère l'id de l'annonce dans l'URL
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: /bookmarket/index.php');
    exit();
}

$id_annonce = $_GET['id'];

// On récupère les infos de l'annonce et du vendeur
$stmt = $pdo->prepare("
    SELECT annonce.*, 
           livre.titre as titre_livre,
           utilisateur.id_utilisateur as vendeur_id,
           utilisateur.prenom as vendeur_prenom
    FROM annonce
    JOIN livre ON annonce.id_livre = livre.id_livre
    JOIN utilisateur ON annonce.id_utilisateur = utilisateur.id_utilisateur
    WHERE annonce.id_annonce = ?
");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

// Si annonce inexistante on redirige
if(!$annonce) {
    header('Location: /bookmarket/index.php');
    exit();
}

// On peut pas contacter pour sa propre annonce
if($annonce['vendeur_id'] == $_SESSION['user_id']) {
    header('Location: /bookmarket/index.php');
    exit();
}

$erreurs = [];
$succes = false;

// On vérifie si le formulaire a été soumis
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $message = trim($_POST['message']);
    
    // Validation du message
    if(empty($message)) {
        $erreurs[] = "Le message est obligatoire";
    }
    if(strlen($message) < 10) {
        $erreurs[] = "Le message doit contenir au moins 10 caractères";
    }
    
    // Si pas d'erreurs on insère le message
    if(empty($erreurs)) {
        $stmt = $pdo->prepare("
            INSERT INTO contact (message, id_expediteur, id_destinataire, id_annonce)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $message,
            $_SESSION['user_id'],      // celui qui envoie
            $annonce['vendeur_id'],     // le vendeur
            $id_annonce
        ]);
        
        $succes = true;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mb-4">Contacter le vendeur</h2>

        <!-- Infos de l'annonce -->
        <div class="alert alert-info mb-4">
            <strong>Annonce :</strong> <?= htmlspecialchars($annonce['titre_livre']) ?><br>
            <strong>Vendeur :</strong> <?= htmlspecialchars($annonce['vendeur_prenom']) ?>
        </div>

        <!-- Message de succès -->
        <?php if($succes): ?>
            <div class="alert alert-success">
                Votre message a bien été envoyé au vendeur !
                <a href="/bookmarket/pages/annonce.php?id=<?= $id_annonce ?>" 
                   class="btn btn-primary btn-sm ms-2">
                   Retour à l'annonce
                </a>
            </div>
        <?php endif; ?>

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

        <!-- Formulaire contact -->
        <?php if(!$succes): ?>
        <div class="card p-4">
            <form method="POST" action="">
                
                <!-- Message -->
                <div class="mb-3">
                    <label for="message" class="form-label">Votre message *</label>
                    <textarea class="form-control" 
                              id="message" 
                              name="message" 
                              rows="5"
                              placeholder="Bonjour, je suis intéressé par votre livre..."
                              required></textarea>
                    <div class="form-text">Minimum 10 caractères</div>
                </div>

                <!-- Bouton envoyer -->
                <button type="submit" class="btn btn-primary w-100">
                    Envoyer le message
                </button>

            </form>
        </div>
        <?php endif; ?>

        <!-- Lien retour -->
        <div class="text-center mt-3">
            <a href="/bookmarket/pages/annonce.php?id=<?= $id_annonce ?>">
                ← Retour à l'annonce
            </a>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>