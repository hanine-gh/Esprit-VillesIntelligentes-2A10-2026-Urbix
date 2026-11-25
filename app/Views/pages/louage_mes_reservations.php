<!-- Main -->
<section id="main">
  <div class="container">
    <div class="row gtr-200">
      <div class="col-12">
        <section class="box">
          <h2>My reservations</h2>
          <p>Review your current and previous louage bookings.</p>
          <?php if ($flash): ?>
            <div class="auth-alert <?= $flash['type'] === 'success' ? 'auth-alert-success' : 'auth-alert-error' ?>"><?= e($flash['message']) ?></div>
          <?php endif; ?>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr><th>Trip</th><th>Date</th><th>Seats</th><th>Status</th><th>Action</th></tr>
              </thead>
              <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr>
                  <td><?= e($r->getTrajet()) ?></td><td><?= e(dateHeure($r->getDepartAt())) ?></td><td><?= $r->getPlaces() ?></td><td><?= e($r->getStatut()) ?></td>
                  <td>
                    <?php if (in_array($r->getStatut(), ['Pending', 'Confirmed'], true)): ?>
                    <form action="<?= BASE_URL ?>index.php?page=louage-reservation-annuler" method="post" data-confirm="Request the cancellation of this reservation?">
                      <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                      <input type="hidden" name="id" value="<?= $r->getId() ?>">
                      <button type="submit">Request cancellation</button>
                    </form>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$reservations): ?>
                <tr><td colspan="5">No reservation yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
  </div>
</section>
