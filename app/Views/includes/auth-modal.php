<?php
// Popup de connexion / inscription (interface utilisateur).
// Les formulaires sont envoyés à UtilisateurController (routes "connexion" et "inscription").
$authForm = $_SESSION['auth_form'] ?? null;
unset($_SESSION['auth_form']);
$authOnglet = $authForm['onglet'] ?? ($ouvrirAuth ?? 'login');
$old = $authForm['old'] ?? [];
$retourAuth = $_SERVER['REQUEST_URI'] ?? url('home');
$oldVal = function (string $cle) use ($old): string {
    return e($old[$cle] ?? '');
};
$roles = [
    'user-normal'         => 'Utilisateur normal',
    'chauffeur-bus'       => 'Chauffeur bus',
    'chauffeur-train'     => 'Chauffeur train',
    'louage'              => 'Louage',
    'covoiturage'         => 'Covoiturage',
    'location'            => 'Location',
    'reparation-vehicule' => 'Réparation de véhicule',
];
?>
<div id="auth-modal" class="auth-modal<?= ($ouvrirAuth || $authForm) ? ' is-open' : '' ?>">
    <a href="#" class="auth-backdrop" aria-label="Fermer la fenêtre"></a>

    <div class="auth-dialog" role="dialog" aria-labelledby="auth-modal-title">
        <a href="#" class="auth-close" aria-label="Fermer">&times;</a>

        <h2 id="auth-modal-title" class="auth-title">Bienvenue</h2>
        <p class="auth-subtitle">Connectez-vous ou créez un compte pour accéder à votre espace.</p>

        <?php if (!empty($authForm['erreur'])): ?>
            <p class="auth-error" role="alert"><i class="fas fa-exclamation-circle" aria-hidden="true"></i> <?= e($authForm['erreur']) ?></p>
        <?php endif; ?>

        <input class="auth-tab-input" type="radio" name="auth-tab" id="auth-tab-login" <?= $authOnglet === 'login' ? 'checked' : '' ?>>
        <input class="auth-tab-input" type="radio" name="auth-tab" id="auth-tab-signin" <?= $authOnglet === 'signin' ? 'checked' : '' ?>>

        <div class="auth-tabs">
            <label for="auth-tab-login" class="auth-tab-label auth-tab-login-label">Connexion</label>
            <label for="auth-tab-signin" class="auth-tab-label auth-tab-signin-label">Inscription</label>
        </div>

        <div class="auth-panels">
            <!-- Connexion -->
            <form class="auth-panel auth-panel-login" method="post" action="<?= url('connexion') ?>" novalidate data-validate="login">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="retour" value="<?= e($retourAuth) ?>">

                <div class="auth-field">
                    <label for="auth-login-email">Email</label>
                    <input type="email" id="auth-login-email" name="login_email" placeholder="vous@exemple.com" value="<?= $oldVal('login_email') ?>" autocomplete="email">
                    <small class="field-error"></small>
                </div>

                <div class="auth-field">
                    <label for="auth-login-password">Mot de passe</label>
                    <input type="password" id="auth-login-password" name="login_password" placeholder="Mot de passe" autocomplete="current-password">
                    <small class="field-error"></small>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="auth-btn auth-btn-primary">Se connecter</button>
                    <a href="#" class="auth-btn auth-btn-secondary">Annuler</a>
                </div>

                <label for="auth-tab-signin" class="auth-switch">Créer un compte</label>
            </form>

            <!-- Inscription -->
            <form class="auth-panel auth-panel-signin" method="post" action="<?= url('inscription') ?>" novalidate data-validate="signin">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="retour" value="<?= e($retourAuth) ?>">

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="auth-last-name">Nom</label>
                        <input type="text" id="auth-last-name" name="last_name" placeholder="Nom" value="<?= $oldVal('nom') ?>" autocomplete="family-name">
                        <small class="field-error"></small>
                    </div>
                    <div class="auth-field">
                        <label for="auth-first-name">Prénom</label>
                        <input type="text" id="auth-first-name" name="first_name" placeholder="Prénom" value="<?= $oldVal('prenom') ?>" autocomplete="given-name">
                        <small class="field-error"></small>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="auth-signin-email">Email</label>
                    <input type="email" id="auth-signin-email" name="email" placeholder="vous@exemple.com" value="<?= $oldVal('email') ?>" autocomplete="email">
                    <small class="field-error"></small>
                </div>

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="auth-birthdate">Date de naissance</label>
                        <input type="date" id="auth-birthdate" name="birthdate" value="<?= $oldVal('date_naissance') ?>">
                        <small class="field-error"></small>
                    </div>
                    <div class="auth-field">
                        <label for="auth-gender">Genre</label>
                        <select id="auth-gender" name="gender">
                            <option value="">Choisir...</option>
                            <?php foreach (['homme' => 'Homme', 'femme' => 'Femme', 'autre' => 'Autre'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($old['genre'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="field-error"></small>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="auth-phone">Numéro de téléphone</label>
                    <input type="tel" id="auth-phone" name="phone" placeholder="22 345 678" value="<?= $oldVal('telephone') ?>" autocomplete="tel">
                    <small class="field-error"></small>
                </div>

                <div class="auth-field">
                    <label for="auth-role">Type de compte</label>
                    <select id="auth-role" name="role">
                        <option value="">Choisir un type...</option>
                        <?php foreach ($roles as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($old['role'] ?? '') === $v ? 'selected' : '' ?>><?= e($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="field-error"></small>
                </div>

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="auth-password">Mot de passe</label>
                        <input type="password" id="auth-password" name="password" placeholder="8 caractères min." autocomplete="new-password">
                        <small class="field-error"></small>
                    </div>
                    <div class="auth-field">
                        <label for="auth-password-confirm">Confirmation</label>
                        <input type="password" id="auth-password-confirm" name="password_confirm" placeholder="Répéter" autocomplete="new-password">
                        <small class="field-error"></small>
                    </div>
                </div>

                <div class="auth-actions">
                    <button type="submit" class="auth-btn auth-btn-primary">S'inscrire</button>
                    <a href="#" class="auth-btn auth-btn-secondary">Annuler</a>
                </div>

                <label for="auth-tab-login" class="auth-switch">Déjà un compte ? Se connecter</label>
            </form>
        </div>
    </div>
</div>
