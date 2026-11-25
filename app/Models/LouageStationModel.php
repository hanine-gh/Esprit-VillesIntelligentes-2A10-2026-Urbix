<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UNE station de louage — table louage_stations.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class LouageStationModel extends Model
{
    private ?int $id = null;
    private string $nom = '';
    private string $ville = '';
    private ?string $adresse = null;
    private ?float $latitude = null;
    private ?float $longitude = null;

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setNom((string) ($r['nom'] ?? ''));
        $o->setVille((string) ($r['ville'] ?? ''));
        $o->setAdresse(isset($r['adresse']) && $r['adresse'] !== '' ? (string) $r['adresse'] : null);
        $o->setLatitude(isset($r['latitude']) && $r['latitude'] !== '' ? (float) $r['latitude'] : null);
        $o->setLongitude(isset($r['longitude']) && $r['longitude'] !== '' ? (float) $r['longitude'] : null);
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getVille(): string { return $this->ville; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function getLatitude(): ?float { return $this->latitude; }
    public function getLongitude(): ?float { return $this->longitude; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setNom(string $v): void { $this->nom = $v; }
    public function setVille(string $v): void { $this->ville = $v; }
    public function setAdresse(?string $v): void { $this->adresse = $v; }
    public function setLatitude(?float $v): void { $this->latitude = $v; }
    public function setLongitude(?float $v): void { $this->longitude = $v; }
}
