<?php
// On inclut le header et la connexion BDD
include('../includes/header.php');
include('../includes/db.php');

// Si l'utilisateur n'est pas connecté on redirige
if(!isset($_SESSION['user_id'])) {
    header('Location: /bookmarket/pages/connexion.php');
    exit();
}

// On marque tous les messages comme lus
$stmt = $pdo->prepare("UPDATE contact SET lu = 1 WHERE id_destinataire = ?");
$stmt->execute([$_SESSION['user_id']]);

// On récupère tous les messages reçus
$stmt = $pdo->prepare("
    SELECT contact.*,
           utilisateur.prenom as expediteur_prenom,
           annonce.titre as titre_annonce,
           livre.titre as titre_livre
    FROM contact
    JOIN utilisateur ON contact.id_expediteur = utilisateur.id_utilisateur
    JOIN annonce ON contact.id_annonce = annonce.id_annonce
    JOIN livre ON annonce.id_livre = livre.id_livre
    WHERE contact.id_destinataire = ?
    ORDER BY contact.date_envoi DESC
");
$stmt->execute([$_SESSION['user_id']]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Mes messages</h2>

        <?php if(empty($messages)): ?>
            <!-- Pas de messages -->
            <div class="alert alert-info text-center">
                <p>Vous n'avez pas encore reçu de messages.</p>
                <a href="/bookmarket/index.php" class="btn btn-primary">
                    Parcourir les annonces
                </a>
            </div>

        <?php else: ?>
            <div class="row">
                <?php foreach($messages as $message): ?>
                <div class="col-12 mb-3">
                    <div class="card <?= $message['lu'] == 0 ? 'border-primary' : '' ?>">
                        <div class="card-header d-flex justify-content-between">
                            <div>
                                <!-- Expéditeur -->
                                <strong>👤 <?= htmlspecialchars($message['expediteur_prenom']) ?></strong>
                                <!-- Annonce concernée -->
                                <span class="text-muted ms-2">
                                    concernant : <?= htmlspecialchars($message['titre_livre']) ?>
                                </span>
                            </div>
                            <div>
                                <!-- Badge non lu -->
                                <?php if($message['lu'] == 0): ?>
                                    <span class="badge bg-primary">Nouveau</span>
                                <?php endif; ?>
                                <!-- Date -->
                                <small class="text-muted ms-2">
                                    <?= date('d/m/Y à H:i', strtotime($message['date_envoi'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Contenu du message -->
                            <p class="card-text"><?= htmlspecialchars($message['message']) ?></p>
                            <!-- Lien vers l'annonce -->
                            <a href="/bookmarket/pages/annonce.php?id=<?= $message['id_annonce'] ?>" 
                               class="btn btn-sm btn-outline-primary">
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