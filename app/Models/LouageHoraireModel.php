<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN départ planifié d'un louage — table louage_horaires.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class LouageHoraireModel extends Model
{
    private ?int $id = null;
    private int $routeId = 0;
    private ?int $vehiculeId = null;
    private string $departAt = '';
    private ?string $arriveeAt = null;
    private string $departNom = '';
    private string $departVille = '';
    private string $arriveeNom = '';
    private string $arriveeVille = '';
    private ?float $prix = null;
    private ?string $immatriculation = null;
    private ?string $modele = null;
    private int $capacite = 0;
    private ?string $chauffeurNom = null;
    private int $placesReservees = 0;

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setRouteId((int) ($r['route_id'] ?? 0));
        $o->setVehiculeId(isset($r['vehicule_id']) && $r['vehicule_id'] !== '' ? (int) $r['vehicule_id'] : null);
        $o->setDepartAt((string) ($r['depart_at'] ?? ''));
        $o->setArriveeAt(isset($r['arrivee_at']) && $r['arrivee_at'] !== '' ? (string) $r['arrivee_at'] : null);
        $o->setDepartNom((string) ($r['depart_nom'] ?? ''));
        $o->setDepartVille((string) ($r['depart_ville'] ?? ''));
        $o->setArriveeNom((string) ($r['arrivee_nom'] ?? ''));
        $o->setArriveeVille((string) ($r['arrivee_ville'] ?? ''));
        $o->setPrix(isset($r['prix']) && $r['prix'] !== '' ? (float) $r['prix'] : null);
        $o->setImmatriculation(isset($r['immatriculation']) && $r['immatriculation'] !== '' ? (string) $r['immatriculation'] : null);
        $o->setModele(isset($r['modele']) && $r['modele'] !== '' ? (string) $r['modele'] : null);
        $o->setCapacite((int) ($r['capacite'] ?? 0));
        $o->setChauffeurNom(isset($r['chauffeur_nom']) && $r['chauffeur_nom'] !== '' ? (string) $r['chauffeur_nom'] : null);
        $o->setPlacesReservees((int) ($r['places_reservees'] ?? 0));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getRouteId(): int { return $this->routeId; }
    public function getVehiculeId(): ?int { return $this->vehiculeId; }
    public function getDepartAt(): string { return $this->departAt; }
    public function getArriveeAt(): ?string { return $this->arriveeAt; }
    public function getDepartNom(): string { return $this->departNom; }
    public function getDepartVille(): string { return $this->departVille; }
    public function getArriveeNom(): string { return $this->arriveeNom; }
    public function getArriveeVille(): string { return $this->arriveeVille; }
    public function getPrix(): ?float { return $this->prix; }
    public function getImmatriculation(): ?string { return $this->immatriculation; }
    public function getModele(): ?string { return $this->modele; }
    public function getCapacite(): int { return $this->capacite; }
    public function getChauffeurNom(): ?string { return $this->chauffeurNom; }
    public function getPlacesReservees(): int { return $this->placesReservees; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setRouteId(int $v): void { $this->routeId = $v; }
    public function setVehiculeId(?int $v): void { $this->vehiculeId = $v; }
    public function setDepartAt(string $v): void { $this->departAt = $v; }
    public function setArriveeAt(?string $v): void { $this->arriveeAt = $v; }
    public function setDepartNom(string $v): void { $this->departNom = $v; }
    public function setDepartVille(string $v): void { $this->departVille = $v; }
    public function setArriveeNom(string $v): void { $this->arriveeNom = $v; }
    public function setArriveeVille(string $v): void { $this->arriveeVille = $v; }
    public function setPrix(?float $v): void { $this->prix = $v; }
    public function setImmatriculation(?string $v): void { $this->immatriculation = $v; }
    public function setModele(?string $v): void { $this->modele = $v; }
    public function setCapacite(int $v): void { $this->capacite = $v; }
    public function setChauffeurNom(?string $v): void { $this->chauffeurNom = $v; }
    public function setPlacesReservees(int $v): void { $this->placesReservees = $v; }

    /** Places encore libres dans le louage */
    public function getPlacesLibres(): int
    {
        return max(0, $this->capacite - $this->placesReservees);
    }

    /** Libellé du trajet, ex. « Tunis to Sousse » */
    public function getTrajet(): string
    {
        return $this->departVille . ' to ' . $this->arriveeVille;
    }
}
