<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN trajet louage entre deux stations, avec son prix — table louage_routes.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class LouageRouteModel extends Model
{
    private ?int $id = null;
    private int $stationDepartId = 0;
    private int $stationArriveeId = 0;
    private ?float $prix = null;
    private string $departNom = '';
    private string $departVille = '';
    private string $arriveeNom = '';
    private string $arriveeVille = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setStationDepartId((int) ($r['station_depart_id'] ?? 0));
        $o->setStationArriveeId((int) ($r['station_arrivee_id'] ?? 0));
        $o->setPrix(isset($r['prix']) && $r['prix'] !== '' ? (float) $r['prix'] : null);
        $o->setDepartNom((string) ($r['depart_nom'] ?? ''));
        $o->setDepartVille((string) ($r['depart_ville'] ?? ''));
        $o->setArriveeNom((string) ($r['arrivee_nom'] ?? ''));
        $o->setArriveeVille((string) ($r['arrivee_ville'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getStationDepartId(): int { return $this->stationDepartId; }
    public function getStationArriveeId(): int { return $this->stationArriveeId; }
    public function getPrix(): ?float { return $this->prix; }
    public function getDepartNom(): string { return $this->departNom; }
    public function getDepartVille(): string { return $this->departVille; }
    public function getArriveeNom(): string { return $this->arriveeNom; }
    public function getArriveeVille(): string { return $this->arriveeVille; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setStationDepartId(int $v): void { $this->stationDepartId = $v; }
    public function setStationArriveeId(int $v): void { $this->stationArriveeId = $v; }
    public function setPrix(?float $v): void { $this->prix = $v; }
    public function setDepartNom(string $v): void { $this->departNom = $v; }
    public function setDepartVille(string $v): void { $this->departVille = $v; }
    public function setArriveeNom(string $v): void { $this->arriveeNom = $v; }
    public function setArriveeVille(string $v): void { $this->arriveeVille = $v; }

    /** Libellé du trajet, ex. « Tunis to Sousse » */
    public function getTrajet(): string
    {
        return $this->departVille . ' to ' . $this->arriveeVille;
    }
}
