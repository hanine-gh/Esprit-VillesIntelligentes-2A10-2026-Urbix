<?php

require_once __DIR__ . '/../Models/MobiliteModel.php';

class MobiliteController {
	protected function view($view, $data = []) {
		extract($data, EXTR_SKIP);
		$viewFile = __DIR__ . '/../Views/' . ltrim($view, '/') . '.php';
		if (file_exists($viewFile)) {
			require $viewFile;
		} else {
			echo "Vue introuvable : " . htmlspecialchars($viewFile);
		}
	}

	private function loadModelData() {
		$model = new MobiliteModel();
		return [$model, $model->getState()];
	}

	public function index() {
		[$model, $state] = $this->loadModelData();
		if (isset($_GET['type']) && in_array($_GET['type'], ['moto', 'trottinette', 'velo', 'tous'], true)) {
			$state['filtre'] = $_GET['type'];
			$model->saveState($state);
		}
		$this->view('pages/mobilite', ['state' => $state]);
	}

	public function backoffice() {
		[, $state] = $this->loadModelData();
		$this->view('pages/backoffice_mobilite', ['state' => $state]);
	}

	public function reserver() {
		[, $state] = $this->loadModelData();
		$this->view('pages/mobilite_action', ['state' => $state, 'action' => 'reserver']);
	}

	public function demarrer() {
		[, $state] = $this->loadModelData();
		$this->view('pages/mobilite_action', ['state' => $state, 'action' => 'demarrer']);
	}

	public function prolonger() {
		[, $state] = $this->loadModelData();
		$this->view('pages/mobilite_action', ['state' => $state, 'action' => 'prolonger']);
	}

	public function incident() {
		[, $state] = $this->loadModelData();
		$this->view('pages/mobilite_action', ['state' => $state, 'action' => 'incident']);
	}

	public function terminer() {
		[, $state] = $this->loadModelData();
		$this->view('pages/mobilite_action', ['state' => $state, 'action' => 'terminer']);
	}

	public function annuler() {
		[, $state] = $this->loadModelData();
		$this->view('pages/mobilite_action', ['state' => $state, 'action' => 'annuler']);
	}
}