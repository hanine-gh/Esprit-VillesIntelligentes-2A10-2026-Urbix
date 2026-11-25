# Urbix – Smart Cities Platform

## Overview

Urbix is a smart urban mobility platform developed to make city transportation easier, faster and more efficient.

The platform uses technology and geolocation to help citizens plan and follow their journeys.

It includes three transportation services:
- Traditional urban buses.
- Smart on-demand buses.
- Metro and train services.

Users can track vehicles in real time, check schedules, view estimated arrival times (ETA), locate stations and request home pickup for on-demand buses.

Urbix also provides an administration space to manage transportation data.

## Features

- Real-time vehicle tracking.
- Geolocation services.
- Bus schedules and ETA information.
- Station location.
- Traditional urban bus services.
- Smart on-demand bus booking.
- Home pickup service.
- Metro and train information.
- Administration dashboard.
- Add, edit, delete and manage transportation data.
  
## Tech Stack

### Frontend
- HTML
- CSS
- Java

### Backend
- PHP

### Database
- MySQL

## Architecture

Urbix is divided into two main parts:

### Front Office
The Front Office is designed for citizens and users. It allows them to:
- Explore available transportation services.
- Track vehicles in real time.
- Check schedules and estimated arrival times.
- Locate transportation stations.
- Request on-demand bus services.

### Back Office
The Back Office is designed for administrators. It allows them to:
- Manage users and transportation data.
- Add new information.
- Update existing data.
- Delete data.
- Manage transportation services.
## Contributors

This project was developed by:

- Ahmed Hajjaji
- Hassen Bessaoud
- Hanine Ghrab
- Ali Wizani
- Salma Missaoui
- Adriel Sere Obde

## Academic Context

Developed at **Esprit School of Engineering – Tunisia**.

Project: Urbix – Smart Urban Mobility Platform

Program: 2A10 – Villes Intelligentes

Academic Year: 2026–2027

## Getting Started

1. Copier le dossier dans `C:/xampp/htdocs/` (le nom du dossier n'a pas d'importance, l'URL est détectée automatiquement).
2. Démarrer **Apache** et **MySQL** dans XAMPP.
3. Dans phpMyAdmin : *Importer* → `database/smartcity.sql` (crée la base `smartcity` avec les entités de tous les modules).
4. Ouvrir `http://localhost/<dossier>/index.php?page=covoiturage`.

Sans base de données, le module covoiturage fonctionne en **mode démo** (données en session).
Compte de démonstration : `salma@urbix.tn` / `Urbix2026`.

### Module Covoiturage (interface client)

| Fichier | Rôle |
|---|---|
| `app/Controllers/CovoiturageController.php` | Recherche, accepter / annuler un trajet |
| `app/Controllers/UtilisateurController.php` | Interface utilisateur (icône à droite) : connexion, inscription, mon espace |
| `app/Models/Trajet.php`, `Reservation.php`, `Utilisateur.php` | Entités (PDO + requêtes préparées) |
| `app/Models/Recommandation.php` | Recherche intelligente : score prix / note / horaire / confort, dates proches |
| `app/Views/pages/covoiturage.php`, `espace-utilisateur.php` | Vues |
| `app/Views/assets/css/covoiturage.css`, `assets/js/covoiturage.js`, `assets/js/auth.js` | Style et contrôle de saisie JavaScript |
| `database/smartcity.sql` | Base commune : utilisateur, véhicule, station, ligne, bus, mission, incident, train, louage, location, garage, réparation, covoiturage |

## Acknowledgments

We would like to thank Esprit School of Engineering, our project supervisors and everyone who contributed to the development of Urbix.
