<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// On récupère l'id de l'annonce dans l'URL
// Exemple : annonce.php?id=3
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si pas d'id ou id invalide on redirige
    header('Location: /bookmarket/index.php');
    exit();
}

$id_annonce = $_GET['id'];

// On récupère toutes les infos de l'annonce
$stmt = $pdo->prepare("
    SELECT annonce.*, 
           livre.titre as titre_livre, 
           livre.auteur, 
           livre.annee_parution,
           ville.nom_ville,
           ville.code_postal,
           utilisateur.nom as vendeur_nom,
           utilisateur.prenom as vendeur_prenom
    FROM annonce
    JOIN livre ON annonce.id_livre = livre.id_livre
    JOIN ville ON annonce.id_ville = ville.id_ville
    JOIN utilisateur ON annonce.id_utilisateur = utilisateur.id_utilisateur
    WHERE annonce.id_annonce = ?
");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'annonce n'existe pas on redirige
if(!$annonce) {
    header('Location: /bookmarket/index.php');
    exit();
}
?>

<div class="row">
    <!-- Colonne gauche : photo -->
    <div class="col-md-5">
        <?php if($annonce['photo']): ?>
            <img src="/bookmarket/assets/uploads/<?= htmlspecialchars($annonce['photo']) ?>" 
                 class="img-fluid rounded"
                 alt="Photo de <?= htmlspecialchars($annonce['titre_livre']) ?>">
        <?php else: ?>
            <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" 
                 style="height: 300px;">
                <p>Pas de photo disponible</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Colonne droite : infos -->
    <div class="col-md-7">
        <h2><?= htmlspecialchars($annonce['titre_annonce'] ?? $annonce['titre']) ?></h2>
        
        <!-- Prix et état -->
        <div class="d-flex align-items-center gap-3 mb-3">
            <h3 class="text-success"><?= htmlspecialchars($annonce['prix']) ?> €</h3>
            <span class="badge bg-secondary fs-6"><?= htmlspecialchars($annonce['etat']) ?></span>
        </div>

        <!-- Infos livre -->
        <div class="card p-3 mb-3">
            <h5>Infos du livre</h5>
            <p><strong>Titre :</strong> <?= htmlspecialchars($annonce['titre_livre']) ?></p>
            <p><strong>Auteur :</strong> <?= htmlspecialchars($annonce['auteur']) ?></p>
            <?php if($annonce['annee_parution']): ?>
                <p><strong>Année :</strong> <?= htmlspecialchars($annonce['annee_parution']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <?php if($annonce['description']): ?>
            <div class="card p-3 mb-3">
                <h5>Description</h5>
                <p><?= htmlspecialchars($annonce['description']) ?></p>
            </div>
        <?php endif; ?>

        <!-- Vendeur et ville -->
        <div class="card p-3 mb-3">
            <h5>Vendeur</h5>
            <p><strong>Vendeur :</strong> <?= htmlspecialchars($annonce['vendeur_prenom']) ?> <?= htmlspecialchars($annonce['vendeur_nom']) ?></p>
            <p><strong>Ville :</strong> <?= htmlspecialchars($annonce['nom_ville']) ?> (<?= htmlspecialchars($annonce['code_postal']) ?>)</p>
        </div>

        <!-- Boutons actions -->
        <div class="d-flex gap-2">
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $annonce['id_utilisateur']): ?>
                <!-- Bouton contacter vendeur (pas pour son propre annonce) -->
                <a href="/bookmarket/pages/contact.php?id=<?= $annonce['id_annonce'] ?>" 
                   class="btn btn-primary"
                   aria-label="Contacter le vendeur"> 
                    Contacter le vendeur
                </a>
                
                <!-- Bouton ajouter aux favoris -->
                <a href="/bookmarket/pages/favoris.php?action=ajouter&id=<?= $annonce['id_annonce'] ?>" 
                   class="btn btn-outline-warning"
                   aria-label="Ajouter au favoris">
                    Ajouter aux favoris
                </a>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $annonce['id_utilisateur']): ?>
                <!-- Boutons modifier/supprimer pour le propriétaire -->
                <a href="/bookmarket/pages/modifier_annonce.php?id=<?= $annonce['id_annonce'] ?>" 
                   class="btn btn-warning"
                   aria-label="Modifier l'annonce">
                    Modifier
                </a>
                <a href="/bookmarket/pages/supprimer_annonce.php?id=<?= $annonce['id_annonce'] ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('Supprimer cette annonce ?')"
                   aria-label="Supprimer l'annonce" >
                   Supprimer
                </a>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>