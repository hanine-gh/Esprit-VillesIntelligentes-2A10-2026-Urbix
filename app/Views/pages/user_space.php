<?php
/**
 * Vue : espace personnel / profil (frontoffice).
 * Variables : $user (UserModel), $editMode, $profileErrors, $profileOld, $profileSuccess,
 *             $genres, $typesService, $csrfToken
 * Remarque : l'id (auto-incrémenté) n'est jamais affiché ni envoyé dans le formulaire.
 */
$isAdmin   = $user->getRole() === 'admin';
$initiales = mb_strtoupper(mb_substr($user->getPrenom(), 0, 1) . mb_substr($user->getNom(), 0, 1));
$typeLabel = $typesService[$user->getTypeService()] ?? 'Non renseigné';
$since     = $user->getCreatedAt() ? date('d/m/Y', strtotime($user->getCreatedAt())) : '—';

// Valeurs du formulaire : celles saisies (si erreur) sinon celles de la base
$f = !empty($profileOld) ? $profileOld : [
    'nom'            => $user->getNom(),
    'prenom'         => $user->getPrenom(),
    'email'          => $user->getEmail(),
    'date_naissance' => $user->getDateNaissance(),
    'genre'          => $user->getGenre(),
    'telephone'      => $user->getTelephone(),
    'type_service'   => $user->getTypeService(),
];
?>
<section id="main">
    <div class="container">
        <div class="profile-layout">

            <!-- ===== Barre latérale ===== -->
            <aside class="profile-sidebar">
                <div class="profile-avatar"><?= e($initiales) ?></div>
                <h3 class="profile-name"><?= e($user->getPrenom() . ' ' . $user->getNom()) ?></h3>
                <p class="profile-email"><?= e($user->getEmail()) ?></p>

                <p class="profile-badges">
                    <span class="auth-badge auth-badge-service"><?= e($typeLabel) ?></span>
                    <?php if ($isAdmin): ?>
                        <span class="auth-badge auth-badge-admin">Administrateur</span>
                    <?php endif; ?>
                </p>

                <ul class="profile-menu">
                    <li>
                        <a href="<?= BASE_URL ?>index.php?page=dashboard" class="<?= !$editMode ? 'active' : '' ?>">
                            <i class="fas fa-id-card"></i> Mon profil
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>index.php?page=dashboard&amp;edit=1" class="<?= $editMode ? 'active' : '' ?>">
                            <i class="fas fa-user-edit"></i> Modifier mon profil
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                        <li>
                            <a href="<?= BASE_URL ?>index.php?page=admin">
                                <i class="fas fa-tachometer-alt"></i> Backoffice
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="<?= BASE_URL ?>index.php?page=logout" class="logout">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </li>
                </ul>

                <p class="profile-since"><i class="far fa-calendar-alt"></i> Membre depuis le <?= e($since) ?></p>
            </aside>

            <!-- ===== Contenu ===== -->
            <div class="profile-content">

                <?php if ($profileSuccess): ?>
                    <div class="auth-alert auth-alert-success"><?= e($profileSuccess) ?></div>
                <?php endif; ?>

                <?php if (!$editMode): ?>
                    <!-- Consultation -->
                    <h2 class="major"><span>Mes informations</span></h2>

                    <div class="profile-grid">
                        <div class="profile-item"><span class="label">Nom</span><span class="value"><?= e($user->getNom()) ?></span></div>
                        <div class="profile-item"><span class="label">Prénom</span><span class="value"><?= e($user->getPrenom()) ?></span></div>
                        <div class="profile-item"><span class="label">Email</span><span class="value"><?= e($user->getEmail()) ?></span></div>
                        <div class="profile-item"><span class="label">Téléphone</span><span class="value"><?= e($user->getTelephone()) ?></span></div>
                        <div class="profile-item"><span class="label">Date de naissance</span><span class="value"><?= $user->getDateNaissance() ? e(date('d/m/Y', strtotime($user->getDateNaissance()))) : '—' ?></span></div>
                        <div class="profile-item"><span class="label">Genre</span><span class="value"><?= e($genres[$user->getGenre()] ?? '—') ?></span></div>
                        <div class="profile-item"><span class="label">Type de compte</span><span class="value"><?= e($typeLabel) ?></span></div>
                        <div class="profile-item"><span class="label">Membre depuis</span><span class="value"><?= e($since) ?></span></div>
                    </div>

                    <div class="profile-actions">
                        <a href="<?= BASE_URL ?>index.php?page=dashboard&amp;edit=1" class="auth-btn auth-btn-primary">
                            <i class="fas fa-user-edit"></i> Modifier mes informations
                        </a>
                    </div>

                <?php else: ?>
                    <!-- Modification -->
                    <h2 class="major"><span>Modifier mon profil</span></h2>

                    <?php if (!empty($profileErrors)): ?>
                        <div class="auth-alert auth-alert-error">
                            <ul>
                                <?php foreach ($profileErrors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form id="profile-form" class="profile-form" method="post" novalidate action="<?= BASE_URL ?>index.php?page=profile-update">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                        <div class="auth-row">
                            <div class="auth-field">
                                <label for="p-nom">Nom</label>
                                <input type="text" id="p-nom" name="nom" value="<?= e($f['nom'] ?? '') ?>">
                            </div>
                            <div class="auth-field">
                                <label for="p-prenom">Prénom</label>
                                <input type="text" id="p-prenom" name="prenom" value="<?= e($f['prenom'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="auth-row">
                            <div class="auth-field">
                                <label for="p-email">Email</label>
                                <input type="text" id="p-email" name="email" value="<?= e($f['email'] ?? '') ?>">
                            </div>
                            <div class="auth-field">
                                <label for="p-telephone">Téléphone</label>
                                <input type="text" id="p-telephone" name="telephone" value="<?= e($f['telephone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="auth-row">
                            <div class="auth-field">
                                <label for="p-date">Date de naissance</label>
                                <input type="date" id="p-date" name="date_naissance" value="<?= e($f['date_naissance'] ?? '') ?>">
                            </div>
                            <div class="auth-field">
                                <label for="p-genre">Genre</label>
                                <select id="p-genre" name="genre">
                                    <option value="">Choisir...</option>
                                    <?php foreach ($genres as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= ($f['genre'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="p-type">Type de compte</label>
                            <select id="p-type" name="type_service">
                                <option value="">Choisir un type...</option>
                                <?php foreach ($typesService as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= ($f['type_service'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <h3 class="profile-subtitle">
                            <i class="fas fa-lock"></i> Changer le mot de passe
                            <small>(laisser vide pour le conserver)</small>
                        </h3>

                        <div class="auth-field">
                            <label for="p-current">Mot de passe actuel</label>
                            <input type="password" id="p-current" name="current_password" autocomplete="current-password">
                        </div>

                        <div class="auth-row">
                            <div class="auth-field">
                                <label for="p-password">Nouveau mot de passe (min. 8 caractères)</label>
                                <input type="password" id="p-password" name="password" autocomplete="new-password">
                            </div>
                            <div class="auth-field">
                                <label for="p-confirm">Confirmer le nouveau mot de passe</label>
                                <input type="password" id="p-confirm" name="password_confirm" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="profile-actions">
                            <button type="submit" class="auth-btn auth-btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                            <a href="<?= BASE_URL ?>index.php?page=dashboard" class="auth-btn auth-btn-secondary">Annuler</a>
                        </div>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>
