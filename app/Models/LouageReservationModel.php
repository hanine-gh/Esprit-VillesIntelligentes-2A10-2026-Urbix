<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UNE réservation de places dans un louage — table louage_reservations.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class LouageReservationModel extends Model
{
    private ?int $id = null;
    private int $horaireId = 0;
    private ?int $userId = null;
    private string $passager = '';
    private string $telephone = '';
    private int $places = 0;
    private string $statut = '';
    private string $departAt = '';
    private string $departVille = '';
    private string $arriveeVille = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setHoraireId((int) ($r['horaire_id'] ?? 0));
        $o->setUserId(isset($r['user_id']) && $r['user_id'] !== '' ? (int) $r['user_id'] : null);
        $o->setPassager((string) ($r['passager'] ?? ''));
        $o->setTelephone((string) ($r['telephone'] ?? ''));
        $o->setPlaces((int) ($r['places'] ?? 0));
        $o->setStatut((string) ($r['statut'] ?? ''));
        $o->setDepartAt((string) ($r['depart_at'] ?? ''));
        $o->setDepartVille((string) ($r['depart_ville'] ?? ''));
        $o->setArriveeVille((string) ($r['arrivee_ville'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getHoraireId(): int { return $this->horaireId; }
    public function getUserId(): ?int { return $this->userId; }
    public function getPassager(): string { return $this->passager; }
    public function getTelephone(): string { return $this->telephone; }
    public function getPlaces(): int { return $this->places; }
    public function getStatut(): string { return $this->statut; }
    public function getDepartAt(): string { return $this->departAt; }
    public function getDepartVille(): string { return $this->departVille; }
    public function getArriveeVille(): string { return $this->arriveeVille; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setHoraireId(int $v): void { $this->horaireId = $v; }
    public function setUserId(?int $v): void { $this->userId = $v; }
    public function setPassager(string $v): void { $this->passager = $v; }
    public function setTelephone(string $v): void { $this->telephone = $v; }
    public function setPlaces(int $v): void { $this->places = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
    public function setDepartAt(string $v): void { $this->departAt = $v; }
    public function setDepartVille(string $v): void { $this->departVille = $v; }
    public function setArriveeVille(string $v): void { $this->arriveeVille = $v; }

    /** Libellé du trajet, ex. « Tunis to Sousse » */
    public function getTrajet(): string
    {
        return $this->departVille . ' to ' . $this->arriveeVille;
    }
}
