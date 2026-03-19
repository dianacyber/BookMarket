<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// Si l'utilisateur n'est pas connecté on le redirige
if(!isset($_SESSION['user_id'])) {
    header('Location: /bookmarket/pages/connexion.php');
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

    // Infos du livre
    $titre_livre = trim($_POST['titre_livre']);
    $auteur = trim($_POST['auteur']);
    $annee_parution = $_POST['annee_parution'];
    $id_categorie = $_POST['id_categorie'];

    // --- VALIDATIONS ---
    if(empty($titre_annonce)) {
        $erreurs[] = "Le titre de l'annonce est obligatoire";
    }
    if(empty($titre_livre)) {
        $erreurs[] = "Le titre du livre est obligatoire";
    }
    if(empty($auteur)) {
        $erreurs[] = "L'auteur est obligatoire";
    }
    if(empty($prix) || !is_numeric($prix) || $prix < 0) {
        $erreurs[] = "Le prix est invalide";
    }

    // --- GESTION PHOTO ---
    $photo = null;
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        
        // Extensions autorisées
        $extensions_autorisees = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        
        if(!in_array($extension, $extensions_autorisees)) {
            $erreurs[] = "Format de photo non autorisé (jpg, jpeg, png, webp)";
        } else {
            // On génère un nom unique pour éviter les doublons
            $nom_photo = uniqid() . '.' . $extension;
            $dossier_upload = '../assets/uploads/';
            move_uploaded_file($_FILES['photo']['tmp_name'], $dossier_upload . $nom_photo);
            $photo = $nom_photo;
        }
    }

    // Si pas d'erreurs on insère en base
    if(empty($erreurs)) {

        // On vérifie si le livre existe déjà
        $stmt = $pdo->prepare("SELECT id_livre FROM livre WHERE titre = ? AND auteur = ?");
        $stmt->execute([$titre_livre, $auteur]);
        $livre = $stmt->fetch(PDO::FETCH_ASSOC);

        if($livre) {
            // Le livre existe déjà on récupère son id
            $id_livre = $livre['id_livre'];
        } else {
            // Le livre n'existe pas on le crée
            $stmt = $pdo->prepare("INSERT INTO livre (titre, auteur, annee_parution) VALUES (?, ?, ?)");
            $stmt->execute([$titre_livre, $auteur, $annee_parution]);
            $id_livre = $pdo->lastInsertId();

            // On associe le livre à la catégorie
            $stmt = $pdo->prepare("INSERT INTO livre_categorie (id_livre, id_categorie) VALUES (?, ?)");
            $stmt->execute([$id_livre, $id_categorie]);
        }

        // On insère l'annonce
        $stmt = $pdo->prepare("INSERT INTO annonce 
            (titre, description, prix, etat, photo, id_utilisateur, id_livre, id_ville) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $titre_annonce,
            $description,
            $prix,
            $etat,
            $photo,
            $_SESSION['user_id'],
            $id_livre,
            $id_ville
        ]);

        // On redirige vers la page d'accueil
        header('Location: /bookmarket/index.php');
        exit();
    }
}

// On récupère les villes et catégories pour le formulaire
$villes = $pdo->query("SELECT * FROM ville ORDER BY nom_ville")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="text-center mb-4">Publier une annonce</h2>

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

                <h5 class="mb-3">Informations du livre</h5>

                <!-- Titre du livre -->
                <div class="mb-3">
                    <label for="titre_livre" class="form-label">Titre du livre *</label>
                    <input type="text" class="form-control" id="titre_livre" name="titre_livre" required>
                </div>

                <!-- Auteur -->
                <div class="mb-3">
                    <label for="auteur" class="form-label">Auteur *</label>
                    <input type="text" class="form-control" id="auteur" name="auteur" required>
                </div>

                <!-- Année parution -->
                <div class="mb-3">
                    <label for="annee_parution" class="form-label">Année de parution</label>
                    <input type="number" class="form-control" id="annee_parution" name="annee_parution" min="1900" max="2025">
                </div>

                <!-- Catégorie -->
                <div class="mb-3">
                    <label for="id_categorie" class="form-label">Catégorie *</label>
                    <select class="form-select" id="id_categorie" name="id_categorie" required>
                        <option value="">Choisir une catégorie...</option>
                        <?php foreach($categories as $categorie): ?>
                            <option value="<?= $categorie['id_categorie'] ?>">
                                <?= htmlspecialchars($categorie['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr>
                <h5 class="mb-3">Informations de l'annonce</h5>

                <!-- Titre annonce -->
                <div class="mb-3">
                    <label for="titre_annonce" class="form-label">Titre de l'annonce *</label>
                    <input type="text" class="form-control" id="titre_annonce" name="titre_annonce" required>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" 
                              placeholder="Décrivez l'état de votre livre..."></textarea>
                </div>

                <!-- Prix -->
                <div class="mb-3">
                    <label for="prix" class="form-label">Prix (€) *</label>
                    <input type="number" class="form-control" id="prix" name="prix" 
                           min="0" step="0.01" required>
                </div>

                <!-- Etat -->
                <div class="mb-3">
                    <label for="etat" class="form-label">État du livre *</label>
                    <select class="form-select" id="etat" name="etat" required>
                        <option value="">Choisir un état...</option>
                        <option value="neuf">Neuf</option>
                        <option value="bon état">Bon état</option>
                        <option value="usagé">Usagé</option>
                    </select>
                </div>

                <!-- Photo -->
                <div class="mb-3">
                    <label for="photo" class="form-label">Photo du livre</label>
                    <input type="file" class="form-control" id="photo" name="photo" 
                           accept=".jpg,.jpeg,.png,.webp">
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

                <!-- Bouton publier -->
                <button type="submit" class="btn btn-primary w-100">
                    Publier l'annonce
                </button>

            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>