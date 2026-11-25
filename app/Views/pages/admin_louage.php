      <div class="page-heading">
        <h3>Administration dashboard</h3>
        <p class="text-subtitle text-muted">Overview of the louage service.</p>
      </div>
      <div class="page-content">
        <section class="row">
          <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                    <div class="stats-icon purple mb-2"><i class="iconly-boldShow"></i></div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Registered louages</h6>
                    <h6 class="font-extrabold mb-0"><?= $nbLouages ?></h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                    <div class="stats-icon blue mb-2"><i class="iconly-boldProfile"></i></div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Drivers</h6>
                    <h6 class="font-extrabold mb-0"><?= $nbChauffeurs ?></h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                    <div class="stats-icon green mb-2"><i class="iconly-boldAdd-User"></i></div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Reservations</h6>
                    <h6 class="font-extrabold mb-0"><?= $nbReservations ?></h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-6 col-lg-3 col-md-6">
            <div class="card">
              <div class="card-body px-4 py-4-5">
                <div class="row">
                  <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                    <div class="stats-icon red mb-2"><i class="iconly-boldBookmark"></i></div>
                  </div>
                  <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                    <h6 class="text-muted font-semibold">Stations</h6>
                    <h6 class="font-extrabold mb-0"><?= $nbStations ?></h6>
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
                  <h4>Quick management links</h4>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-vehicules" class="btn btn-outline-primary w-100">Manage louages</a></div>
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-chauffeurs" class="btn btn-outline-primary w-100">Manage drivers</a></div>
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-stations" class="btn btn-outline-primary w-100">Manage stations</a></div>
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-trajets" class="btn btn-outline-primary w-100">Manage routes</a></div>
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-horaires" class="btn btn-outline-primary w-100">Manage schedules</a></div>
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-reservations" class="btn btn-outline-primary w-100">Manage reservations</a></div>
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-prix" class="btn btn-outline-primary w-100">Manage prices</a></div>
                    <div class="col-md-3 col-6 mb-3"><a href="<?= BASE_URL ?>index.php?page=admin-louage-reclamations" class="btn btn-outline-primary w-100">Manage complaints</a></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
