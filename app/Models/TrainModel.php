<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN train — table train_trains.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class TrainModel extends Model
{
    private ?int $id = null;
    private string $numero = '';
    private string $stationDebut = '';
    private int $capacite = 0;
    private int $ligneId = 0;
    private string $etat = '';
    private string $ligneNom = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setNumero((string) ($r['numero'] ?? ''));
        $o->setStationDebut((string) ($r['station_debut'] ?? ''));
        $o->setCapacite((int) ($r['capacite'] ?? 0));
        $o->setLigneId((int) ($r['ligne_id'] ?? 0));
        $o->setEtat((string) ($r['etat'] ?? ''));
        $o->setLigneNom((string) ($r['ligne_nom'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getNumero(): string { return $this->numero; }
    public function getStationDebut(): string { return $this->stationDebut; }
    public function getCapacite(): int { return $this->capacite; }
    public function getLigneId(): int { return $this->ligneId; }
    public function getEtat(): string { return $this->etat; }
    public function getLigneNom(): string { return $this->ligneNom; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setNumero(string $v): void { $this->numero = $v; }
    public function setStationDebut(string $v): void { $this->stationDebut = $v; }
    public function setCapacite(int $v): void { $this->capacite = $v; }
    public function setLigneId(int $v): void { $this->ligneId = $v; }
    public function setEtat(string $v): void { $this->etat = $v; }
    public function setLigneNom(string $v): void { $this->ligneNom = $v; }
}
