<?php
declare(strict_types=1);

namespace App\Models;

/**
 * UNE réclamation de passager — table louage_reclamations.
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 */
class LouageReclamationModel extends Model
{
    private ?int $id = null;
    private string $passager = '';
    private string $sujet = '';
    private string $message = '';
    private string $statut = '';

    public static function fromArray(array $r): self
    {
        $o = new self();
        $o->setId(isset($r['id']) && $r['id'] !== '' ? (int) $r['id'] : null);
        $o->setPassager((string) ($r['passager'] ?? ''));
        $o->setSujet((string) ($r['sujet'] ?? ''));
        $o->setMessage((string) ($r['message'] ?? ''));
        $o->setStatut((string) ($r['statut'] ?? ''));
        return $o;
    }

    public function getId(): ?int { return $this->id; }
    public function getPassager(): string { return $this->passager; }
    public function getSujet(): string { return $this->sujet; }
    public function getMessage(): string { return $this->message; }
    public function getStatut(): string { return $this->statut; }

    public function setId(?int $v): void { $this->id = $v; }
    public function setPassager(string $v): void { $this->passager = $v; }
    public function setSujet(string $v): void { $this->sujet = $v; }
    public function setMessage(string $v): void { $this->message = $v; }
    public function setStatut(string $v): void { $this->statut = $v; }
}
