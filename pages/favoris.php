<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// Si l'utilisateur n'est pas connecté on redirige
if(!isset($_SESSION['user_id'])) {
    header('Location: /bookmarket/pages/connexion.php');
    exit();
}

// --- GESTION DES ACTIONS ---

// Ajouter un favori
if(isset($_GET['action']) && $_GET['action'] == 'ajouter' && isset($_GET['id'])) {
    $id_annonce = $_GET['id'];
    
    // On vérifie que l'annonce existe et que c'est pas la sienne
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM annonce WHERE id_annonce = ?");
    $stmt->execute([$id_annonce]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($annonce && $annonce['id_utilisateur'] != $_SESSION['user_id']) {
        // On insère le favori (UNIQUE KEY empêche les doublons)
        try {
            $stmt = $pdo->prepare("INSERT INTO favoris (id_utilisateur, id_annonce) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $id_annonce]);
        } catch(PDOException $e) {
            // Si doublon on ignore l'erreur silencieusement
        }
    }
    
    // On redirige vers la page de l'annonce
    header('Location: /bookmarket/pages/annonce.php?id=' . $id_annonce);
    exit();
}

// Supprimer un favori
if(isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id_annonce = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM favoris WHERE id_utilisateur = ? AND id_annonce = ?");
    $stmt->execute([$_SESSION['user_id'], $id_annonce]);
    
    // On reste sur la page favoris
    header('Location: /bookmarket/pages/favoris.php');
    exit();
}

// --- AFFICHAGE DES FAVORIS ---

// On récupère tous les favoris de l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT annonce.*, 
           livre.titre as titre_livre,
           livre.auteur,
           ville.nom_ville,
           favoris.date_ajout
    FROM favoris
    JOIN annonce ON favoris.id_annonce = annonce.id_annonce
    JOIN livre ON annonce.id_livre = livre.id_livre
    JOIN ville ON annonce.id_ville = ville.id_ville
    WHERE favoris.id_utilisateur = ?
    ORDER BY favoris.date_ajout DESC
");
$stmt->execute([$_SESSION['user_id']]);
$favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Mes favoris</h2>

        <?php if(empty($favoris)): ?>
            <!-- Message si pas de favoris -->
            <div class="alert alert-info text-center">
                <p>Vous n'avez pas encore de favoris.</p>
                <a href="/bookmarket/index.php" class="btn btn-primary">
                    Parcourir les annonces
                </a>
            </div>

        <?php else: ?>
            <div class="row">
                <?php foreach($favoris as $favori): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <!-- Photo -->
                        <?php if($favori['photo']): ?>
                            <img src="/bookmarket/assets/uploads/<?= htmlspecialchars($favori['photo']) ?>" 
                                 class="card-img-top"
                                 alt="Photo de <?= htmlspecialchars($favori['titre_livre']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <p>Pas de photo</p>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <!-- Titre et auteur -->
                            <h5 class="card-title"><?= htmlspecialchars($favori['titre_livre']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($favori['auteur']) ?></p>
                            <!-- Prix et état -->
                            <p class="card-text">
                                <strong><?= htmlspecialchars($favori['prix']) ?> €</strong>
                                <span class="badge bg-secondary"><?= htmlspecialchars($favori['etat']) ?></span>
                            </p>
                            <!-- Ville -->
                            <p class="card-text">
                                <small>📍 <?= htmlspecialchars($favori['nom_ville']) ?></small>
                            </p>
                            <!-- Date ajout favori -->
                            <p class="card-text">
                                <small class="text-muted">
                                    Ajouté le <?= date('d/m/Y', strtotime($favori['date_ajout'])) ?>
                                </small>
                            </p>
                        </div>

                        <div class="card-footer d-flex gap-2">
                            <!-- Bouton voir l'annonce -->
                            <a href="/bookmarket/pages/annonce.php?id=<?= $favori['id_annonce'] ?>" 
                               class="btn btn-primary btn-sm flex-fill">
                               Voir
                            </a>
                            <!-- Bouton supprimer le favori -->
                            <a href="/bookmarket/pages/favoris.php?action=supprimer&id=<?= $favori['id_annonce'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Retirer des favoris ?')">
                               🗑️
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>