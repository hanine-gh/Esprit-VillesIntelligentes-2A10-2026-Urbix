<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN trajet proposé par un conducteur — table covoiturage_trajets (+ conducteur, véhicule, avis).
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class CovoiturageTrajetModel extends Model
{
    private ?int $id = null;
    private int $conducteurId = 0;
    private ?int $vehiculeId = null;
    private string $villeDepart = '';
    private string $villeArrivee = '';
    private ?string $pointRencontre = null;
    private string $dateDepart = '';
    private string $heureDepart = '';
    private ?int $dureeEstimeeMin = null;
    private float $prixPlace = 0;
    private int $placesTotal = 0;
    private int $placesDisponibles = 0;
    private string $bagages = '';
    private int $fumeur = 0;
    private int $animaux = 0;
    private ?string $description = null;
    private string $statut = '';
    private string $conducteurNom = '';
    private string $conducteurPrenom = '';
    private ?string $conducteurTelephone = null;
    private ?string $vehiculeMarque = null;
    private ?string $vehiculeModele = null;
    private ?string $vehiculeCouleur = null;
    private int $climatisation = 0;
    private ?float $noteMoyenne = null;
    private int $nbAvis = 0;
    private ?int $score = null;
    private int $meilleur = 0;
    private ?string $heureArrivee = null;

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setConducteurId((int) ($r['conducteur_id'] ?? 0));
        $o->setVehiculeId(isset($r['vehicule_id']) && $r['vehicule_id'] !== '' ? (int) $r['vehicule_id'] : null);
        $o->setVilleDepart((string) ($r['ville_depart'] ?? ''));
        $o->setVilleArrivee((string) ($r['ville_arrivee'] ?? ''));
        $o->setPointRencontre(isset($r['point_rencontre']) && $r['point_rencontre'] !== '' ? (string) $r['point_rencontre'] : null);
        $o->setDateDepart((string) ($r['date_depart'] ?? ''));
        $o->setHeureDepart((string) ($r['heure_depart'] ?? ''));
        $o->setDureeEstimeeMin(isset($r['duree_estimee_min']) && $r['duree_estimee_min'] !== '' ? (int) $r['duree_estimee_min'] : null);
        $o->setPrixPlace((float) ($r['prix_place'] ?? 0));
        $o->setPlacesTotal((int) ($r['places_total'] ?? 0));
        $o->setPlacesDisponibles((int) ($r['places_disponibles'] ?? 0));
        $o->setBagages((string) ($r['bagages'] ?? ''));
        $o->setFumeur((int) ($r['fumeur'] ?? 0));
        $o->setAnimaux((int) ($r['animaux'] ?? 0));
        $o->setDescription(isset($r['description']) && $r['description'] !== '' ? (string) $r['description'] : null);
        $o->setStatut((string) ($r['statut'] ?? ''));
        $o->setConducteurNom((string) ($r['conducteur_nom'] ?? ''));
        $o->setConducteurPrenom((string) ($r['conducteur_prenom'] ?? ''));
        $o->setConducteurTelephone(isset($r['conducteur_telephone']) && $r['conducteur_telephone'] !== '' ? (string) $r['conducteur_telephone'] : null);
        $o->setVehiculeMarque(isset($r['vehicule_marque']) && $r['vehicule_marque'] !== '' ? (string) $r['vehicule_marque'] : null);
        $o->setVehiculeModele(isset($r['vehicule_modele']) && $r['vehicule_modele'] !== '' ? (string) $r['vehicule_modele'] : null);
        $o->setVehiculeCouleur(isset($r['vehicule_couleur']) && $r['vehicule_couleur'] !== '' ? (string) $r['vehicule_couleur'] : null);
        $o->setClimatisation((int) ($r['climatisation'] ?? 0));
        $o->setNoteMoyenne(isset($r['note_moyenne']) && $r['note_moyenne'] !== '' ? (float) $r['note_moyenne'] : null);
        $o->setNbAvis((int) ($r['nb_avis'] ?? 0));
        $o->setScore(isset($r['score']) && $r['score'] !== '' ? (int) $r['score'] : null);
        $o->setMeilleur((int) ($r['meilleur'] ?? 0));
        $o->setHeureArrivee(isset($r['heure_arrivee']) && $r['heure_arrivee'] !== '' ? (string) $r['heure_arrivee'] : null);
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getConducteurId(): int { return $this->conducteurId; }
    public function getVehiculeId(): ?int { return $this->vehiculeId; }
    public function getVilleDepart(): string { return $this->villeDepart; }
    public function getVilleArrivee(): string { return $this->villeArrivee; }
    public function getPointRencontre(): ?string { return $this->pointRencontre; }
    public function getDateDepart(): string { return $this->dateDepart; }
    public function getHeureDepart(): string { return $this->heureDepart; }
    public function getDureeEstimeeMin(): ?int { return $this->dureeEstimeeMin; }
    public function getPrixPlace(): float { return $this->prixPlace; }
    public function getPlacesTotal(): int { return $this->placesTotal; }
    public function getPlacesDisponibles(): int { return $this->placesDisponibles; }
    public function getBagages(): string { return $this->bagages; }
    public function getFumeur(): int { return $this->fumeur; }
    public function getAnimaux(): int { return $this->animaux; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatut(): string { return $this->statut; }
    public function getConducteurNom(): string { return $this->conducteurNom; }
    public function getConducteurPrenom(): string { return $this->conducteurPrenom; }
    public function getConducteurTelephone(): ?string { return $this->conducteurTelephone; }
    public function getVehiculeMarque(): ?string { return $this->vehiculeMarque; }
    public function getVehiculeModele(): ?string { return $this->vehiculeModele; }
    public function getVehiculeCouleur(): ?string { return $this->vehiculeCouleur; }
    public function getClimatisation(): int { return $this->climatisation; }
    public function getNoteMoyenne(): ?float { return $this->noteMoyenne; }
    public function getNbAvis(): int { return $this->nbAvis; }
    public function getScore(): ?int { return $this->score; }
    public function getMeilleur(): int { return $this->meilleur; }
    public function getHeureArrivee(): ?string { return $this->heureArrivee; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setConducteurId(int $v): void { $this->conducteurId = $v; }
    public function setVehiculeId(?int $v): void { $this->vehiculeId = $v; }
    public function setVilleDepart(string $v): void { $this->villeDepart = $v; }
    public function setVilleArrivee(string $v): void { $this->villeArrivee = $v; }
    public function setPointRencontre(?string $v): void { $this->pointRencontre = $v; }
    public function setDateDepart(string $v): void { $this->dateDepart = $v; }
    public function setHeureDepart(string $v): void { $this->heureDepart = $v; }
    public function setDureeEstimeeMin(?int $v): void { $this->dureeEstimeeMin = $v; }
    public function setPrixPlace(float $v): void { $this->prixPlace = $v; }
    public function setPlacesTotal(int $v): void { $this->placesTotal = $v; }
    public function setPlacesDisponibles(int $v): void { $this->placesDisponibles = $v; }
    public function setBagages(string $v): void { $this->bagages = $v; }
    public function setFumeur(int $v): void { $this->fumeur = $v; }
    public function setAnimaux(int $v): void { $this->animaux = $v; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
    public function setConducteurNom(string $v): void { $this->conducteurNom = $v; }
    public function setConducteurPrenom(string $v): void { $this->conducteurPrenom = $v; }
    public function setConducteurTelephone(?string $v): void { $this->conducteurTelephone = $v; }
    public function setVehiculeMarque(?string $v): void { $this->vehiculeMarque = $v; }
    public function setVehiculeModele(?string $v): void { $this->vehiculeModele = $v; }
    public function setVehiculeCouleur(?string $v): void { $this->vehiculeCouleur = $v; }
    public function setClimatisation(int $v): void { $this->climatisation = $v; }
    public function setNoteMoyenne(?float $v): void { $this->noteMoyenne = $v; }
    public function setNbAvis(int $v): void { $this->nbAvis = $v; }
    public function setScore(?int $v): void { $this->score = $v; }
    public function setMeilleur(int $v): void { $this->meilleur = $v; }
    public function setHeureArrivee(?string $v): void { $this->heureArrivee = $v; }

    /** Petits libellés calculés par la recherche intelligente (« Le moins cher »...) */
    private array $badges = [];

    public function getBadges(): array { return $this->badges; }
    public function setBadges(array $v): void { $this->badges = $v; }
}
