<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Models\TrainModel;
use PDO;

/** TrainAdminController : tableau de bord du module Train (admin uniquement). */
class TrainAdminController extends Controller
{
    use UserFormTrait;
    use AdminTrait;

    protected string $page  = 'admin-train';
    protected string $title = 'Train | Backoffice Smart City';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // index.php?page=admin-train
    public function indexAction(): void
    {
        $this->requireSuperAdmin();

        $stats = $this->db->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(etat = 'En circulation'), 0) AS en_circulation,
                    COALESCE(SUM(etat <> 'En circulation'), 0) AS indisponibles
             FROM train_trains"
        )->fetch();
        $nbRetards = (int) $this->db->query(
            "SELECT COUNT(DISTINCT train_id) FROM train_alertes WHERE type = 'Retard' AND etat = 'Active' AND train_id IS NOT NULL"
        )->fetchColumn();
        $nbAlertesActives = (int) $this->db->query("SELECT COUNT(*) FROM train_alertes WHERE etat = 'Active'")->fetchColumn();
        $lignesInterrompues = $this->db->query("SELECT nom FROM train_lignes WHERE etat = 'Interrompue' ORDER BY nom")->fetchAll(PDO::FETCH_COLUMN);

        $this->render('admin_train', [
            'total'              => (int) $stats['total'],
            'nbLignes'           => (int) $this->db->query('SELECT COUNT(*) FROM train_lignes')->fetchColumn(),
            'enCirculation'      => (int) $stats['en_circulation'],
            'indisponibles'      => (int) $stats['indisponibles'],
            'etatGeneral'        => ($nbAlertesActives === 0 && !$lignesInterrompues) ? 'Réseau en fonctionnement' : 'Réseau perturbé',
            'nbRetards'          => $nbRetards,
            'lignesInterrompues' => $lignesInterrompues,
            'derniersTrains'     => array_map([TrainModel::class, 'fromArray'], $this->db->query(
                'SELECT t.*, l.nom AS ligne_nom FROM train_trains t JOIN train_lignes l ON l.id = t.ligne_id ORDER BY t.id DESC LIMIT 4'
            )->fetchAll()),
        ], 'layout_admin');
    }
}
