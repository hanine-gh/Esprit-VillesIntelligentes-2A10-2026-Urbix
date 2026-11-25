<?php
declare(strict_types=1);

namespace App\Models;

/**
 * DemandeStopModel : représente UNE demande de STOP d'un voyageur (table `demandes_stop`).
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 * trajet* / station* : infos jointes pour l'affichage.
 */
class DemandeStopModel extends Model
{
    private ?int $id = null;
    private int $userId = 0;
    private int $trajetId = 0;
    private int $stationId = 0;
    private string $dateTrajet = '';
    private int $nbPersonnes = 1;
    private ?string $message = null;
    private string $statut = 'en_attente';
    private ?string $createdAt = null;

    private ?string $trajetCode = null;
    private ?string $trajetNom = null;
    private ?string $stationNom = null;
    private ?string $stationAdresse = null;
    private ?string $heurePassage = null;

    public static function fromArray(array $row): self
    {
        $d = new self();
        $d->setId(isset($row['id']) ? (int) $row['id'] : null);
        $d->setUserId((int) ($row['user_id'] ?? 0));
        $d->setTrajetId((int) ($row['trajet_id'] ?? 0));
        $d->setStationId((int) ($row['station_id'] ?? 0));
        $d->setDateTrajet((string) ($row['date_trajet'] ?? ''));
        $d->setNbPersonnes((int) ($row['nb_personnes'] ?? 1));
        $d->setMessage($row['message'] ?? null);
        $d->setStatut((string) ($row['statut'] ?? 'en_attente'));
        $d->setCreatedAt($row['created_at'] ?? null);
        $d->setTrajetCode($row['trajet_code'] ?? null);
        $d->setTrajetNom($row['trajet_nom'] ?? null);
        $d->setStationNom($row['station_nom'] ?? null);
        $d->setStationAdresse($row['station_adresse'] ?? null);
        $d->setHeurePassage(isset($row['heure_passage']) ? substr((string) $row['heure_passage'], 0, 5) : null);
        return $d;
    }

    // ===== Getters =====
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTrajetId(): int { return $this->trajetId; }
    public function getStationId(): int { return $this->stationId; }
    public function getDateTrajet(): string { return $this->dateTrajet; }
    public function getNbPersonnes(): int { return $this->nbPersonnes; }
    public function getMessage(): ?string { return $this->message; }
    public function getStatut(): string { return $this->statut; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getTrajetCode(): ?string { return $this->trajetCode; }
    public function getTrajetNom(): ?string { return $this->trajetNom; }
    public function getStationNom(): ?string { return $this->stationNom; }
    public function getStationAdresse(): ?string { return $this->stationAdresse; }
    public function getHeurePassage(): ?string { return $this->heurePassage; }

    // ===== Setters =====
    public function setId(?int $id): void { $this->id = $id; }
    public function setUserId(int $id): void { $this->userId = $id; }
    public function setTrajetId(int $id): void { $this->trajetId = $id; }
    public function setStationId(int $id): void { $this->stationId = $id; }
    public function setDateTrajet(string $d): void { $this->dateTrajet = $d; }
    public function setNbPersonnes(int $n): void { $this->nbPersonnes = $n; }
    public function setMessage(?string $m): void { $this->message = $m; }
    public function setStatut(string $s): void { $this->statut = $s; }
    public function setCreatedAt(?string $d): void { $this->createdAt = $d; }
    public function setTrajetCode(?string $c): void { $this->trajetCode = $c; }
    public function setTrajetNom(?string $n): void { $this->trajetNom = $n; }
    public function setStationNom(?string $n): void { $this->stationNom = $n; }
    public function setStationAdresse(?string $a): void { $this->stationAdresse = $a; }
    public function setHeurePassage(?string $h): void { $this->heurePassage = $h; }
}
