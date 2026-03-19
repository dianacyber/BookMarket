# BookMarket
Site d'annonces de livres entre particuliers

# BookMarket 

BookMarket c'est un site où tu peux vendre et acheter des livres 
entre particuliers, tu postes une annonce, puis si un acheteur est intéressé, vous vous mettez d'accord et vous convenez d'un lieu pour une remise en main propre !


## Comment installer le projet

1. Clone le repo :
```bash
git clone https://github.com/dianacyber/bookmarket.git
```

2. Installe XAMPP et démarre Apache + MySQL

3. Va sur http://localhost/phpmyadmin et crée la base de données 
en important le fichier `bookmarket.sql` qui est à la racine du projet

4. Mets le dossier bookmarket dans `C:/xampp/htdocs/`

5. Va sur http://localhost/bookmarket

## Comment tester

Lance les tests unitaires avec :
```bash
php phpunit-10.5.63.phar tests/ValidationTest.php
```

## Technologies utilisées

- PHP 8
- MySQL
- Bootstrap 5
- PHPUnit

Gakou Diana
