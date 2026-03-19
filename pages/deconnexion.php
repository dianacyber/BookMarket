<?php
// On démarre la session pour pouvoir la détruire
session_start();

// On détruit toutes les variables de session
session_destroy();

// On redirige vers la page d'accueil
header('Location: /bookmarket/index.php');
exit();
?>