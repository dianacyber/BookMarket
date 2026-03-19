<?php
include('includes/header.php');
include('includes/db.php');

// Récupérer les dernières annonces
$sql = "SELECT annonce.*, livre.titre as titre_livre, livre.auteur, ville.nom_ville 
        FROM annonce 
        JOIN livre ON annonce.id_livre = livre.id_livre
        JOIN ville ON annonce.id_ville = ville.id_ville
        WHERE annonce.statut = 'disponible'
        ORDER BY annonce.date_publication DESC
        LIMIT 12";

$stmt = $pdo->query($sql);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-center mb-4">📚 Bienvenue sur BookMarket</h1>
<p class="text-center text-muted">Achetez et vendez vos livres entre particuliers</p>

<div class="row">
    <?php foreach($annonces as $annonce): ?>
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <?php if($annonce['photo']): ?>
                <img src="/bookmarket/assets/uploads/<?= htmlspecialchars($annonce['photo']) ?>" 
                     class="card-img-top" 
                     alt="Photo de <?= htmlspecialchars($annonce['titre_livre']) ?>"
                     style="height: 200px; object-fit: cover;">
            <?php else: ?>
                <img src="/bookmarket/assets/img/no-image.png" 
                     class="card-img-top" 
                     alt="Pas de photo disponible"
                     style="height: 200px; object-fit: cover;">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($annonce['titre_livre']) ?></h5>
                <p class="card-text text-muted"><?= htmlspecialchars($annonce['auteur']) ?></p>
                <p class="card-text">
                    <strong><?= htmlspecialchars($annonce['prix']) ?> €</strong>
                    <span class="badge bg-secondary"><?= htmlspecialchars($annonce['etat']) ?></span>
                </p>
                <p class="card-text"><small>📍 <?= htmlspecialchars($annonce['nom_ville']) ?></small></p>
                <a href="/bookmarket/pages/annonce.php?id=<?= $annonce['id_annonce'] ?>" 
                   class="btn btn-primary w-100">Voir l'annonce</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include('includes/footer.php'); ?>