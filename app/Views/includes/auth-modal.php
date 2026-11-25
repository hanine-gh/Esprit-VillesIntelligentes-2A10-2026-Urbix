<?php
// Popup de connexion / inscription (seule interface d'authentification du site).
// Les formulaires sont envoyés en POST au routeur (page=login / page=register).
// Après traitement, le contrôleur revient ici : la popup s'ouvre toute seule
// sur le bon onglet avec les messages ($authPopup).
// Variables fournies par Controller::render() : $csrfToken, $genres, $typesService, $authPopup
$popupTab     = $authPopup['tab'] ?? 'register';
$popupErrors  = $authPopup['errors'] ?? [];
$popupOld     = $authPopup['old'] ?? [];
$popupSuccess = $authPopup['success'] ?? null;
$popupInfo    = $authPopup['info'] ?? null;
?>
<div id="auth-modal" class="auth-modal<?= $authPopup ? ' is-open' : '' ?>">
    <a href="#" class="auth-backdrop" aria-label="Fermer la fenêtre"></a>

    <div class="auth-dialog" role="dialog" aria-labelledby="auth-modal-title">
        <a href="#" class="auth-close" aria-label="Fermer">&times;</a>

        <h2 id="auth-modal-title" class="auth-title">Bienvenue</h2>
        <p class="auth-subtitle">Connectez-vous ou créez un compte.</p>

        <input class="auth-tab-input" type="radio" name="auth-tab" id="auth-tab-login" <?= $popupTab === 'login' ? 'checked' : '' ?>>
        <input class="auth-tab-input" type="radio" name="auth-tab" id="auth-tab-signin" <?= $popupTab !== 'login' ? 'checked' : '' ?>>

        <div class="auth-tabs">
            <label for="auth-tab-login" class="auth-tab-label auth-tab-login-label">Login</label>
            <label for="auth-tab-signin" class="auth-tab-label auth-tab-signin-label">Sign in</label>
        </div>

        <div class="auth-panels">
            <!-- ===== CONNEXION ===== -->
            <div class="auth-panel auth-panel-login">
                <?php if ($popupTab === 'login'): ?>
                    <?php if ($popupSuccess): ?>
                        <div class="auth-alert auth-alert-success"><?= e($popupSuccess) ?></div>
                    <?php endif; ?>
                    <?php if ($popupInfo): ?>
                        <div class="auth-alert auth-alert-info"><?= e($popupInfo) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($popupErrors)): ?>
                        <div class="auth-alert auth-alert-error">
                            <ul>
                                <?php foreach ($popupErrors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <form id="auth-login-form" method="post" novalidate action="<?= BASE_URL ?>index.php?page=login">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="auth-field">
                        <label for="auth-login-email">Email</label>
                        <input type="text" id="auth-login-email" name="email" placeholder="vous@exemple.com"
                               value="<?= $popupTab === 'login' ? e($popupOld['email'] ?? '') : '' ?>">
                    </div>

                    <div class="auth-field">
                        <label for="auth-login-password">Mot de passe</label>
                        <input type="password" id="auth-login-password" name="password" placeholder="Mot de passe">
                    </div>

                    <div class="auth-actions">
                        <button type="submit" class="auth-btn auth-btn-primary">Se connecter</button>
                        <a href="#" class="auth-btn auth-btn-secondary">Annuler</a>
                    </div>
                </form>

                <label for="auth-tab-signin" class="auth-switch">Créer un compte</label>
            </div>

            <!-- ===== INSCRIPTION ===== -->
            <?php $old = $popupTab === 'register' ? $popupOld : []; ?>
            <div class="auth-panel auth-panel-signin">
                <?php if ($popupTab === 'register' && !empty($popupErrors)): ?>
                    <div class="auth-alert auth-alert-error">
                        <ul>
                            <?php foreach ($popupErrors as $error): ?>
                                <li><?= e($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="auth-register-form" method="post" novalidate action="<?= BASE_URL ?>index.php?page=register">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                    <div class="auth-row">
                        <div class="auth-field">
                            <label for="auth-last-name">Nom</label>
                            <input type="text" id="auth-last-name" name="nom" placeholder="Nom"
                                   value="<?= e($old['nom'] ?? '') ?>">
                        </div>
                        <div class="auth-field">
                            <label for="auth-first-name">Prénom</label>
                            <input type="text" id="auth-first-name" name="prenom" placeholder="Prénom"
                                   value="<?= e($old['prenom'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="auth-signin-email">Email</label>
                        <input type="text" id="auth-signin-email" name="email" placeholder="vous@exemple.com"
                               value="<?= e($old['email'] ?? '') ?>">
                    </div>

                    <div class="auth-row">
                        <div class="auth-field">
                            <label for="auth-birthdate">Date de naissance</label>
                            <input type="date" id="auth-birthdate" name="date_naissance"
                                   value="<?= e($old['date_naissance'] ?? '') ?>">
                        </div>
                        <div class="auth-field">
                            <label for="auth-gender">Genre</label>
                            <select id="auth-gender" name="genre">
                                <option value="">Choisir...</option>
                                <?php foreach ($genres as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= ($old['genre'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="auth-phone">Numéro de téléphone</label>
                        <input type="text" id="auth-phone" name="telephone" placeholder="20 000 000"
                               value="<?= e($old['telephone'] ?? '') ?>">
                    </div>

                    <div class="auth-field">
                        <label for="auth-type-service">Type de compte</label>
                        <select id="auth-type-service" name="type_service">
                            <option value="">Choisir un type...</option>
                            <?php foreach ($typesService as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= ($old['type_service'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="auth-row">
                        <div class="auth-field">
                            <label for="auth-password">Mot de passe (min. 8 caractères)</label>
                            <input type="password" id="auth-password" name="password">
                        </div>
                        <div class="auth-field">
                            <label for="auth-password-confirm">Confirmer le mot de passe</label>
                            <input type="password" id="auth-password-confirm" name="password_confirm">
                        </div>
                    </div>

                    <div class="auth-actions">
                        <button type="submit" class="auth-btn auth-btn-primary">S'inscrire</button>
                        <a href="#" class="auth-btn auth-btn-secondary">Annuler</a>
                    </div>
                </form>

                <label for="auth-tab-login" class="auth-switch">Déjà un compte ? Se connecter</label>
            </div>
        </div>
    </div>
</div>
