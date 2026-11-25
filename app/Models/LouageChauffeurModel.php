<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN chauffeur de louage — table louage_chauffeurs.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class LouageChauffeurModel extends Model
{
    private ?int $id = null;
    private string $nom = '';
    private ?string $telephone = null;
    private ?string $permis = null;
    private string $statut = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setNom((string) ($r['nom'] ?? ''));
        $o->setTelephone(isset($r['telephone']) && $r['telephone'] !== '' ? (string) $r['telephone'] : null);
        $o->setPermis(isset($r['permis']) && $r['permis'] !== '' ? (string) $r['permis'] : null);
        $o->setStatut((string) ($r['statut'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getTelephone(): ?string { return $this->telephone; }
    public function getPermis(): ?string { return $this->permis; }
    public function getStatut(): string { return $this->statut; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setNom(string $v): void { $this->nom = $v; }
    public function setTelephone(?string $v): void { $this->telephone = $v; }
    public function setPermis(?string $v): void { $this->permis = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
}
