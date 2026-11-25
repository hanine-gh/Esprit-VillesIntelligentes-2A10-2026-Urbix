      <div class="page-heading">
        <h3>Manage reservations</h3>
        <p class="text-subtitle text-muted">Review passenger booking requests and update their status.</p>
      </div>
      <div class="page-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Reservation list</h4>
                </div>
                <div class="card-body">
                  <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Passenger</th><th>Route</th><th>Seats</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($reservations as $r): ?>
                        <tr>
                          <td><?= e($r->getPassager()) ?><br><small class="text-muted"><?= e($r->getTelephone()) ?></small></td>
                          <td><?= e($r->getTrajet()) ?></td><td><?= $r->getPlaces() ?></td><td><?= e(dateHeure($r->getDepartAt())) ?></td><td><?= e($r->getStatut()) ?></td>
                          <td>
                            <?php if ($r->getStatut() === 'Pending'): ?>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-reservation-statut" method="post" class="d-inline">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $r->getId() ?>">
                              <input type="hidden" name="status" value="Confirmed">
                              <button class="btn btn-sm btn-success">Confirm</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($r->getStatut() !== 'Cancelled'): ?>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-reservation-statut" method="post" class="d-inline" data-confirm="Cancel this reservation?">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $r->getId() ?>">
                              <input type="hidden" name="status" value="Cancelled">
                              <button class="btn btn-sm btn-danger">Cancel</button>
                            </form>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$reservations): ?><tr><td colspan="6">No reservation yet.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
