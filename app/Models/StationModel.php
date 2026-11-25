<?php
declare(strict_types=1);

namespace App\Models;

/**
 * StationModel : représente UNE station d'un trajet (table `stations`).
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class StationModel extends Model
{
    private ?int $id = null;
    private int $trajetId = 0;
    private string $nom = '';
    private string $adresse = '';
    private int $ordre = 1;
    private string $heurePassage = '';
    private ?string $createdAt = null;

    public static function fromArray(array $row): self
    {
        $s = new self();
        $s->setId(isset($row['id']) ? (int) $row['id'] : null);
        $s->setTrajetId((int) ($row['trajet_id'] ?? 0));
        $s->setNom((string) ($row['nom'] ?? ''));
        $s->setAdresse((string) ($row['adresse'] ?? ''));
        $s->setOrdre((int) ($row['ordre'] ?? 1));
        $s->setHeurePassage(substr((string) ($row['heure_passage'] ?? ''), 0, 5));
        $s->setCreatedAt($row['created_at'] ?? null);
        return $s;
    }

    // ===== Getters =====
    public function getId(): ?int { return $this->id; }
    public function getTrajetId(): int { return $this->trajetId; }
    public function getNom(): string { return $this->nom; }
    public function getAdresse(): string { return $this->adresse; }
    public function getOrdre(): int { return $this->ordre; }
    public function getHeurePassage(): string { return $this->heurePassage; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    // ===== Setters =====
    public function setId(?int $id): void { $this->id = $id; }
    public function setTrajetId(int $id): void { $this->trajetId = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setAdresse(string $adresse): void { $this->adresse = $adresse; }
    public function setOrdre(int $ordre): void { $this->ordre = $ordre; }
    public function setHeurePassage(string $h): void { $this->heurePassage = $h; }
    public function setCreatedAt(?string $d): void { $this->createdAt = $d; }
}
