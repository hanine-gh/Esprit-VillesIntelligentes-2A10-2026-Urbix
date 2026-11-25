<nav id="nav">
	<ul>
		<li><a href="index.php?page=home">HOME</a></li>
		<li class="current">
			<a href="index.php?page=mobilite">LOCATION</a>
			<ul>
				<li><a href="index.php?page=mobilite">Tous les véhicules</a></li>
				<li><a href="index.php?page=mobilite-reserver">Réserver</a></li>
				<li><a href="index.php?page=mobilite-demarrer">Déverrouiller / Démarrer</a></li>
				<li><a href="index.php?page=mobilite-prolonger">Prolonger</a></li>
				<li><a href="index.php?page=mobilite-incident">Signaler un incident</a></li>
				<li><a href="index.php?page=mobilite-terminer">Terminer &amp; noter</a></li>
				<li><a href="index.php?page=mobilite-annuler">Annuler / Supprimer</a></li>
			</ul>
		</li>
		<li><a href="index.php?page=mobilite-reserver">RÉSERVER</a></li>
		<li><a href="index.php?page=mobilite-prolonger">PROLONGER</a></li>
		<li><a href="index.php?page=backoffice-mobilite">ESPACE ADMIN</a></li>
	</ul>
	<button type="button" id="btnOpenUserModal" class="user-nav-btn" aria-label="Espace Utilisateur" title="Connexion / User" style="position: fixed; top: 14px; right: 18px; z-index: 10001; width: 42px; height: 42px; border-radius: 50%; border: none; background: #4c6ef5; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.18);">
		<i class="fas fa-user" aria-hidden="true"></i>
	</button>
</nav>

<div id="urbixUserModalBackdrop" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); display: none; align-items: center; justify-content: center; z-index: 10002; padding: 1em;">
	<div style="background: #ffffff; color: #2e383f; border-radius: 8px; max-width: 420px; width: 100%; padding: 1.5em; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);">
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1em; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.6em;">
			<h3 style="margin: 0;" id="urbixUserModalTitle">Espace Utilisateur URBIX</h3>
			<button type="button" id="btnCloseUrbixUserModal" class="button alt small" style="padding: 0.2em 0.6em; height: auto;">✕</button>
		</div>

		<p id="urbixUserCurrentBadge" style="font-size: 0.85em; font-weight: 700; color: #1d5bbf; margin-bottom: 0.9em;">Statut : Invité</p>

		<div style="display: flex; gap: 0.5em; margin-bottom: 1em;">
			<button type="button" id="urbixTabLogin" class="button small">Connexion</button>
			<button type="button" id="urbixTabRegister" class="button alt small">Inscription</button>
		</div>

		<form id="urbixUserAuthForm">
			<div id="urbixNomFieldWrap" style="display: none; margin-bottom: 0.75em;">
				<label for="urbixUserNom">Nom complet</label>
				<input type="text" id="urbixUserNom" placeholder="Ex: Ali Wizani" />
			</div>
			<div style="margin-bottom: 0.75em;">
				<label for="urbixUserEmail">Email</label>
				<input type="email" id="urbixUserEmail" placeholder="utilisateur@urbix.tn" required />
			</div>
			<div style="margin-bottom: 1em;">
				<label for="urbixUserPassword">Mot de passe</label>
				<input type="password" id="urbixUserPassword" placeholder="••••••••" required />
			</div>
			<div style="display: flex; gap: 0.5em; flex-wrap: wrap;">
				<button type="submit" id="urbixSubmitAuthBtn" class="button small">Se connecter</button>
				<button type="button" id="urbixLogoutBtn" class="button alt small" style="display: none;">Déconnexion</button>
			</div>
		</form>
	</div>
</div>

<script>
	(function () {
		const USER_KEY = 'urbix_current_user_v1';
		const backdrop = document.getElementById('urbixUserModalBackdrop');
		const openBtn = document.getElementById('btnOpenUserModal');
		const closeBtn = document.getElementById('btnCloseUrbixUserModal');
		const tabLogin = document.getElementById('urbixTabLogin');
		const tabRegister = document.getElementById('urbixTabRegister');
		const nomWrap = document.getElementById('urbixNomFieldWrap');
		const submitBtn = document.getElementById('urbixSubmitAuthBtn');
		const logoutBtn = document.getElementById('urbixLogoutBtn');
		const badge = document.getElementById('urbixUserCurrentBadge');
		const form = document.getElementById('urbixUserAuthForm');

		let mode = 'login';

		function getSavedUser() {
			try {
				return JSON.parse(localStorage.getItem(USER_KEY) || 'null');
			} catch (e) {
				return null;
			}
		}

		function refreshUserStatus() {
			const user = getSavedUser();
			if (user && user.email) {
				badge.textContent = 'Connecté : ' + (user.nom ? user.nom + ' (' + user.email + ')' : user.email);
				logoutBtn.style.display = 'inline-block';
				openBtn.title = user.nom || user.email;
			} else {
				badge.textContent = 'Statut : Invité';
				logoutBtn.style.display = 'none';
				openBtn.title = 'Connexion / User';
			}
		}

		function setMode(nextMode) {
			mode = nextMode;
			const isRegister = mode === 'register';
			nomWrap.style.display = isRegister ? 'block' : 'none';
			submitBtn.textContent = isRegister ? 'Créer le compte' : 'Se connecter';
			tabLogin.classList.toggle('alt', isRegister);
			tabRegister.classList.toggle('alt', !isRegister);
		}

		if (openBtn && backdrop) {
			openBtn.addEventListener('click', function (e) {
				e.preventDefault();
				setMode('login');
				refreshUserStatus();
				backdrop.style.display = 'flex';
			});
		}

		if (closeBtn && backdrop) {
			closeBtn.addEventListener('click', function () {
				backdrop.style.display = 'none';
			});
			backdrop.addEventListener('click', function (e) {
				if (e.target === backdrop) {
					backdrop.style.display = 'none';
				}
			});
		}

		if (tabLogin) tabLogin.addEventListener('click', function () { setMode('login'); });
		if (tabRegister) tabRegister.addEventListener('click', function () { setMode('register'); });

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				const email = document.getElementById('urbixUserEmail').value.trim();
				const nom = document.getElementById('urbixUserNom').value.trim();
				if (!email) return;
				const prev = getSavedUser();
				const userData = {
					nom: nom || (prev && prev.nom) || email.split('@')[0],
					email: email
				};
				localStorage.setItem(USER_KEY, JSON.stringify(userData));
				refreshUserStatus();
				backdrop.style.display = 'none';
			});
		}

		if (logoutBtn) {
			logoutBtn.addEventListener('click', function () {
				localStorage.removeItem(USER_KEY);
				refreshUserStatus();
				backdrop.style.display = 'none';
			});
		}

		refreshUserStatus();
	})();
</script>