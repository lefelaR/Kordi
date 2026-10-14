ALTER TABLE venues
    ADD COLUMN latitude DECIMAL(10, 7) NULL AFTER details,
    ADD COLUMN longitude DECIMAL(10, 7) NULL AFTER latitude;
