<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UNE alerte / perturbation du réseau train — table train_alertes.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class TrainAlerteModel extends Model
{
    private ?int $id = null;
    private string $type = '';
    private ?int $trainId = null;
    private int $ligneId = 0;
    private ?string $station = null;
    private ?int $dureeRetard = null;
    private string $dateAlerte = '';
    private ?string $heureAlerte = null;
    private string $description = '';
    private string $etat = '';
    private string $trainNumero = '';
    private string $ligneNom = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setType((string) ($r['type'] ?? ''));
        $o->setTrainId(isset($r['train_id']) && $r['train_id'] !== '' ? (int) $r['train_id'] : null);
        $o->setLigneId((int) ($r['ligne_id'] ?? 0));
        $o->setStation(isset($r['station']) && $r['station'] !== '' ? (string) $r['station'] : null);
        $o->setDureeRetard(isset($r['duree_retard']) && $r['duree_retard'] !== '' ? (int) $r['duree_retard'] : null);
        $o->setDateAlerte((string) ($r['date_alerte'] ?? ''));
        $o->setHeureAlerte(isset($r['heure_alerte']) && $r['heure_alerte'] !== '' ? (string) $r['heure_alerte'] : null);
        $o->setDescription((string) ($r['description'] ?? ''));
        $o->setEtat((string) ($r['etat'] ?? ''));
        $o->setTrainNumero((string) ($r['train_numero'] ?? ''));
        $o->setLigneNom((string) ($r['ligne_nom'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getTrainId(): ?int { return $this->trainId; }
    public function getLigneId(): int { return $this->ligneId; }
    public function getStation(): ?string { return $this->station; }
    public function getDureeRetard(): ?int { return $this->dureeRetard; }
    public function getDateAlerte(): string { return $this->dateAlerte; }
    public function getHeureAlerte(): ?string { return $this->heureAlerte; }
    public function getDescription(): string { return $this->description; }
    public function getEtat(): string { return $this->etat; }
    public function getTrainNumero(): string { return $this->trainNumero; }
    public function getLigneNom(): string { return $this->ligneNom; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setType(string $v): void { $this->type = $v; }
    public function setTrainId(?int $v): void { $this->trainId = $v; }
    public function setLigneId(int $v): void { $this->ligneId = $v; }
    public function setStation(?string $v): void { $this->station = $v; }
    public function setDureeRetard(?int $v): void { $this->dureeRetard = $v; }
    public function setDateAlerte(string $v): void { $this->dateAlerte = $v; }
    public function setHeureAlerte(?string $v): void { $this->heureAlerte = $v; }
    public function setDescription(string $v): void { $this->description = $v; }
    public function setEtat(string $v): void { $this->etat = $v; }
    public function setTrainNumero(string $v): void { $this->trainNumero = $v; }
    public function setLigneNom(string $v): void { $this->ligneNom = $v; }
}
