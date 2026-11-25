<?php
namespace App\Models;

/**
 * Entité Utilisateur (commune à tous les modules).
 */
class Utilisateur extends BaseModel {

    public const ROLES = [
        'user-normal'         => 'Utilisateur normal',
        'chauffeur-bus'       => 'Chauffeur bus',
        'chauffeur-train'     => 'Chauffeur train',
        'louage'              => 'Louage',
        'covoiturage'         => 'Covoiturage',
        'location'            => 'Location',
        'reparation-vehicule' => 'Réparation de véhicule',
        'admin'               => 'Administrateur',
    ];

    public static function parEmail(string $email): ?array {
        $pdo = self::pdo();
        if ($pdo === null) {
            foreach (DemoStore::data()['utilisateurs'] as $u) {
                if (strcasecmp($u['email'], $email) === 0) {
                    return $u;
                }
            }
            return null;
        }
        $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE email = :e');
        $stmt->execute(['e' => $email]);
        $u = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $u ?: null;
    }

    /** Inscrit un utilisateur et retourne son id. */
    public static function inscrire(array $d): int {
        $hash = password_hash($d['mot_de_passe'], PASSWORD_DEFAULT);
        $pdo = self::pdo();

        if ($pdo === null) {
            $db = &DemoStore::data();
            $id = max(array_keys($db['utilisateurs'])) + 1;
            $db['utilisateurs'][$id] = [
                'id_utilisateur' => $id, 'nom' => $d['nom'], 'prenom' => $d['prenom'], 'email' => $d['email'],
                'mot_de_passe' => $hash, 'date_naissance' => $d['date_naissance'] ?: null,
                'genre' => $d['genre'] ?: null, 'telephone' => $d['telephone'], 'role' => $d['role'],
                'date_inscription' => date('Y-m-d H:i:s'),
            ];
            return $id;
        }

        $stmt = $pdo->prepare('INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, date_naissance, genre, telephone, role)
                               VALUES (:nom, :prenom, :email, :mdp, :naissance, :genre, :tel, :role)');
        $stmt->execute([
            'nom' => $d['nom'], 'prenom' => $d['prenom'], 'email' => $d['email'], 'mdp' => $hash,
            'naissance' => $d['date_naissance'] ?: null, 'genre' => $d['genre'] ?: null,
            'tel' => $d['telephone'], 'role' => $d['role'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** Vérifie l'email et le mot de passe ; retourne l'utilisateur (sans mot de passe) ou null. */
    public static function authentifier(string $email, string $motDePasse): ?array {
        $u = self::parEmail($email);
        if ($u === null) {
            return null;
        }
        $stocke = (string) $u['mot_de_passe'];
        $ok = password_verify($motDePasse, $stocke);

        // Comptes de démo importés depuis le script SQL (mot de passe non haché) :
        // on accepte une fois puis on re-hache immédiatement.
        if (!$ok && strpos($stocke, '$2y$') !== 0 && hash_equals($stocke, $motDePasse)) {
            $ok = true;
            self::changerMotDePasse((int) $u['id_utilisateur'], $motDePasse);
        }
        if (!$ok) {
            return null;
        }
        unset($u['mot_de_passe']);
        return $u;
    }

    private static function changerMotDePasse(int $id, string $motDePasse): void {
        $pdo = self::pdo();
        if ($pdo === null) {
            return;
        }
        $stmt = $pdo->prepare('UPDATE utilisateur SET mot_de_passe = :m WHERE id_utilisateur = :id');
        $stmt->execute(['m' => password_hash($motDePasse, PASSWORD_DEFAULT), 'id' => $id]);
    }
}
