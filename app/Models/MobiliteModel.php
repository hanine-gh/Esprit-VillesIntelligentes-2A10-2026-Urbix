<?php

class MobiliteModel {
	public function __construct() {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		if (!isset($_SESSION['urbix_mobilite'])) {
			$_SESSION['urbix_mobilite'] = $this->getDefaultData();
		}
	}

	private function getDefaultData() {
		return [
			'filtre' => 'moto',
			'selectedVehiculeId' => 'V1',
			'selectedLocationId' => '',
			'vehicules' => [
				['id' => 'V1', 'nom' => 'Moto Urbix X125', 'type' => 'moto', 'tarifHeure' => 12, 'autonomie' => '88%', 'station' => 'Station Centre Ville', 'statut' => 'disponible', 'noteMoyenne' => 4.8, 'avisCount' => 14, 'image' => 'urbix-moto.png'],
				['id' => 'V2', 'nom' => 'Moto VoltRoute 250', 'type' => 'moto', 'tarifHeure' => 15, 'autonomie' => '94%', 'station' => 'Station Parc Urbain', 'statut' => 'disponible', 'noteMoyenne' => 4.7, 'avisCount' => 11, 'image' => 'voltrider.png'],
				['id' => 'V3', 'nom' => 'Trottinette ScootCity Pro', 'type' => 'trottinette', 'tarifHeure' => 4, 'autonomie' => '92%', 'station' => 'Station Campus Nord', 'statut' => 'disponible', 'noteMoyenne' => 4.6, 'avisCount' => 22, 'image' => 'scootcity.png'],
				['id' => 'V4', 'nom' => 'Trottinette GlideMax', 'type' => 'trottinette', 'tarifHeure' => 5, 'autonomie' => '76%', 'station' => 'Station Gare Centrale', 'statut' => 'disponible', 'noteMoyenne' => 4.5, 'avisCount' => 16, 'image' => 'glidemax.png'],
				['id' => 'V5', 'nom' => 'Vélo Urbain V-Assist', 'type' => 'velo', 'tarifHeure' => 5, 'autonomie' => '81%', 'station' => 'Station Gare Centrale', 'statut' => 'disponible', 'noteMoyenne' => 4.9, 'avisCount' => 19, 'image' => 'velo-vassist.png'],
				['id' => 'V6', 'nom' => 'Vélo EcoRide City', 'type' => 'velo', 'tarifHeure' => 3, 'autonomie' => '100%', 'station' => 'Station Centre Ville', 'statut' => 'disponible', 'noteMoyenne' => 4.8, 'avisCount' => 27, 'image' => 'ecoride.png'],
			],
			'locations' => []
		];
	}

	public function getState() {
		return $_SESSION['urbix_mobilite'];
	}

	public function saveState(array $state) {
		$_SESSION['urbix_mobilite'] = $state;
	}

	public function resetState() {
		$_SESSION['urbix_mobilite'] = $this->getDefaultData();
		return $_SESSION['urbix_mobilite'];
	}
}