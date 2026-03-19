<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// Si l'utilisateur n'est pas connecté on redirige
if(!isset($_SESSION['user_id'])) {
    header('Location: /bookmarket/pages/connexion.php');
    exit();
}

// On récupère toutes les annonces de l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT annonce.*, 
           livre.titre as titre_livre,
           livre.auteur,
           ville.nom_ville
    FROM annonce
    JOIN livre ON annonce.id_livre = livre.id_livre
    JOIN ville ON annonce.id_ville = ville.id_ville
    WHERE annonce.id_utilisateur = ?
    ORDER BY annonce.date_publication DESC
");
$stmt->execute([$_SESSION['user_id']]);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📋 Mes annonces</h2>
            <!-- Bouton créer une annonce -->
            <a href="/bookmarket/pages/creer_annonce.php" class="btn btn-primary">
                + Publier une annonce
            </a>
        </div>

        <?php if(empty($annonces)): ?>
            <!-- Message si pas d'annonces -->
            <div class="alert alert-info text-center">
                <p>Vous n'avez pas encore publié d'annonce.</p>
                <a href="/bookmarket/pages/creer_annonce.php" class="btn btn-primary">
                    Publier ma première annonce
                </a>
            </div>

        <?php else: ?>
            <!-- Tableau des annonces -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Livre</th>
                            <th scope="col">Prix</th>
                            <th scope="col">État</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Ville</th>
                            <th scope="col">Date</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($annonces as $annonce): ?>
                        <tr>
                            <!-- Titre du livre -->
                            <td>
                                <strong><?= htmlspecialchars($annonce['titre_livre']) ?></strong>
                                <br>
                                <small class="text-muted"><?= htmlspecialchars($annonce['auteur']) ?></small>
                            </td>
                            <!-- Prix -->
                            <td><?= htmlspecialchars($annonce['prix']) ?> €</td>
                            <!-- État -->
                            <td>
                                <span class="badge bg-secondary">
                                    <?= htmlspecialchars($annonce['etat']) ?>
                                </span>
                            </td>
                            <!-- Statut -->
                            <td>
                                <?php if($annonce['statut'] == 'disponible'): ?>
                                    <span class="badge bg-success">Disponible</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Vendu</span>
                                <?php endif; ?>
                            </td>
                            <!-- Ville -->
                            <td><?= htmlspecialchars($annonce['nom_ville']) ?></td>
                            <!-- Date -->
                            <td><?= date('d/m/Y', strtotime($annonce['date_publication'])) ?></td>
                            <!-- Actions -->
                            <td>
                                <a href="/bookmarket/pages/annonce.php?id=<?= $annonce['id_annonce'] ?>" 
                                   class="btn btn-sm btn-info">Voir</a>
                                <a href="/bookmarket/pages/modifier_annonce.php?id=<?= $annonce['id_annonce'] ?>" 
                                   class="btn btn-sm btn-warning">Modifier</a>
                                <a href="/bookmarket/pages/supprimer_annonce.php?id=<?= $annonce['id_annonce'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Supprimer cette annonce ?')">
                                   🗑️ Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>