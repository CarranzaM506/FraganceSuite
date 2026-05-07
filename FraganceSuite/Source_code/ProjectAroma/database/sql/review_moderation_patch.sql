-- Ejecutar en la base de datos `aroma`
-- Agrega columnas para moderación de reseñas
ALTER TABLE `review`
    ADD COLUMN `is_blocked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `comment`,
    ADD COLUMN `moderation_reason` VARCHAR(120) NULL AFTER `is_blocked`;

-- Marcar reseñas existentes como visibles
UPDATE `review`
SET `is_blocked` = 0
WHERE `is_blocked` IS NULL;
