<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\TrainLigneModel;
use App\Models\TrainModel;

/**
 * TrainTrait : lectures et contrôles communs au module Train (frontoffice et backoffice).
 * Le contrôleur doit avoir une propriété $db (PDO).
 */
trait TrainTrait
{
    /** Stations du réseau ferroviaire */
    public static function trainStations(): array
    {
        return ['Ariana', 'Centre Ville', 'Bab Saadoun', 'Le Bardo', 'Cité El Khadra'];
    }

    private static function sqlTrainHoraires(): string
    {
        return 'SELECT h.*, t.numero AS train_numero, l.nom AS ligne_nom
                                           FROM train_horaires h
                                           JOIN train_trains t ON t.id = h.train_id
                                           JOIN train_lignes l ON l.id = h.ligne_id';
    }

    private static function sqlTrainAlertes(): string
    {
        return 'SELECT a.*, t.numero AS train_numero, l.nom AS ligne_nom
                                          FROM train_alertes a
                                          LEFT JOIN train_trains t ON t.id = a.train_id
                                          JOIN train_lignes l ON l.id = a.ligne_id';
    }

    /** @return TrainLigneModel[] */
    private function getTrainLignes(): array
    {
        $rows = $this->db->query('SELECT * FROM train_lignes ORDER BY nom')->fetchAll();
        return array_map([TrainLigneModel::class, 'fromArray'], $rows);
    }

    /** @return TrainModel[] */
    private function getTrains(): array
    {
        $rows = $this->db->query(
            'SELECT t.*, l.nom AS ligne_nom FROM train_trains t JOIN train_lignes l ON l.id = t.ligne_id ORDER BY t.numero'
        )->fetchAll();
        return array_map([TrainModel::class, 'fromArray'], $rows);
    }

    private function trainLigneExiste(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM train_lignes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Id du train portant ce numéro, ou null */
    private function trainIdParNumero(string $numero): ?int
    {
        $stmt = $this->db->prepare('SELECT id FROM train_trains WHERE numero = :n');
        $stmt->execute(['n' => $numero]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    private static function trainIsDate(string $v): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $v);
        return $d !== false && $d->format('Y-m-d') === $v;
    }

    private static function trainIsTime(string $v): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $v);
    }
}
