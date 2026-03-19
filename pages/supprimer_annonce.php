<?php
// On inclut la connexion BDD et le header
session_start();
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

// On récupère l'annonce pour vérifier le propriétaire
$stmt = $pdo->prepare("SELECT * FROM annonce WHERE id_annonce = ?");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'annonce n'existe pas on redirige
if(!$annonce) {
    header('Location: /bookmarket/index.php');
    exit();
}

// Vérification que l'utilisateur est bien le propriétaire
if($annonce['id_utilisateur'] != $_SESSION['user_id']) {
    header('Location: /bookmarket/index.php');
    exit();
}

// On supprime la photo si elle existe
if($annonce['photo']) {
    $chemin_photo = '../assets/uploads/' . $annonce['photo'];
    if(file_exists($chemin_photo)) {
        unlink($chemin_photo); // supprime le fichier
    }
}

// On supprime les favoris liés à cette annonce
$stmt = $pdo->prepare("DELETE FROM favoris WHERE id_annonce = ?");
$stmt->execute([$id_annonce]);

// On supprime les contacts liés à cette annonce
$stmt = $pdo->prepare("DELETE FROM contact WHERE id_annonce = ?");
$stmt->execute([$id_annonce]);

// On supprime l'annonce
$stmt = $pdo->prepare("DELETE FROM annonce WHERE id_annonce = ? AND id_utilisateur = ?");
$stmt->execute([$id_annonce, $_SESSION['user_id']]);

// On redirige vers la page d'accueil
header('Location: /bookmarket/index.php');
exit();
?>