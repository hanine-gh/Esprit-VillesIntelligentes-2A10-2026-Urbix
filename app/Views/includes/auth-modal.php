<?php
// Popup statique de connexion / inscription (aucun JavaScript, aucun traitement).
?>
<div id="auth-modal" class="auth-modal">
    <a href="#" class="auth-backdrop" aria-label="Fermer la fenêtre"></a>

    <div class="auth-dialog" role="dialog" aria-labelledby="auth-modal-title">
        <a href="#" class="auth-close" aria-label="Fermer">&times;</a>

        <h2 id="auth-modal-title" class="auth-title">Bienvenue</h2>
        <p class="auth-subtitle">Connectez-vous ou créez un compte.</p>

        <input class="auth-tab-input" type="radio" name="auth-tab" id="auth-tab-login">
        <input class="auth-tab-input" type="radio" name="auth-tab" id="auth-tab-signin" checked>

        <div class="auth-tabs">
            <label for="auth-tab-login" class="auth-tab-label auth-tab-login-label">Login</label>
            <label for="auth-tab-signin" class="auth-tab-label auth-tab-signin-label">Sign in</label>
        </div>

        <div class="auth-panels">
            <div class="auth-panel auth-panel-login">
                <div class="auth-field">
                    <label for="auth-login-email">Email</label>
                    <input type="email" id="auth-login-email" name="login_email" placeholder="vous@exemple.com">
                </div>

                <div class="auth-field">
                    <label for="auth-login-password">Mot de passe</label>
                    <input type="password" id="auth-login-password" name="login_password" placeholder="Mot de passe">
                </div>

                <div class="auth-actions">
                    <button type="button" class="auth-btn auth-btn-primary">Se connecter</button>
                    <a href="#" class="auth-btn auth-btn-secondary">Annuler</a>
                </div>

                <label for="auth-tab-signin" class="auth-switch">Créer un compte</label>
            </div>

            <div class="auth-panel auth-panel-signin">
                <div class="auth-row">
                    <div class="auth-field">
                        <label for="auth-last-name">Nom</label>
                        <input type="text" id="auth-last-name" name="last_name" placeholder="Nom">
                    </div>
                    <div class="auth-field">
                        <label for="auth-first-name">Prénom</label>
                        <input type="text" id="auth-first-name" name="first_name" placeholder="Prénom">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="auth-signin-email">Email</label>
                    <input type="email" id="auth-signin-email" name="email" placeholder="vous@exemple.com">
                </div>

                <div class="auth-row">
                    <div class="auth-field">
                        <label for="auth-birthdate">Date de naissance</label>
                        <input type="date" id="auth-birthdate" name="birthdate">
                    </div>
                    <div class="auth-field">
                        <label for="auth-gender">Gender</label>
                        <select id="auth-gender" name="gender">
                            <option value="">Choisir...</option>
                            <option value="homme">Homme</option>
                            <option value="femme">Femme</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="auth-phone">Numéro de téléphone</label>
                    <input type="tel" id="auth-phone" name="phone" placeholder="06 00 00 00 00">
                </div>

                <div class="auth-field">
                    <label for="auth-role">Type de compte</label>
                    <select id="auth-role" name="role">
                        <option value="">Choisir un type...</option>
                        <option value="user-normal">Utilisateur normal</option>
                        <option value="chauffeur-bus">Chauffeur bus</option>
                        <option value="chauffeur-train">Chauffeur train</option>
                        <option value="louage">Louage</option>
                        <option value="covoiturage">Covoiturage</option>
                        <option value="location">Location</option>
                        <option value="reparation-vehicule">Réparation de véhicule</option>
                    </select>
                </div>

                <div class="auth-actions">
                    <button type="button" class="auth-btn auth-btn-primary">S'inscrire</button>
                    <a href="#" class="auth-btn auth-btn-secondary">Annuler</a>
                </div>

                <label for="auth-tab-login" class="auth-switch">Déjà un compte ? Se connecter</label>
            </div>
        </div>
    </div>
</div>
