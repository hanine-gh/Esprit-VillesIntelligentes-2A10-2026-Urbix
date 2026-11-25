<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UNE location d'un véhicule — table location_locations.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class MobiliteLocationModel extends Model
{
    private ?int $id = null;
    private string $reference = '';
    private ?int $userId = null;
    private ?int $vehiculeId = null;
    private string $vehiculeNom = '';
    private string $vehiculeType = '';
    private string $stationDepart = '';
    private string $stationRetour = '';
    private int $dureeHeures = 0;
    private float $montant = 0;
    private string $codePin = '';
    private string $statut = '';
    private int $annulee = 0;
    private ?string $incident = null;
    private ?int $noteDonnee = null;
    private string $clientNom = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setReference((string) ($r['reference'] ?? ''));
        $o->setUserId(isset($r['user_id']) && $r['user_id'] !== '' ? (int) $r['user_id'] : null);
        $o->setVehiculeId(isset($r['vehicule_id']) && $r['vehicule_id'] !== '' ? (int) $r['vehicule_id'] : null);
        $o->setVehiculeNom((string) ($r['vehicule_nom'] ?? ''));
        $o->setVehiculeType((string) ($r['vehicule_type'] ?? ''));
        $o->setStationDepart((string) ($r['station_depart'] ?? ''));
        $o->setStationRetour((string) ($r['station_retour'] ?? ''));
        $o->setDureeHeures((int) ($r['duree_heures'] ?? 0));
        $o->setMontant((float) ($r['montant'] ?? 0));
        $o->setCodePin((string) ($r['code_pin'] ?? ''));
        $o->setStatut((string) ($r['statut'] ?? ''));
        $o->setAnnulee((int) ($r['annulee'] ?? 0));
        $o->setIncident(isset($r['incident']) && $r['incident'] !== '' ? (string) $r['incident'] : null);
        $o->setNoteDonnee(isset($r['note_donnee']) && $r['note_donnee'] !== '' ? (int) $r['note_donnee'] : null);
        $o->setClientNom((string) ($r['client_nom'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getReference(): string { return $this->reference; }
    public function getUserId(): ?int { return $this->userId; }
    public function getVehiculeId(): ?int { return $this->vehiculeId; }
    public function getVehiculeNom(): string { return $this->vehiculeNom; }
    public function getVehiculeType(): string { return $this->vehiculeType; }
    public function getStationDepart(): string { return $this->stationDepart; }
    public function getStationRetour(): string { return $this->stationRetour; }
    public function getDureeHeures(): int { return $this->dureeHeures; }
    public function getMontant(): float { return $this->montant; }
    public function getCodePin(): string { return $this->codePin; }
    public function getStatut(): string { return $this->statut; }
    public function getAnnulee(): int { return $this->annulee; }
    public function getIncident(): ?string { return $this->incident; }
    public function getNoteDonnee(): ?int { return $this->noteDonnee; }
    public function getClientNom(): string { return $this->clientNom; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setReference(string $v): void { $this->reference = $v; }
    public function setUserId(?int $v): void { $this->userId = $v; }
    public function setVehiculeId(?int $v): void { $this->vehiculeId = $v; }
    public function setVehiculeNom(string $v): void { $this->vehiculeNom = $v; }
    public function setVehiculeType(string $v): void { $this->vehiculeType = $v; }
    public function setStationDepart(string $v): void { $this->stationDepart = $v; }
    public function setStationRetour(string $v): void { $this->stationRetour = $v; }
    public function setDureeHeures(int $v): void { $this->dureeHeures = $v; }
    public function setMontant(float $v): void { $this->montant = $v; }
    public function setCodePin(string $v): void { $this->codePin = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
    public function setAnnulee(int $v): void { $this->annulee = $v; }
    public function setIncident(?string $v): void { $this->incident = $v; }
    public function setNoteDonnee(?int $v): void { $this->noteDonnee = $v; }
    public function setClientNom(string $v): void { $this->clientNom = $v; }
}
