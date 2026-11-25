<?php
declare(strict_types=1);

namespace App\Models;

/**
 * BusModel : représente UN bus de la flotte (table `buses`).
 * Uniquement des attributs privés + getters/setters (aucun SQL).
 * chauffeurNom / chauffeurPrenom / nbTrajets : infos jointes pour l'affichage.
 */
class BusModel extends Model
{
    private ?int $id = null;
    private string $matricule = '';
    private string $marque = '';
    private string $modele = '';
    private int $capacite = 0;
    private int $annee = 0;
    private string $statut = 'disponible';
    private ?string $etatCommentaire = null;   // problème signalé par le chauffeur
    private ?string $etatMajAt = null;         // date du dernier signalement
    private ?int $chauffeurId = null;
    private ?string $createdAt = null;

    private ?string $chauffeurNom = null;
    private ?string $chauffeurPrenom = null;
    private int $nbTrajets = 0;

    public static function fromArray(array $row): self
    {
        $b = new self();
        $b->setId(isset($row['id']) ? (int) $row['id'] : null);
        $b->setMatricule((string) ($row['matricule'] ?? ''));
        $b->setMarque((string) ($row['marque'] ?? ''));
        $b->setModele((string) ($row['modele'] ?? ''));
        $b->setCapacite((int) ($row['capacite'] ?? 0));
        $b->setAnnee((int) ($row['annee'] ?? 0));
        $b->setStatut((string) ($row['statut'] ?? 'disponible'));
        $b->setEtatCommentaire($row['etat_commentaire'] ?? null);
        $b->setEtatMajAt($row['etat_maj_at'] ?? null);
        $b->setChauffeurId(isset($row['chauffeur_id']) && $row['chauffeur_id'] !== '' ? (int) $row['chauffeur_id'] : null);
        $b->setCreatedAt($row['created_at'] ?? null);
        $b->setChauffeurNom($row['chauffeur_nom'] ?? null);
        $b->setChauffeurPrenom($row['chauffeur_prenom'] ?? null);
        $b->setNbTrajets((int) ($row['nb_trajets'] ?? 0));
        return $b;
    }

    // ===== Getters =====
    public function getId(): ?int { return $this->id; }
    public function getMatricule(): string { return $this->matricule; }
    public function getMarque(): string { return $this->marque; }
    public function getModele(): string { return $this->modele; }
    public function getCapacite(): int { return $this->capacite; }
    public function getAnnee(): int { return $this->annee; }
    public function getStatut(): string { return $this->statut; }
    public function getEtatCommentaire(): ?string { return $this->etatCommentaire; }
    public function getEtatMajAt(): ?string { return $this->etatMajAt; }
    public function getChauffeurId(): ?int { return $this->chauffeurId; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getChauffeurNom(): ?string { return $this->chauffeurNom; }
    public function getChauffeurPrenom(): ?string { return $this->chauffeurPrenom; }
    public function getNbTrajets(): int { return $this->nbTrajets; }

    // ===== Setters =====
    public function setId(?int $id): void { $this->id = $id; }
    public function setMatricule(string $m): void { $this->matricule = $m; }
    public function setMarque(string $m): void { $this->marque = $m; }
    public function setModele(string $m): void { $this->modele = $m; }
    public function setCapacite(int $c): void { $this->capacite = $c; }
    public function setAnnee(int $a): void { $this->annee = $a; }
    public function setStatut(string $s): void { $this->statut = $s; }
    public function setEtatCommentaire(?string $c): void { $this->etatCommentaire = $c; }
    public function setEtatMajAt(?string $d): void { $this->etatMajAt = $d; }
    public function setChauffeurId(?int $id): void { $this->chauffeurId = $id; }
    public function setCreatedAt(?string $d): void { $this->createdAt = $d; }
    public function setChauffeurNom(?string $n): void { $this->chauffeurNom = $n; }
    public function setChauffeurPrenom(?string $p): void { $this->chauffeurPrenom = $p; }
    public function setNbTrajets(int $n): void { $this->nbTrajets = $n; }
}
