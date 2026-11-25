<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\StationModel;
use App\Models\TrajetModel;

/**
 * StationFormTrait : contrôle de saisie et enregistrement d'une station,
 * partagé par l'admin (StationController) et le chauffeur (ChauffeurController).
 * Une seule implémentation = exactement les mêmes règles.
 * Le contrôleur doit avoir $db (PDO) et utiliser AdminTrait (isValidTime) + UserFormTrait (field).
 *
 * Règles :
 * - nom : 2 à 100 caractères ; adresse : 5 à 255 caractères
 * - ordre : entier de 1 à 99, unique sur le trajet
 * - heure de passage : HH:MM, dans les horaires du trajet,
 *   après la station précédente et avant la suivante
 */
trait StationFormTrait
{
    protected static function stationNamePattern(): string
    {
        return "/^[\p{L}0-9][\p{L}0-9 '\-.,]{1,99}$/u";       // 2 à 100
    }

    protected static function stationAddressPattern(): string
    {
        return "/^[\p{L}0-9][\p{L}0-9 '\-.,°\/]{4,254}$/u";  // 5 à 255
    }

    protected function nextOrdre(int $trajetId): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(ordre), 0) + 1 FROM stations WHERE trajet_id = :id');
        $stmt->execute(['id' => $trajetId]);
        return (int) $stmt->fetchColumn();
    }

    protected function readStationInput(array $post): array
    {
        return [
            'nom'           => $this->field($post, 'nom'),
            'adresse'       => $this->field($post, 'adresse'),
            'ordre'         => $this->field($post, 'ordre'),
            'heure_passage' => $this->field($post, 'heure_passage'),
        ];
    }

    protected function validateStationInput(array $d, TrajetModel $trajet, int $exceptId = 0): array
    {
        $errors = [];

        if (!preg_match(self::stationNamePattern(), $d['nom'])) {
            $errors[] = "Le nom de la station doit contenir 2 à 100 caractères.";
        }
        if (!preg_match(self::stationAddressPattern(), $d['adresse'])) {
            $errors[] = "L'adresse doit contenir 5 à 255 caractères.";
        }

        $ordreOk = ctype_digit($d['ordre']) && (int) $d['ordre'] >= 1 && (int) $d['ordre'] <= 99;
        if (!$ordreOk) {
            $errors[] = "L'ordre doit être un nombre entier entre 1 et 99.";
        }

        $heureOk = $this->isValidTime($d['heure_passage']);
        if (!$heureOk) {
            $errors[] = "Heure de passage invalide (format HH:MM).";
        } elseif ($d['heure_passage'] < $trajet->getHeureDepart() || $d['heure_passage'] > $trajet->getHeureArrivee()) {
            $errors[] = "L'heure de passage doit être comprise entre {$trajet->getHeureDepart()} et {$trajet->getHeureArrivee()} (horaires du trajet).";
        }

        $tid = (int) $trajet->getId();

        if ($ordreOk) {
            // Ordre unique sur ce trajet
            $stmt = $this->db->prepare('SELECT nom FROM stations WHERE trajet_id = :tid AND ordre = :ordre AND id <> :id LIMIT 1');
            $stmt->execute(['tid' => $tid, 'ordre' => (int) $d['ordre'], 'id' => $exceptId]);
            $taken = $stmt->fetchColumn();
            if ($taken !== false) {
                $errors[] = "L'ordre {$d['ordre']} est déjà utilisé par la station « {$taken} ».";
            }
        }

        if ($ordreOk && $heureOk) {
            // Cohérence ordre / heure : après la station précédente, avant la suivante
            $stmt = $this->db->prepare(
                'SELECT nom FROM stations WHERE trajet_id = :tid AND id <> :id AND ordre < :ordre AND heure_passage > :h LIMIT 1'
            );
            $stmt->execute(['tid' => $tid, 'id' => $exceptId, 'ordre' => (int) $d['ordre'], 'h' => $d['heure_passage']]);
            if (($before = $stmt->fetchColumn()) !== false) {
                $errors[] = "L'heure de passage doit être après celle de la station précédente « {$before} ».";
            }

            $stmt = $this->db->prepare(
                'SELECT nom FROM stations WHERE trajet_id = :tid AND id <> :id AND ordre > :ordre AND heure_passage < :h LIMIT 1'
            );
            $stmt->execute(['tid' => $tid, 'id' => $exceptId, 'ordre' => (int) $d['ordre'], 'h' => $d['heure_passage']]);
            if (($after = $stmt->fetchColumn()) !== false) {
                $errors[] = "L'heure de passage doit être avant celle de la station suivante « {$after} ».";
            }
        }

        return $errors;
    }

    protected function fillStation(StationModel $s, array $d): void
    {
        $s->setNom($d['nom']);
        $s->setAdresse($d['adresse']);
        $s->setOrdre((int) $d['ordre']);
        $s->setHeurePassage($d['heure_passage']);
    }

    /** Enregistre une nouvelle station (requête préparée). */
    protected function insertStation(StationModel $station): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO stations (trajet_id, nom, adresse, ordre, heure_passage)
             VALUES (:trajet_id, :nom, :adresse, :ordre, :heure_passage)'
        );
        $stmt->execute([
            'trajet_id'     => $station->getTrajetId(),
            'nom'           => $station->getNom(),
            'adresse'       => $station->getAdresse(),
            'ordre'         => $station->getOrdre(),
            'heure_passage' => $station->getHeurePassage(),
        ]);
    }
}
