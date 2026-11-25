      <div class="page-heading">
        <h3>Manage complaints</h3>
        <p class="text-subtitle text-muted">Review and follow up on passenger complaints.</p>
      </div>
      <div class="page-content">
        <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Complaint list</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Passenger</th><th>Subject</th><th>Message</th><th>Status</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($reclamations as $r): ?>
                        <tr>
                          <td><?= e($r->getPassager()) ?></td><td><?= e($r->getSujet()) ?></td><td><?= e($r->getMessage()) ?></td><td><?= e($r->getStatut()) ?></td>
                          <td>
                            <?php if ($r->getStatut() === 'Open'): ?>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-reclamation-resoudre" method="post" class="d-inline">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $r->getId() ?>">
                              <button class="btn btn-sm btn-primary">Mark as resolved</button>
                            </form>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$reclamations): ?><tr><td colspan="5">No complaint.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Send a notification</h4>
                </div>
                <div class="card-body">
                  
                  <form action="<?= BASE_URL ?>index.php?page=admin-louage-notification" method="post" data-validate="modules" novalidate>
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <div class="form-group">
                      <label for="message" class="form-label">Message</label>
                      <textarea class="form-control" id="message" name="message" data-v-required="Message is required." data-v-pattern="^[\s\S]{5,1000}$" data-v-msg="5 to 1000 characters." rows="4"><?= e($texte) ?></textarea>
                    </div>
                    <div class="mt-3">
                      <button type="submit" class="btn btn-primary">Send notification</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
