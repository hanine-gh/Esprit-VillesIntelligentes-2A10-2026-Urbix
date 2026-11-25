<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN véhicule de location (moto, trottinette, vélo) — table location_vehicules.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class MobiliteVehiculeModel extends Model
{
    private ?int $id = null;
    private string $nom = '';
    private string $type = '';
    private float $tarifHeure = 0;
    private string $autonomie = '';
    private string $station = '';
    private string $statut = '';
    private float $noteMoyenne = 0;
    private int $avisCount = 0;
    private string $image = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setNom((string) ($r['nom'] ?? ''));
        $o->setType((string) ($r['type'] ?? ''));
        $o->setTarifHeure((float) ($r['tarif_heure'] ?? 0));
        $o->setAutonomie((string) ($r['autonomie'] ?? ''));
        $o->setStation((string) ($r['station'] ?? ''));
        $o->setStatut((string) ($r['statut'] ?? ''));
        $o->setNoteMoyenne((float) ($r['note_moyenne'] ?? 0));
        $o->setAvisCount((int) ($r['avis_count'] ?? 0));
        $o->setImage((string) ($r['image'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getType(): string { return $this->type; }
    public function getTarifHeure(): float { return $this->tarifHeure; }
    public function getAutonomie(): string { return $this->autonomie; }
    public function getStation(): string { return $this->station; }
    public function getStatut(): string { return $this->statut; }
    public function getNoteMoyenne(): float { return $this->noteMoyenne; }
    public function getAvisCount(): int { return $this->avisCount; }
    public function getImage(): string { return $this->image; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setNom(string $v): void { $this->nom = $v; }
    public function setType(string $v): void { $this->type = $v; }
    public function setTarifHeure(float $v): void { $this->tarifHeure = $v; }
    public function setAutonomie(string $v): void { $this->autonomie = $v; }
    public function setStation(string $v): void { $this->station = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
    public function setNoteMoyenne(float $v): void { $this->noteMoyenne = $v; }
    public function setAvisCount(int $v): void { $this->avisCount = $v; }
    public function setImage(string $v): void { $this->image = $v; }
}
