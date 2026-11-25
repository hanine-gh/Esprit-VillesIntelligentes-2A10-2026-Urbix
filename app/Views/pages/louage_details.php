<!-- Main -->
<section id="main">
  <div class="container">
    <div class="row gtr-200">
      <div class="col-12">
        <section class="box">
          <h2>Trip and louage details</h2>
          <?php if ($horaire === null): ?>
            <p>This trip does not exist.</p>
            <ul class="actions">
              <li><a href="<?= BASE_URL ?>index.php?page=louage-recherche" class="button alt">Back to search</a></li>
            </ul>
          <?php else: ?>
          <p>Review the trip information before making a reservation.</p>
          <h3><?= e($horaire->getTrajet()) ?></h3>
          <ul class="divided">
            <li>Departure station: <?= e($horaire->getDepartNom()) ?>, <?= e($horaire->getDepartVille()) ?></li>
            <li>Arrival station: <?= e($horaire->getArriveeNom()) ?>, <?= e($horaire->getArriveeVille()) ?></li>
            <li>Departure time: <?= e(dateHeure($horaire->getDepartAt())) ?></li>
            <li>Estimated arrival: <?= e(dateHeure($horaire->getArriveeAt())) ?></li>
            <li>Price: <?= e(prixTnd($horaire->getPrix())) ?> per passenger</li>
            <li>Vehicle: <?= $horaire->getModele() ? e($horaire->getModele() . ' (' . $horaire->getImmatriculation() . ')') : 'Not assigned yet' ?></li>
            <li>Driver: <?= $horaire->getChauffeurNom() ? e($horaire->getChauffeurNom()) : 'Not assigned yet' ?></li>
            <li>Available seats: <?= $horaire->getPlacesLibres() ?></li>
          </ul>
          <ul class="actions">
            <li><a href="<?= BASE_URL ?>index.php?page=louage-reservation&amp;schedule=<?= $horaire->getId() ?>" class="button">Reserve a seat</a></li>
            <li><a href="<?= BASE_URL ?>index.php?page=louage-recherche" class="button alt">Back to search</a></li>
          </ul>
          <?php endif; ?>
        </section>
      </div>
    </div>
  </div>
</section>
