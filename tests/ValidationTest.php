<?php
// On importe PHPUnit
use PHPUnit\Framework\TestCase;

// On crée une classe de validation qu'on va tester
class Validation {
    
    // Vérifie la complexité du mot de passe
    public static function validerMotDePasse($mdp) {
        if(strlen($mdp) < 12) return false;
        if(!preg_match('/[A-Z]/', $mdp)) return false;
        if(!preg_match('/[0-9]/', $mdp)) return false;
        if(!preg_match('/[^a-zA-Z0-9]/', $mdp)) return false;
        return true;
    }
    
    // Vérifie qu'un email est valide
    public static function validerEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // Vérifie qu'un prix est valide
    public static function validerPrix($prix) {
        return is_numeric($prix) && $prix >= 0;
    }
}

// La classe de tests
class ValidationTest extends TestCase {
    
    // --- TESTS MOT DE PASSE ---
    
    public function testMotDePasseValide() {
        // Un bon mot de passe doit retourner true
        $this->assertTrue(Validation::validerMotDePasse('Bonjour1234!'));
    }
    
    public function testMotDePasseTropCourt() {
        // Moins de 12 caractères doit retourner false
        $this->assertFalse(Validation::validerMotDePasse('Court1!'));
    }
    
    public function testMotDePasseSansMajuscule() {
        // Sans majuscule doit retourner false
        $this->assertFalse(Validation::validerMotDePasse('bonjour1234!'));
    }
    
    public function testMotDePasseSansChiffre() {
        // Sans chiffre doit retourner false
        $this->assertFalse(Validation::validerMotDePasse('Bonjourtest!'));
    }
    
    public function testMotDePasseSansCaractereSpecial() {
        // Sans caractère spécial doit retourner false
        $this->assertFalse(Validation::validerMotDePasse('Bonjour12345'));
    }
    
    // --- TESTS EMAIL ---
    
    public function testEmailValide() {
        // Un email valide doit retourner true
        $this->assertTrue(Validation::validerEmail('marie@test.fr'));
    }
    
    public function testEmailInvalide() {
        // Un email invalide doit retourner false
        $this->assertFalse(Validation::validerEmail('pasvalide'));
    }
    
    public function testEmailSansArobase() {
        // Sans @ doit retourner false
        $this->assertFalse(Validation::validerEmail('marieattest.fr'));
    }
    
    // --- TESTS PRIX ---
    
    public function testPrixValide() {
        // Un prix positif doit retourner true
        $this->assertTrue(Validation::validerPrix(10.50));
    }
    
    public function testPrixZero() {
        // Un prix à 0 doit retourner true (livre gratuit)
        $this->assertTrue(Validation::validerPrix(0));
    }
    
    public function testPrixNegatif() {
        // Un prix négatif doit retourner false
        $this->assertFalse(Validation::validerPrix(-5));
    }
    
    public function testPrixTexte() {
        // Du texte comme prix doit retourner false
        $this->assertFalse(Validation::validerPrix('abc'));
    }
}