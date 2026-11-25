<?php
declare(strict_types=1);

namespace App\Models;

/**
 * TrajetModel : représente UN trajet de bus (table `trajets`).
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 * chauffeurNom / chauffeurPrenom / nbStations / bus* : infos jointes pour l'affichage.
 */
class TrajetModel extends Model
{
    private ?int $id = null;
    private string $code = '';
    private string $nom = '';
    private string $pointDepart = '';
    private string $pointArrivee = '';
    private string $heureDepart = '';
    private string $heureArrivee = '';
    private string $statut = 'actif';
    private ?int $chauffeurId = null;
    private ?int $busId = null;
    private ?string $createdAt = null;

    private ?string $chauffeurNom = null;
    private ?string $chauffeurPrenom = null;
    private int $nbStations = 0;
    private ?string $busMatricule = null;
    private ?string $busMarque = null;
    private ?string $busModele = null;
    private ?int $busCapacite = null;
    private ?string $busStatut = null;

    public static function fromArray(array $row): self
    {
        $t = new self();
        $t->setId(isset($row['id']) ? (int) $row['id'] : null);
        $t->setCode((string) ($row['code'] ?? ''));
        $t->setNom((string) ($row['nom'] ?? ''));
        $t->setPointDepart((string) ($row['point_depart'] ?? ''));
        $t->setPointArrivee((string) ($row['point_arrivee'] ?? ''));
        $t->setHeureDepart(substr((string) ($row['heure_depart'] ?? ''), 0, 5));   // "07:30:00" -> "07:30"
        $t->setHeureArrivee(substr((string) ($row['heure_arrivee'] ?? ''), 0, 5));
        $t->setStatut((string) ($row['statut'] ?? 'actif'));
        $t->setChauffeurId(isset($row['chauffeur_id']) && $row['chauffeur_id'] !== '' ? (int) $row['chauffeur_id'] : null);
        $t->setBusId(isset($row['bus_id']) && $row['bus_id'] !== '' ? (int) $row['bus_id'] : null);
        $t->setCreatedAt($row['created_at'] ?? null);
        $t->setChauffeurNom($row['chauffeur_nom'] ?? null);
        $t->setChauffeurPrenom($row['chauffeur_prenom'] ?? null);
        $t->setNbStations((int) ($row['nb_stations'] ?? 0));
        $t->setBusMatricule($row['bus_matricule'] ?? null);
        $t->setBusMarque($row['bus_marque'] ?? null);
        $t->setBusModele($row['bus_modele'] ?? null);
        $t->setBusCapacite(isset($row['bus_capacite']) ? (int) $row['bus_capacite'] : null);
        $t->setBusStatut($row['bus_statut'] ?? null);
        return $t;
    }

    // ===== Getters =====
    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getNom(): string { return $this->nom; }
    public function getPointDepart(): string { return $this->pointDepart; }
    public function getPointArrivee(): string { return $this->pointArrivee; }
    public function getHeureDepart(): string { return $this->heureDepart; }
    public function getHeureArrivee(): string { return $this->heureArrivee; }
    public function getStatut(): string { return $this->statut; }
    public function getChauffeurId(): ?int { return $this->chauffeurId; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getChauffeurNom(): ?string { return $this->chauffeurNom; }
    public function getChauffeurPrenom(): ?string { return $this->chauffeurPrenom; }
    public function getNbStations(): int { return $this->nbStations; }
    public function getBusId(): ?int { return $this->busId; }
    public function getBusMatricule(): ?string { return $this->busMatricule; }
    public function getBusMarque(): ?string { return $this->busMarque; }
    public function getBusModele(): ?string { return $this->busModele; }
    public function getBusCapacite(): ?int { return $this->busCapacite; }
    public function getBusStatut(): ?string { return $this->busStatut; }

    // ===== Setters =====
    public function setId(?int $id): void { $this->id = $id; }
    public function setCode(string $code): void { $this->code = $code; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPointDepart(string $p): void { $this->pointDepart = $p; }
    public function setPointArrivee(string $p): void { $this->pointArrivee = $p; }
    public function setHeureDepart(string $h): void { $this->heureDepart = $h; }
    public function setHeureArrivee(string $h): void { $this->heureArrivee = $h; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
    public function setChauffeurId(?int $id): void { $this->chauffeurId = $id; }
    public function setCreatedAt(?string $d): void { $this->createdAt = $d; }
    public function setChauffeurNom(?string $n): void { $this->chauffeurNom = $n; }
    public function setChauffeurPrenom(?string $p): void { $this->chauffeurPrenom = $p; }
    public function setNbStations(int $n): void { $this->nbStations = $n; }
    public function setBusId(?int $id): void { $this->busId = $id; }
    public function setBusMatricule(?string $m): void { $this->busMatricule = $m; }
    public function setBusMarque(?string $m): void { $this->busMarque = $m; }
    public function setBusModele(?string $m): void { $this->busModele = $m; }
    public function setBusCapacite(?int $c): void { $this->busCapacite = $c; }
    public function setBusStatut(?string $s): void { $this->busStatut = $s; }
}
