-- =====================================================================
-- Gestion Location : renommage des tables (mobilite_... -> location_...)
-- À importer UNE SEULE FOIS dans phpMyAdmin > base urbix_db > onglet Import.
-- Les données et les jointures (clés étrangères) sont conservées.
-- =====================================================================
USE urbix_db;

RENAME TABLE mobilite_vehicules TO location_vehicules,
             mobilite_locations TO location_locations;
