-- =====================================================================
-- Migration 001 — Promotions sur les équipements
-- Ajoute le flag "en promo" et le prix promo aux équipements.
-- Exécuter dans phpMyAdmin (ou : mysql -u root Rentova < ce fichier)
-- =====================================================================

ALTER TABLE equipements
    ADD COLUMN en_promo   TINYINT(1)     NOT NULL DEFAULT 0
        AFTER prix_jour,
    ADD COLUMN prix_promo DECIMAL(10,2)  NULL DEFAULT NULL
        AFTER en_promo;
