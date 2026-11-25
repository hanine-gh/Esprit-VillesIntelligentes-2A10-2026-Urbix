<?php $edition = $form['id'] > 0; ?>
      <div class="page-heading">
        <h3>Manage drivers</h3>
        <p class="text-subtitle text-muted">Create and maintain driver records.</p>
      </div>
      <div class="page-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4><?= $edition ? 'Edit a driver' : 'Add a driver' ?></h4>
                </div>
                <div class="card-body">
                  <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
                  <form action="<?= BASE_URL ?>index.php?page=admin-louage-chauffeurs" method="post" data-validate="modules" novalidate>
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="name" class="form-label">Full name</label>
                          <input type="text" class="form-control" id="name" name="name" data-v-required="Full name is required." data-v-pattern="^\p{L}[\p{L} '.-]{1,79}$" data-v-msg="Full name: 2 to 80 letters." value="<?= e($form['name']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="phone" class="form-label">Phone</label>
                          <input type="tel" class="form-control" id="phone" name="phone" data-v-pattern="^\+?[0-9 ]{8,15}$" data-v-msg="Phone: 8 to 15 digits." value="<?= e($form['phone']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="license" class="form-label">Driving license number</label>
                          <input type="text" class="form-control" id="license" name="license" data-v-pattern="^[0-9A-Za-z-]{4,30}$" data-v-msg="4 to 30 letters, digits or dashes." value="<?= e($form['license']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="status" class="form-label">Status</label>
                          <select class="form-select" id="status" name="status">
                            <?php foreach ($statuts as $s): ?>
                            <option<?= selected($form['status'], $s) ?>><?= e($s) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="mt-3">
                      <button type="submit" class="btn btn-primary"><?= $edition ? 'Save changes' : 'Add driver' ?></button>
                      <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-louage-chauffeurs" class="btn btn-light-secondary">Cancel</a><?php endif; ?>
                    </div>
                  </form>
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
                  <h4>Driver list</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Name</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($chauffeurs as $c): ?>
                        <tr>
                          <td><?= e($c->getNom()) ?></td><td><?= $c->getTelephone() !== null ? e($c->getTelephone()) : '—' ?></td><td><?= e($c->getStatut()) ?></td>
                          <td>
                            <a href="<?= BASE_URL ?>index.php?page=admin-louage-chauffeurs&amp;edit=<?= $c->getId() ?>" class="btn btn-sm btn-success">Edit</a>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-chauffeur-delete" method="post" class="d-inline" data-confirm="Delete this driver?">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $c->getId() ?>">
                              <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$chauffeurs): ?><tr><td colspan="4">No driver yet.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
