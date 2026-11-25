<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN louage (véhicule) — table louage_vehicules.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class LouageVehiculeModel extends Model
{
    private ?int $id = null;
    private string $immatriculation = '';
    private string $modele = '';
    private int $capacite = 0;
    private ?int $chauffeurId = null;
    private string $statut = '';
    private ?string $chauffeurNom = null;

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setImmatriculation((string) ($r['immatriculation'] ?? ''));
        $o->setModele((string) ($r['modele'] ?? ''));
        $o->setCapacite((int) ($r['capacite'] ?? 0));
        $o->setChauffeurId(isset($r['chauffeur_id']) && $r['chauffeur_id'] !== '' ? (int) $r['chauffeur_id'] : null);
        $o->setStatut((string) ($r['statut'] ?? ''));
        $o->setChauffeurNom(isset($r['chauffeur_nom']) && $r['chauffeur_nom'] !== '' ? (string) $r['chauffeur_nom'] : null);
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getImmatriculation(): string { return $this->immatriculation; }
    public function getModele(): string { return $this->modele; }
    public function getCapacite(): int { return $this->capacite; }
    public function getChauffeurId(): ?int { return $this->chauffeurId; }
    public function getStatut(): string { return $this->statut; }
    public function getChauffeurNom(): ?string { return $this->chauffeurNom; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setImmatriculation(string $v): void { $this->immatriculation = $v; }
    public function setModele(string $v): void { $this->modele = $v; }
    public function setCapacite(int $v): void { $this->capacite = $v; }
    public function setChauffeurId(?int $v): void { $this->chauffeurId = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
    public function setChauffeurNom(?string $v): void { $this->chauffeurNom = $v; }
}
