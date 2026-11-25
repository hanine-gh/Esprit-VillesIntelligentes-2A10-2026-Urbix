<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UNE ligne ferroviaire — table train_lignes.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class TrainLigneModel extends Model
{
    private ?int $id = null;
    private string $nom = '';
    private string $stationDepart = '';
    private string $stationArrivee = '';
    private string $stationsIntermediaires = '';
    private ?float $distance = null;
    private string $etat = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setNom((string) ($r['nom'] ?? ''));
        $o->setStationDepart((string) ($r['station_depart'] ?? ''));
        $o->setStationArrivee((string) ($r['station_arrivee'] ?? ''));
        $o->setStationsIntermediaires((string) ($r['stations_intermediaires'] ?? ''));
        $o->setDistance(isset($r['distance']) && $r['distance'] !== '' ? (float) $r['distance'] : null);
        $o->setEtat((string) ($r['etat'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getStationDepart(): string { return $this->stationDepart; }
    public function getStationArrivee(): string { return $this->stationArrivee; }
    public function getStationsIntermediaires(): string { return $this->stationsIntermediaires; }
    public function getDistance(): ?float { return $this->distance; }
    public function getEtat(): string { return $this->etat; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setNom(string $v): void { $this->nom = $v; }
    public function setStationDepart(string $v): void { $this->stationDepart = $v; }
    public function setStationArrivee(string $v): void { $this->stationArrivee = $v; }
    public function setStationsIntermediaires(string $v): void { $this->stationsIntermediaires = $v; }
    public function setDistance(?float $v): void { $this->distance = $v; }
    public function setEtat(string $v): void { $this->etat = $v; }

    /** Itinéraire complet : départ, stations intermédiaires, arrivée */
    public function getItineraire(): array
    {
        $milieu = array_filter(array_map('trim', explode(',', $this->stationsIntermediaires)), 'strlen');
        return array_values(array_merge([$this->stationDepart], $milieu, [$this->stationArrivee]));
    }
}
