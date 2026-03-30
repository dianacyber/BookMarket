CREATE DATABASE IF NOT EXISTS livres_db;
USE livres_db;

-- VILLE
CREATE TABLE ville (
    id_ville INT AUTO_INCREMENT PRIMARY KEY,
    nom_ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10) NOT NULL
);

-- UTILISATEUR
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_ville INT,
    FOREIGN KEY (id_ville) REFERENCES ville(id_ville)
);

-- CATEGORIE
CREATE TABLE categorie (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL
);

-- LIVRE
CREATE TABLE livre (
    id_livre INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    auteur VARCHAR(150) NOT NULL,
    annee_parution YEAR,
    isbn VARCHAR(20)
);

-- LIVRE_CATEGORIE (1,N vers 1,N)
CREATE TABLE livre_categorie (
    id_livre INT,
    id_categorie INT,
    PRIMARY KEY (id_livre, id_categorie),
    FOREIGN KEY (id_livre) REFERENCES livre(id_livre),
    FOREIGN KEY (id_categorie) REFERENCES categorie(id_categorie)
);

-- ANNONCE
CREATE TABLE annonce (
    id_annonce INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    etat ENUM('neuf', 'bon état', 'usagé') NOT NULL,
    photo VARCHAR(255),
    date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('disponible', 'vendu') DEFAULT 'disponible',
    id_utilisateur INT,
    id_livre INT,
    id_ville INT,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur),
    FOREIGN KEY (id_livre) REFERENCES livre(id_livre),
    FOREIGN KEY (id_ville) REFERENCES ville(id_ville)
);

-- FAVORIS (1,N vers 1,N porteuse)
CREATE TABLE favoris (
    id_favori INT AUTO_INCREMENT PRIMARY KEY,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_utilisateur INT,
    id_annonce INT,
    UNIQUE KEY (id_utilisateur, id_annonce),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur),
    FOREIGN KEY (id_annonce) REFERENCES annonce(id_annonce)
);

-- CONTACT
CREATE TABLE contact (
    id_contact INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu TINYINT(1) DEFAULT 0,
    id_expediteur INT,
    id_destinataire INT,
    id_annonce INT,
    FOREIGN KEY (id_expediteur) REFERENCES utilisateur(id_utilisateur),
    FOREIGN KEY (id_destinataire) REFERENCES utilisateur(id_utilisateur),
    FOREIGN KEY (id_annonce) REFERENCES annonce(id_annonce)
);

USE livres_db;


-- UTILISATEURS
INSERT IGNORE INTO utilisateur (nom, prenom, email, mot_de_passe, id_ville) VALUES
('Dupont', 'Marie', 'marie@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('Martin', 'Lucas', 'lucas@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('Bernard', 'Sophie', 'sophie@test.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3);

-- LIVRES
INSERT IGNORE INTO livre (titre, auteur, annee_parution, isbn) VALUES
('Harry Potter et la Sorcière', 'J.K. Rowling', 1997, '9782070541270'),
('Le Petit Prince', 'Antoine de Saint-Exupéry', 1943, '9782070408504'),
('1984', 'George Orwell', 1949, '9782070368228'),
('Da Vinci Code', 'Dan Brown', 2003, '9782709626091'),
('L''Alchimiste', 'Paulo Coelho', 1988, '9782290004449'),
('Hunger Games', 'Suzanne Collins', 2008, '9782266180498'),
('Le Seigneur des Anneaux', 'J.R.R. Tolkien', 1954, '9782267011258'),
('Dune', 'Frank Herbert', 1965, '9782221252055');

-- LIVRE_CATEGORIE
INSERT IGNORE INTO livre_categorie (id_livre, id_categorie) VALUES
(1, 3),
(1, 6),
(2, 6),
(3, 1),
(4, 4),
(4, 8),
(5, 1),
(6, 2),
(6, 6),
(7, 3),
(8, 2);

-- ANNONCES
INSERT IGNORE INTO annonce (titre, description, prix, etat, statut, id_utilisateur, id_livre, id_ville) VALUES
('Harry Potter tome 1 à vendre', 'Très bon état, lu une seule fois', 5.00, 'bon état', 'disponible', 1, 1, 1),
('Le Petit Prince', 'Couverture légèrement abîmée mais intérieur parfait', 3.50, 'usagé', 'disponible', 2, 2, 2),
('1984 de Orwell', 'Neuf, jamais lu, cadeau non désiré', 8.00, 'neuf', 'disponible', 3, 3, 3),
('Da Vinci Code', 'Bon état général', 4.00, 'bon état', 'disponible', 1, 4, 1),
('L''Alchimiste', 'Quelques annotations au crayon', 3.00, 'usagé', 'disponible', 2, 5, 2),
('Hunger Games tome 1', 'Parfait état, acheté récemment', 6.50, 'bon état', 'disponible', 3, 6, 3),
('Le Seigneur des Anneaux', 'Edition collector, très bon état', 15.00, 'bon état', 'disponible', 1, 7, 1),
('Dune', 'Neuf sous blister', 10.00, 'neuf', 'disponible', 2, 8, 2);