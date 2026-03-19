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

// On récupère l'annonce existante
$stmt = $pdo->prepare("
    SELECT annonce.*, livre.titre as titre_livre, livre.auteur, livre.annee_parution
    FROM annonce
    JOIN livre ON annonce.id_livre = livre.id_livre
    WHERE annonce.id_annonce = ?
");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'annonce n'existe pas on redirige
if(!$annonce) {
    header('Location: /bookmarket/index.php');
    exit();
}

// Vérification que l'utilisateur est bien le propriétaire de l'annonce
if($annonce['id_utilisateur'] != $_SESSION['user_id']) {
    header('Location: /bookmarket/index.php');
    exit();
}

// Variable pour stocker les erreurs
$erreurs = [];

// On vérifie si le formulaire a été soumis
if($_SERVER['REQUEST_METHOD'] === 'POST') {

    // On récupère les données du formulaire
    $titre_annonce = trim($_POST['titre_annonce']);
    $description = trim($_POST['description']);
    $prix = $_POST['prix'];
    $etat = $_POST['etat'];
    $id_ville = $_POST['id_ville'];
    $statut = $_POST['statut'];

    // --- VALIDATIONS ---
    if(empty($titre_annonce)) {
        $erreurs[] = "Le titre de l'annonce est obligatoire";
    }
    if(empty($prix) || !is_numeric($prix) || $prix < 0) {
        $erreurs[] = "Le prix est invalide";
    }

    // --- GESTION PHOTO ---
    $photo = $annonce['photo']; // on garde l'ancienne photo par défaut
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        
        $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if(!in_array($extension, $extensions_autorisees)) {
            $erreurs[] = "Format de photo non autorisé (jpg, jpeg, png, webp)";
        } else {
            // On génère un nom unique pour la nouvelle photo
            $nom_photo = uniqid() . '.' . $extension;
            $dossier_upload = '../assets/uploads/';
            move_uploaded_file($_FILES['photo']['tmp_name'], $dossier_upload . $nom_photo);
            $photo = $nom_photo;
        }
    }

    // Si pas d'erreurs on met à jour en base
    if(empty($erreurs)) {
        $stmt = $pdo->prepare("
            UPDATE annonce 
            SET titre = ?, description = ?, prix = ?, etat = ?, photo = ?, statut = ?, id_ville = ?
            WHERE id_annonce = ? AND id_utilisateur = ?
        ");
        $stmt->execute([
            $titre_annonce,
            $description,
            $prix,
            $etat,
            $photo,
            $statut,
            $id_ville,
            $id_annonce,
            $_SESSION['user_id']
        ]);

        // On redirige vers la page de l'annonce
        header('Location: /bookmarket/pages/annonce.php?id=' . $id_annonce);
        exit();
    }
}

// On récupère les villes pour le formulaire
$villes = $pdo->query("SELECT * FROM ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="text-center mb-4">Modifier l'annonce</h2>

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

        <div class="card p-4">
            <form method="POST" action="" enctype="multipart/form-data">

                <!-- Titre annonce -->
                <div class="mb-3">
                    <label for="titre_annonce" class="form-label">Titre de l'annonce *</label>
                    <input type="text" 
                           class="form-control" 
                           id="titre_annonce" 
                           name="titre_annonce" 
                           value="<?= htmlspecialchars($annonce['titre']) ?>"
                           required>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" 
                              id="description" 
                              name="description" 
                              rows="3"><?= htmlspecialchars($annonce['description'] ?? '') ?></textarea>
                </div>

                <!-- Prix -->
                <div class="mb-3">
                    <label for="prix" class="form-label">Prix (€) *</label>
                    <input type="number" 
                           class="form-control" 
                           id="prix" 
                           name="prix" 
                           value="<?= htmlspecialchars($annonce['prix']) ?>"
                           min="0" step="0.01" required>
                </div>

                <!-- Etat -->
                <div class="mb-3">
                    <label for="etat" class="form-label">État du livre *</label>
                    <select class="form-select" id="etat" name="etat" required>
                        <option value="neuf" <?= $annonce['etat'] == 'neuf' ? 'selected' : '' ?>>Neuf</option>
                        <option value="bon état" <?= $annonce['etat'] == 'bon état' ? 'selected' : '' ?>>Bon état</option>
                        <option value="usagé" <?= $annonce['etat'] == 'usagé' ? 'selected' : '' ?>>Usagé</option>
                    </select>
                </div>

                <!-- Statut -->
                <div class="mb-3">
                    <label for="statut" class="form-label">Statut *</label>
                    <select class="form-select" id="statut" name="statut" required>
                        <option value="disponible" <?= $annonce['statut'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="vendu" <?= $annonce['statut'] == 'vendu' ? 'selected' : '' ?>>Vendu</option>
                    </select>
                </div>

                <!-- Photo actuelle -->
                <?php if($annonce['photo']): ?>
                    <div class="mb-3">
                        <label class="form-label">Photo actuelle</label>
                        <img src="/bookmarket/assets/uploads/<?= htmlspecialchars($annonce['photo']) ?>" 
                             style="height: 150px; object-fit: cover;"
                             alt="Photo actuelle"
                             class="d-block rounded mb-2">
                    </div>
                <?php endif; ?>

                <!-- Nouvelle photo -->
                <div class="mb-3">
                    <label for="photo" class="form-label">Changer la photo</label>
                    <input type="file" class="form-control" id="photo" name="photo" 
                           accept=".jpg,.jpeg,.png,.webp">
                </div>

                <!-- Ville -->
                <div class="mb-3">
                    <label for="id_ville" class="form-label">Ville *</label>
                    <select class="form-select" id="id_ville" name="id_ville" required>
                        <?php foreach($villes as $ville): ?>
                            <option value="<?= $ville['id_ville'] ?>" 
                                <?= $annonce['id_ville'] == $ville['id_ville'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ville['nom_ville']) ?>
                                (<?= htmlspecialchars($ville['code_postal']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Bouton modifier -->
                <button type="submit" class="btn btn-warning w-100">
                    Modifier l'annonce
                </button>

            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>