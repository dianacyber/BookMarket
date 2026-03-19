<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookMarket</title>
    <!-- Bootstrap -->
<!-- Bootstrap EN PREMIER -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- CSS APRÈS pour surcharger Bootstrap -->
<link href="/bookmarket/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/bookmarket/index.php">BookMarket</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/bookmarket/pages/annonces.php">Annonces</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/bookmarket/pages/mes_annonces.php">Mes annonces</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bookmarket/pages/favoris.php">Favoris</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bookmarket/pages/messages.php">Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bookmarket/pages/deconnexion.php">Déconnexion</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/bookmarket/pages/inscription.php">Inscription</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/bookmarket/pages/connexion.php">Connexion</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">