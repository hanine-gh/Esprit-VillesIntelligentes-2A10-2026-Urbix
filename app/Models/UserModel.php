<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UserModel : représente UN utilisateur de la table `users` (base urbix_db).
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class UserModel extends Model
{
    private ?int $id = null;
    private string $nom = '';
    private string $prenom = '';
    private string $email = '';
    private string $password = '';          // hachage password_hash(), jamais le mot de passe en clair
    private ?string $dateNaissance = null;
    private ?string $genre = null;
    private ?string $telephone = null;
    private ?string $typeService = null;
    private string $role = 'user';           // 'user' (frontoffice) ou 'admin' (backoffice)
    private ?string $createdAt = null;

    /**
     * Construit un UserModel à partir d'une ligne de la base de données.
     */
    public static function fromArray(array $row): self
    {
        $user = new self();
        $user->setId(isset($row['id']) ? (int) $row['id'] : null);
        $user->setNom((string) ($row['nom'] ?? ''));
        $user->setPrenom((string) ($row['prenom'] ?? ''));
        $user->setEmail((string) ($row['email'] ?? ''));
        $user->setPassword((string) ($row['password'] ?? ''));
        $user->setDateNaissance($row['date_naissance'] ?? null);
        $user->setGenre($row['genre'] ?? null);
        $user->setTelephone($row['telephone'] ?? null);
        $user->setTypeService($row['type_service'] ?? null);
        $user->setRole((string) ($row['role'] ?? 'user'));
        $user->setCreatedAt($row['created_at'] ?? null);
        return $user;
    }

    // ===== Getters =====
    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getDateNaissance(): ?string { return $this->dateNaissance; }
    public function getGenre(): ?string { return $this->genre; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getTypeService(): ?string { return $this->typeService; }
    public function getRole(): string { return $this->role; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    // ===== Setters =====
    public function setId(?int $id): void { $this->id = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setDateNaissance(?string $dateNaissance): void { $this->dateNaissance = $dateNaissance; }
    public function setGenre(?string $genre): void { $this->genre = $genre; }
    public function setTelephone(?string $telephone): void { $this->telephone = $telephone; }
    public function setTypeService(?string $typeService): void { $this->typeService = $typeService; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setCreatedAt(?string $createdAt): void { $this->createdAt = $createdAt; }
}
