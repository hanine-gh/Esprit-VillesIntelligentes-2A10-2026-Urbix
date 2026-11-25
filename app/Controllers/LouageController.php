<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\LouageHoraireModel;
use App\Models\LouageReservationModel;
use PDO;

/**
 * LouageController : module Louage — frontoffice.
 * Recherche, résultats, détails et stations : consultation libre.
 * Réservation, suivi, mes réservations : utilisateur connecté (table users).
 */
class LouageController extends Controller
{
    use UserFormTrait;
    use FrontTrait;
    use LouageTrait;

    protected string $bodyClass = 'no-sidebar is-preload';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // index.php?page=louage-recherche
    public function rechercheAction(): void
    {
        $this->page  = 'louage-recherche';
        $this->title = 'Search Louages | Smart City';
        $this->render('louage_recherche', ['villes' => $this->getLouageVilles()]);
    }

    // index.php?page=louage-resultats
    public function resultatsAction(): void
    {
        $this->page  = 'louage-resultats';
        $this->title = 'Search Results | Smart City';

        $recherche = [
            'departure'   => trim((string) ($_GET['departure'] ?? '')),
            'destination' => trim((string) ($_GET['destination'] ?? '')),
            'date'        => (string) ($_GET['date'] ?? ''),
            'seats'       => (int) ($_GET['seats'] ?? 1),
        ];
        $villes = $this->getLouageVilles();
        $erreurs = [];
        if (!in_array($recherche['departure'], $villes, true) || !in_array($recherche['destination'], $villes, true)) {
            $erreurs[] = 'Please choose a departure and a destination.';
        } elseif ($recherche['departure'] === $recherche['destination']) {
            $erreurs[] = 'The destination must be different from the departure.';
        }
        if (!self::isDate($recherche['date'])) {
            $erreurs[] = 'Please choose a valid travel date.';
        }
        if ($recherche['seats'] < 1 || $recherche['seats'] > 8) {
            $erreurs[] = 'Number of passengers: 1 to 8.';
        }

        $resultats = [];
        if (!$erreurs) {
            $stmt = $this->db->prepare(self::sqlLouageHoraires() .
                " WHERE sd.ville = :dep AND sa.ville = :arr AND DATE(h.depart_at) = :date AND h.depart_at >= NOW()
                    AND v.id IS NOT NULL AND v.statut <> 'Unavailable'
                  ORDER BY h.depart_at");
            $stmt->execute(['dep' => $recherche['departure'], 'arr' => $recherche['destination'], 'date' => $recherche['date']]);
            foreach ($stmt->fetchAll() as $row) {
                $h = LouageHoraireModel::fromArray($row);
                if ($h->getPlacesLibres() >= $recherche['seats']) {
                    $resultats[] = $h;
                }
            }
        }

        $this->render('louage_resultats', ['recherche' => $recherche, 'erreurs' => $erreurs, 'resultats' => $resultats]);
    }

    // index.php?page=louage-details&id=...
    public function detailsAction(): void
    {
        $this->page  = 'louage-details';
        $this->title = 'Louage Details | Smart City';
        $this->render('louage_details', ['horaire' => $this->findLouageHoraire((int) ($_GET['id'] ?? 0))]);
    }

    // index.php?page=louage-reservation[&schedule=...]
    public function reservationAction(): void
    {
        $userId = $this->requireUserId();
        $this->page  = 'louage-reservation';
        $this->title = 'Seat Reservation | Smart City';

        $moi = $this->currentUserInfo();
        $erreurs = [];
        $form = ['trip' => (int) ($_GET['schedule'] ?? 0), 'passenger' => $moi['nom'], 'phone' => $moi['telephone'], 'number_seats' => '1'];

        if ($this->isPost()) {
            $form = [
                'trip'         => (int) ($_POST['trip'] ?? 0),
                'passenger'    => trim((string) ($_POST['passenger'] ?? '')),
                'phone'        => trim((string) ($_POST['phone'] ?? '')),
                'number_seats' => trim((string) ($_POST['number_seats'] ?? '')),
            ];
            if (!$this->frontCsrfOk()) {
                $erreurs[] = 'Session expirée, veuillez réessayer.';
            }
            if (!self::louageNomValide($form['passenger'])) {
                $erreurs[] = 'Passenger full name: 2 to 80 letters.';
            }
            if (!self::louageTelephoneValide($form['phone'])) {
                $erreurs[] = 'Phone number: 8 to 15 digits.';
            }
            if (!ctype_digit($form['number_seats']) || (int) $form['number_seats'] < 1 || (int) $form['number_seats'] > 8) {
                $erreurs[] = 'Number of seats: 1 to 8.';
            }

            if (!$erreurs) {
                // Transaction : les places libres sont vérifiées et réservées en une fois
                $this->db->beginTransaction();
                $this->db->prepare('SELECT id FROM louage_horaires WHERE id = :id FOR UPDATE')->execute(['id' => $form['trip']]);
                $horaire = $this->findLouageHoraire($form['trip']);

                if ($horaire === null || strtotime($horaire->getDepartAt()) < time() || $horaire->getVehiculeId() === null) {
                    $erreurs[] = 'Please choose an available trip.';
                } elseif ($horaire->getPlacesLibres() < (int) $form['number_seats']) {
                    $erreurs[] = 'Only ' . $horaire->getPlacesLibres() . ' seat(s) left on this trip.';
                }

                if ($erreurs) {
                    $this->db->rollBack();
                } else {
                    $r = new LouageReservationModel();
                    $r->setHoraireId((int) $horaire->getId());
                    $r->setUserId($userId);
                    $r->setPassager($form['passenger']);
                    $r->setTelephone($form['phone']);
                    $r->setPlaces((int) $form['number_seats']);
                    $r->setStatut('Pending');

                    $this->db->prepare(
                        'INSERT INTO louage_reservations (horaire_id, user_id, passager, telephone, places, statut) VALUES (:h, :u, :p, :t, :n, :s)'
                    )->execute(['h' => $r->getHoraireId(), 'u' => $r->getUserId(), 'p' => $r->getPassager(),
                                't' => $r->getTelephone(), 'n' => $r->getPlaces(), 's' => $r->getStatut()]);
                    $this->db->commit();
                    $this->frontFlash('success', 'Your reservation request has been sent.');
                    self::redirect('louage-mes-reservations');
                }
            }
        }

        $horaires = $this->db->query(self::sqlLouageHoraires() . ' WHERE h.depart_at >= NOW() ORDER BY h.depart_at')->fetchAll();
        $horaires = array_filter(array_map([LouageHoraireModel::class, 'fromArray'], $horaires),
            fn($h) => $h->getVehiculeId() !== null && $h->getPlacesLibres() > 0);

        $this->render('louage_reservation', ['form' => $form, 'erreurs' => $erreurs, 'horaires' => $horaires]);
    }

    // index.php?page=louage-stations
    public function stationsAction(): void
    {
        $this->page  = 'louage-stations';
        $this->title = 'Louage Stations | Smart City';
        $this->render('louage_stations', ['stations' => $this->getLouageStations()]);
    }

    // index.php?page=louage-suivi : prochain trajet réservé (non annulé) de l'utilisateur
    public function suiviAction(): void
    {
        $userId = $this->requireUserId();
        $this->page  = 'louage-suivi';
        $this->title = 'Louage Tracking | Smart City';

        $stmt = $this->db->prepare(self::sqlLouageHoraires() .
            " JOIN louage_reservations res ON res.horaire_id = h.id
              WHERE res.user_id = :u AND res.statut IN ('Pending', 'Confirmed', 'Cancellation requested')
                AND COALESCE(h.arrivee_at, h.depart_at) >= NOW()
              ORDER BY h.depart_at LIMIT 1");
        $stmt->execute(['u' => $userId]);
        $row = $stmt->fetch();

        $this->render('louage_suivi', ['horaire' => $row ? LouageHoraireModel::fromArray($row) : null]);
    }

    // index.php?page=louage-mes-reservations
    public function mesReservationsAction(): void
    {
        $userId = $this->requireUserId();
        $this->page  = 'louage-mes-reservations';
        $this->title = 'My Reservations | Smart City';

        $stmt = $this->db->prepare(self::sqlLouageReservations() . ' WHERE res.user_id = :u ORDER BY h.depart_at DESC');
        $stmt->execute(['u' => $userId]);

        $this->render('louage_mes_reservations', [
            'reservations' => array_map([LouageReservationModel::class, 'fromArray'], $stmt->fetchAll()),
            'flash'        => $this->popFrontFlash(),
        ]);
    }

    /** « Request cancellation » : seulement ses propres réservations en attente ou confirmées */
    public function annulerAction(): void
    {
        $userId = $this->requireUserId();
        if ($this->isPost() && $this->frontCsrfOk()) {
            $stmt = $this->db->prepare(
                "UPDATE louage_reservations SET statut = 'Cancellation requested'
                 WHERE id = :id AND user_id = :u AND statut IN ('Pending', 'Confirmed')"
            );
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0), 'u' => $userId]);
            $stmt->rowCount()
                ? $this->frontFlash('success', 'Your cancellation request has been sent.')
                : $this->frontFlash('error', 'This reservation cannot be cancelled.');
        }
        self::redirect('louage-mes-reservations');
    }
}
