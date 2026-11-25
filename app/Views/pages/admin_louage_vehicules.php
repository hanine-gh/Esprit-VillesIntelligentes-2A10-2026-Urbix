<?php $edition = $form['id'] > 0; ?>
      <div class="page-heading">
        <h3>Manage louages</h3>
        <p class="text-subtitle text-muted">Add new vehicles and maintain the louage fleet.</p>
      </div>
      <div class="page-content">
        <section class="section">
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4><?= $edition ? 'Edit a louage' : 'Add a louage' ?></h4>
                </div>
                <div class="card-body">
                  <?php require __DIR__ . '/../admin/partials/messages.php'; ?>
                  <form action="<?= BASE_URL ?>index.php?page=admin-louage-vehicules" method="post" data-validate="modules" novalidate>
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
                    <div class="row">
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="registration" class="form-label">Registration number</label>
                          <input type="text" class="form-control" id="registration" name="registration" data-v-required="Registration number is required." data-v-pattern="^[0-9A-Za-z ]{4,20}$" data-v-msg="4 to 20 letters, digits or spaces." value="<?= e($form['registration']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="model" class="form-label">Vehicle model</label>
                          <input type="text" class="form-control" id="model" name="model" data-v-required="Vehicle model is required." data-v-maxlength="60" data-v-msg="60 characters max." value="<?= e($form['model']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="capacity" class="form-label">Capacity</label>
                          <input type="number" class="form-control" id="capacity" name="capacity" data-v-required="Capacity is required." data-v-int="1,20" data-v-msg="Capacity: 1 to 20 seats." value="<?= e($form['capacity']) ?>">
                        </div>
                      </div>
                      <div class="col-md-6 col-12">
                        <div class="form-group">
                          <label for="driver" class="form-label">Assigned driver</label>
                          <select class="form-select" id="driver" name="driver">
                            <option value="">No driver</option>
                            <?php foreach ($chauffeurs as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"<?= selected($form['driver'], $c['id']) ?>><?= e($c['nom']) ?></option>
                            <?php endforeach; ?>
                          </select>
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
                      <button type="submit" class="btn btn-primary"><?= $edition ? 'Save changes' : 'Add louage' ?></button>
                      <?php if ($edition): ?><a href="<?= BASE_URL ?>index.php?page=admin-louage-vehicules" class="btn btn-light-secondary">Cancel</a><?php endif; ?>
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
                  <h4>Louage list</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr><th>Registration</th><th>Model</th><th>Capacity</th><th>Driver</th><th>Status</th><th>Actions</th></tr>
                      </thead>
                      <tbody>
                        <?php foreach ($vehicules as $v): ?>
                        <tr>
                          <td><?= e($v->getImmatriculation()) ?></td><td><?= e($v->getModele()) ?></td><td><?= $v->getCapacite() ?></td>
                          <td><?= $v->getChauffeurNom() !== null ? e($v->getChauffeurNom()) : '—' ?></td><td><?= e($v->getStatut()) ?></td>
                          <td>
                            <a href="<?= BASE_URL ?>index.php?page=admin-louage-vehicules&amp;edit=<?= $v->getId() ?>" class="btn btn-sm btn-success">Edit</a>
                            <form action="<?= BASE_URL ?>index.php?page=admin-louage-vehicule-delete" method="post" class="d-inline" data-confirm="Delete this louage?">
                              <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                              <input type="hidden" name="id" value="<?= $v->getId() ?>">
                              <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$vehicules): ?><tr><td colspan="6">No louage yet.</td></tr><?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
