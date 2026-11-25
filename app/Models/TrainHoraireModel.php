<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UN horaire de trajet en train — table train_horaires.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class TrainHoraireModel extends Model
{
    private ?int $id = null;
    private int $trainId = 0;
    private int $ligneId = 0;
    private string $stationDepart = '';
    private string $heureDepart = '';
    private string $stationArrivee = '';
    private string $heureArrivee = '';
    private string $dateTrajet = '';
    private string $etat = '';
    private ?float $prix = null;
    private string $trainNumero = '';
    private string $ligneNom = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setTrainId((int) ($r['train_id'] ?? 0));
        $o->setLigneId((int) ($r['ligne_id'] ?? 0));
        $o->setStationDepart((string) ($r['station_depart'] ?? ''));
        $o->setHeureDepart((string) ($r['heure_depart'] ?? ''));
        $o->setStationArrivee((string) ($r['station_arrivee'] ?? ''));
        $o->setHeureArrivee((string) ($r['heure_arrivee'] ?? ''));
        $o->setDateTrajet((string) ($r['date_trajet'] ?? ''));
        $o->setEtat((string) ($r['etat'] ?? ''));
        $o->setPrix(isset($r['prix']) && $r['prix'] !== '' ? (float) $r['prix'] : null);
        $o->setTrainNumero((string) ($r['train_numero'] ?? ''));
        $o->setLigneNom((string) ($r['ligne_nom'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getTrainId(): int { return $this->trainId; }
    public function getLigneId(): int { return $this->ligneId; }
    public function getStationDepart(): string { return $this->stationDepart; }
    public function getHeureDepart(): string { return $this->heureDepart; }
    public function getStationArrivee(): string { return $this->stationArrivee; }
    public function getHeureArrivee(): string { return $this->heureArrivee; }
    public function getDateTrajet(): string { return $this->dateTrajet; }
    public function getEtat(): string { return $this->etat; }
    public function getPrix(): ?float { return $this->prix; }
    public function getTrainNumero(): string { return $this->trainNumero; }
    public function getLigneNom(): string { return $this->ligneNom; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setTrainId(int $v): void { $this->trainId = $v; }
    public function setLigneId(int $v): void { $this->ligneId = $v; }
    public function setStationDepart(string $v): void { $this->stationDepart = $v; }
    public function setHeureDepart(string $v): void { $this->heureDepart = $v; }
    public function setStationArrivee(string $v): void { $this->stationArrivee = $v; }
    public function setHeureArrivee(string $v): void { $this->heureArrivee = $v; }
    public function setDateTrajet(string $v): void { $this->dateTrajet = $v; }
    public function setEtat(string $v): void { $this->etat = $v; }
    public function setPrix(?float $v): void { $this->prix = $v; }
    public function setTrainNumero(string $v): void { $this->trainNumero = $v; }
    public function setLigneNom(string $v): void { $this->ligneNom = $v; }

    /** Durée du trajet, ex. "25 min" ou "1 h 05" */
    public function getDuree(): string
    {
        $min = (int) ((strtotime($this->heureArrivee) - strtotime($this->heureDepart)) / 60);
        return $min < 60 ? $min . ' min' : intdiv($min, 60) . ' h ' . str_pad((string) ($min % 60), 2, '0', STR_PAD_LEFT);
    }

    /** Libellé de l'état avec accent pour l'affichage */
    public function getEtatLibelle(): string
    {
        return $this->etat === "A l'heure" ? "À l'heure" : $this->etat;
    }
}
