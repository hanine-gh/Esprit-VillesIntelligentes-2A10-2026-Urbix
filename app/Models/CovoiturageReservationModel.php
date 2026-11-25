<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UNE réservation de covoiturage (+ infos du trajet) — table covoiturage_reservations.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class CovoiturageReservationModel extends Model
{
    private ?int $id = null;
    private int $trajetId = 0;
    private int $passagerId = 0;
    private int $nbPlaces = 0;
    private float $prixTotal = 0;
    private string $statut = '';
    private string $dateReservation = '';
    private string $villeDepart = '';
    private string $villeArrivee = '';
    private string $dateDepart = '';
    private string $heureDepart = '';
    private ?string $pointRencontre = null;
    private string $conducteurNom = '';
    private string $conducteurPrenom = '';
    private ?string $conducteurTelephone = null;
    private ?string $vehiculeMarque = null;
    private ?string $vehiculeModele = null;

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setTrajetId((int) ($r['trajet_id'] ?? 0));
        $o->setPassagerId((int) ($r['passager_id'] ?? 0));
        $o->setNbPlaces((int) ($r['nb_places'] ?? 0));
        $o->setPrixTotal((float) ($r['prix_total'] ?? 0));
        $o->setStatut((string) ($r['statut'] ?? ''));
        $o->setDateReservation((string) ($r['date_reservation'] ?? ''));
        $o->setVilleDepart((string) ($r['ville_depart'] ?? ''));
        $o->setVilleArrivee((string) ($r['ville_arrivee'] ?? ''));
        $o->setDateDepart((string) ($r['date_depart'] ?? ''));
        $o->setHeureDepart((string) ($r['heure_depart'] ?? ''));
        $o->setPointRencontre(isset($r['point_rencontre']) && $r['point_rencontre'] !== '' ? (string) $r['point_rencontre'] : null);
        $o->setConducteurNom((string) ($r['conducteur_nom'] ?? ''));
        $o->setConducteurPrenom((string) ($r['conducteur_prenom'] ?? ''));
        $o->setConducteurTelephone(isset($r['conducteur_telephone']) && $r['conducteur_telephone'] !== '' ? (string) $r['conducteur_telephone'] : null);
        $o->setVehiculeMarque(isset($r['vehicule_marque']) && $r['vehicule_marque'] !== '' ? (string) $r['vehicule_marque'] : null);
        $o->setVehiculeModele(isset($r['vehicule_modele']) && $r['vehicule_modele'] !== '' ? (string) $r['vehicule_modele'] : null);
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getTrajetId(): int { return $this->trajetId; }
    public function getPassagerId(): int { return $this->passagerId; }
    public function getNbPlaces(): int { return $this->nbPlaces; }
    public function getPrixTotal(): float { return $this->prixTotal; }
    public function getStatut(): string { return $this->statut; }
    public function getDateReservation(): string { return $this->dateReservation; }
    public function getVilleDepart(): string { return $this->villeDepart; }
    public function getVilleArrivee(): string { return $this->villeArrivee; }
    public function getDateDepart(): string { return $this->dateDepart; }
    public function getHeureDepart(): string { return $this->heureDepart; }
    public function getPointRencontre(): ?string { return $this->pointRencontre; }
    public function getConducteurNom(): string { return $this->conducteurNom; }
    public function getConducteurPrenom(): string { return $this->conducteurPrenom; }
    public function getConducteurTelephone(): ?string { return $this->conducteurTelephone; }
    public function getVehiculeMarque(): ?string { return $this->vehiculeMarque; }
    public function getVehiculeModele(): ?string { return $this->vehiculeModele; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setTrajetId(int $v): void { $this->trajetId = $v; }
    public function setPassagerId(int $v): void { $this->passagerId = $v; }
    public function setNbPlaces(int $v): void { $this->nbPlaces = $v; }
    public function setPrixTotal(float $v): void { $this->prixTotal = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
    public function setDateReservation(string $v): void { $this->dateReservation = $v; }
    public function setVilleDepart(string $v): void { $this->villeDepart = $v; }
    public function setVilleArrivee(string $v): void { $this->villeArrivee = $v; }
    public function setDateDepart(string $v): void { $this->dateDepart = $v; }
    public function setHeureDepart(string $v): void { $this->heureDepart = $v; }
    public function setPointRencontre(?string $v): void { $this->pointRencontre = $v; }
    public function setConducteurNom(string $v): void { $this->conducteurNom = $v; }
    public function setConducteurPrenom(string $v): void { $this->conducteurPrenom = $v; }
    public function setConducteurTelephone(?string $v): void { $this->conducteurTelephone = $v; }
    public function setVehiculeMarque(?string $v): void { $this->vehiculeMarque = $v; }
    public function setVehiculeModele(?string $v): void { $this->vehiculeModele = $v; }
}
