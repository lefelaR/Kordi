ALTER TABLE vendors
    ADD COLUMN location VARCHAR(180) NULL AFTER name,
    ADD COLUMN price DECIMAL(12, 2) NULL AFTER location,
    ADD COLUMN capacity INT UNSIGNED NULL AFTER price;

ALTER TABLE vendors
    DROP COLUMN contact,
    DROP COLUMN email,
    DROP COLUMN phone;
