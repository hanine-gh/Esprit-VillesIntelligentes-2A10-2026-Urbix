<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;

/**
 * UserFormTrait : contrôle de saisie et vérification CSRF communs
 * (inscription, modification du profil, gestion des chauffeurs par l'admin).
 * Une seule implémentation = exactement les mêmes règles partout.
 */
trait UserFormTrait
{
    /** Lit un champ texte du formulaire (refuse les tableaux injectés). */
    protected function field(array $post, string $key): string
    {
        $value = $post[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    /** Lit et nettoie les champs du formulaire utilisateur. */
    protected function readUserInput(array $post): array
    {
        return [
            'nom'            => $this->field($post, 'nom'),
            'prenom'         => $this->field($post, 'prenom'),
            'email'          => $this->field($post, 'email'),
            'date_naissance' => $this->field($post, 'date_naissance'),
            'genre'          => $this->field($post, 'genre'),
            'telephone'      => preg_replace('/[\s.\-]/', '', $this->field($post, 'telephone')),
            'type_service'   => $this->field($post, 'type_service'),
        ];
    }

    /** Valide les données personnelles (mêmes règles à la création et à la modification). */
    protected function validateUserInput(array $d): array
    {
        $errors = [];
        $namePattern = "/^\p{L}[\p{L} '\-]{1,49}$/u";

        if (!preg_match($namePattern, $d['nom'])) {
            $errors[] = "Le nom doit contenir 2 à 50 lettres (espaces, apostrophes et tirets autorisés).";
        }
        if (!preg_match($namePattern, $d['prenom'])) {
            $errors[] = "Le prénom doit contenir 2 à 50 lettres (espaces, apostrophes et tirets autorisés).";
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 150) {
            $errors[] = "Adresse email invalide.";
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $d['date_naissance']);
        if (!$date || $date->format('Y-m-d') !== $d['date_naissance']
            || $date > new \DateTimeImmutable('today')
            || $date < new \DateTimeImmutable('1900-01-01')) {
            $errors[] = "Date de naissance invalide.";
        }

        if (!array_key_exists($d['genre'], AuthController::GENRES)) {
            $errors[] = "Veuillez choisir un genre valide.";
        }
        if (!preg_match('/^\+?[0-9]{8,15}$/', $d['telephone'])) {
            $errors[] = "Numéro de téléphone invalide (8 à 15 chiffres).";
        }
        if (!array_key_exists($d['type_service'], AuthController::TYPES_SERVICE)) {
            $errors[] = "Veuillez choisir un type de compte valide.";
        }
        return $errors;
    }

    /** Valide un nouveau mot de passe et sa confirmation. */
    protected function validatePassword(string $password, string $confirm): array
    {
        if (mb_strlen($password) < 8) {
            return ["Le mot de passe doit contenir au moins 8 caractères."];
        }
        if ($password !== $confirm) {
            return ["Les deux mots de passe ne correspondent pas."];
        }
        return [];
    }

    /** Remplit un UserModel avec les données validées. */
    protected function fillUser(UserModel $user, array $d): void
    {
        $user->setNom($d['nom']);
        $user->setPrenom($d['prenom']);
        $user->setEmail($d['email']);
        $user->setDateNaissance($d['date_naissance']);
        $user->setGenre($d['genre']);
        $user->setTelephone($d['telephone']);
        $user->setTypeService($d['type_service']);
    }

    protected function verifyCsrfToken(?string $token): bool
    {
        if (!$token || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
