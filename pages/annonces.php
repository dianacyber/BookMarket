<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// On récupère les filtres depuis l'URL
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$id_ville = isset($_GET['id_ville']) ? $_GET['id_ville'] : '';
$id_categorie = isset($_GET['id_categorie']) ? $_GET['id_categorie'] : '';
$etat = isset($_GET['etat']) ? $_GET['etat'] : '';

// On construit la requête dynamiquement selon les filtres
$sql = "
    SELECT annonce.*, 
           livre.titre as titre_livre,
           livre.auteur,
           ville.nom_ville
    FROM annonce
    JOIN livre ON annonce.id_livre = livre.id_livre
    JOIN ville ON annonce.id_ville = ville.id_ville
    WHERE annonce.statut = 'disponible'
";

// Tableau des paramètres pour PDO
$params = [];

// Filtre par recherche (titre du livre ou auteur)
if(!empty($recherche)) {
    $sql .= " AND (livre.titre LIKE ? OR livre.auteur LIKE ?)";
    $params[] = '%' . $recherche . '%';
    $params[] = '%' . $recherche . '%';
}

// Filtre par ville
if(!empty($id_ville)) {
    $sql .= " AND annonce.id_ville = ?";
    $params[] = $id_ville;
}

// Filtre par catégorie
if(!empty($id_categorie)) {
    $sql .= " AND livre.id_livre IN (
        SELECT id_livre FROM livre_categorie WHERE id_categorie = ?
    )";
    $params[] = $id_categorie;
}

// Filtre par état
if(!empty($etat)) {
    $sql .= " AND annonce.etat = ?";
    $params[] = $etat;
}

$sql .= " ORDER BY annonce.date_publication DESC";

// On exécute la requête
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// On récupère les villes et catégories pour les filtres
$villes = $pdo->query("SELECT * FROM ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">

    <!-- Colonne filtres -->
    <div class="col-md-3">
        <div class="card p-3 mb-4">
            <h5>Filtres</h5>
            <form method="GET" action="">

                <!-- Recherche -->
                <div class="mb-3">
                    <label for="recherche" class="form-label">Recherche</label>
                    <input type="text" 
                           class="form-control" 
                           id="recherche"
                           name="recherche" 
                           value="<?= htmlspecialchars($recherche) ?>"
                           placeholder="Titre, auteur...">
                </div>

                <!-- Filtre ville -->
                <div class="mb-3">
                    <label for="id_ville" class="form-label">Ville</label>
                    <select class="form-select" id="id_ville" name="id_ville">
                        <option value="">Toutes les villes</option>
                        <?php foreach($villes as $ville): ?>
                            <option value="<?= $ville['id_ville'] ?>"
                                <?= $id_ville == $ville['id_ville'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ville['nom_ville']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre catégorie -->
                <div class="mb-3">
                    <label for="id_categorie" class="form-label">Catégorie</label>
                    <select class="form-select" id="id_categorie" name="id_categorie">
                        <option value="">Toutes les catégories</option>
                        <?php foreach($categories as $categorie): ?>
                            <option value="<?= $categorie['id_categorie'] ?>"
                                <?= $id_categorie == $categorie['id_categorie'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categorie['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtre état -->
                <div class="mb-3">
                    <label for="etat" class="form-label">État</label>
                    <select class="form-select" id="etat" name="etat">
                        <option value="">Tous les états</option>
                        <option value="neuf" <?= $etat == 'neuf' ? 'selected' : '' ?>>Neuf</option>
                        <option value="bon état" <?= $etat == 'bon état' ? 'selected' : '' ?>>Bon état</option>
                        <option value="usagé" <?= $etat == 'usagé' ? 'selected' : '' ?>>Usagé</option>
                    </select>
                </div>

                <!-- Boutons -->
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    🔍 Rechercher
                </button>
                <a href="/bookmarket/pages/annonces.php" class="btn btn-outline-secondary w-100">
                    Réinitialiser
                </a>

            </form>
        </div>
    </div>

    <!-- Colonne annonces -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Toutes les annonces</h2>
            <!-- Nombre de résultats -->
            <span class="badge bg-secondary fs-6">
                <?= count($annonces) ?> annonce(s)
            </span>
        </div>

        <?php if(empty($annonces)): ?>
            <div class="alert alert-info text-center">
                Aucune annonce trouvée pour ces critères.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($annonces as $annonce): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <!-- Photo -->
                        <?php if($annonce['photo']): ?>
                            <img src="/bookmarket/assets/uploads/<?= htmlspecialchars($annonce['photo']) ?>" 
                                 class="card-img-top"
                                 alt="Photo de <?= htmlspecialchars($annonce['titre_livre']) ?>"
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                                 style="height: 200px;">
                                <p class="mb-0">Pas de photo</p>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($annonce['titre_livre']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($annonce['auteur']) ?></p>
                            <p class="card-text">
                                <strong><?= htmlspecialchars($annonce['prix']) ?> €</strong>
                                <span class="badge bg-secondary"><?= htmlspecialchars($annonce['etat']) ?></span>
                            </p>
                            <p class="card-text">
                                <small>📍 <?= htmlspecialchars($annonce['nom_ville']) ?></small>
                            </p>
                        </div>

                        <div class="card-footer">
                            <a href="/bookmarket/pages/annonce.php?id=<?= $annonce['id_annonce'] ?>" 
                               class="btn btn-primary w-100">
                               Voir l'annonce
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