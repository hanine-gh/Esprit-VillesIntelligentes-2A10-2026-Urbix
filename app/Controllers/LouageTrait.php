<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\LouageHoraireModel;
use App\Models\LouageRouteModel;
use App\Models\LouageStationModel;
use App\Models\LouageVehiculeModel;

/**
 * LouageTrait : lectures et contrôles communs au module Louage (frontoffice et backoffice).
 * Le contrôleur doit avoir une propriété $db (PDO).
 */
trait LouageTrait
{
    /** Départs avec trajet, stations, prix, véhicule, chauffeur et places déjà réservées */
    private static function sqlLouageHoraires(): string
    {
        return "SELECT h.*, r.prix,
                       sd.nom AS depart_nom, sd.ville AS depart_ville,
                       sa.nom AS arrivee_nom, sa.ville AS arrivee_ville,
                       v.immatriculation, v.modele, COALESCE(v.capacite, 0) AS capacite, c.nom AS chauffeur_nom,
                       (SELECT COALESCE(SUM(re.places), 0) FROM louage_reservations re
                         WHERE re.horaire_id = h.id AND re.statut <> 'Cancelled') AS places_reservees
                FROM louage_horaires h
                JOIN louage_routes r    ON r.id = h.route_id
                JOIN louage_stations sd ON sd.id = r.station_depart_id
                JOIN louage_stations sa ON sa.id = r.station_arrivee_id
                LEFT JOIN louage_vehicules v  ON v.id = h.vehicule_id
                LEFT JOIN louage_chauffeurs c ON c.id = v.chauffeur_id";
    }

    private static function sqlLouageRoutes(): string
    {
        return 'SELECT r.*, sd.nom AS depart_nom, sd.ville AS depart_ville, sa.nom AS arrivee_nom, sa.ville AS arrivee_ville
                FROM louage_routes r
                JOIN louage_stations sd ON sd.id = r.station_depart_id
                JOIN louage_stations sa ON sa.id = r.station_arrivee_id';
    }

    /** Réservations avec le trajet et la date de départ */
    private static function sqlLouageReservations(): string
    {
        return 'SELECT res.*, h.depart_at, sd.ville AS depart_ville, sa.ville AS arrivee_ville
                FROM louage_reservations res
                JOIN louage_horaires h  ON h.id = res.horaire_id
                JOIN louage_routes r    ON r.id = h.route_id
                JOIN louage_stations sd ON sd.id = r.station_depart_id
                JOIN louage_stations sa ON sa.id = r.station_arrivee_id';
    }

    private function findLouageHoraire(int $id): ?LouageHoraireModel
    {
        $stmt = $this->db->prepare(self::sqlLouageHoraires() . ' WHERE h.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? LouageHoraireModel::fromArray($row) : null;
    }

    /** @return LouageStationModel[] */
    private function getLouageStations(): array
    {
        return array_map([LouageStationModel::class, 'fromArray'],
            $this->db->query('SELECT * FROM louage_stations ORDER BY ville, nom')->fetchAll());
    }

    /** @return string[] villes qui ont au moins une station */
    private function getLouageVilles(): array
    {
        return $this->db->query('SELECT DISTINCT ville FROM louage_stations ORDER BY ville')->fetchAll(\PDO::FETCH_COLUMN);
    }

    /** @return LouageRouteModel[] */
    private function getLouageRoutes(): array
    {
        return array_map([LouageRouteModel::class, 'fromArray'],
            $this->db->query(self::sqlLouageRoutes() . ' ORDER BY sd.ville, sa.ville')->fetchAll());
    }

    /** @return LouageVehiculeModel[] */
    private function getLouageVehicules(): array
    {
        return array_map([LouageVehiculeModel::class, 'fromArray'], $this->db->query(
            'SELECT v.*, c.nom AS chauffeur_nom FROM louage_vehicules v LEFT JOIN louage_chauffeurs c ON c.id = v.chauffeur_id ORDER BY v.immatriculation'
        )->fetchAll());
    }

    private static function louageTelephoneValide(string $tel): bool
    {
        return (bool) preg_match('/^\+?[0-9 ]{8,15}$/', $tel);
    }

    private static function louageNomValide(string $nom): bool
    {
        return (bool) preg_match("/^[\p{L}][\p{L} '.-]{1,79}$/u", $nom);
    }
}
