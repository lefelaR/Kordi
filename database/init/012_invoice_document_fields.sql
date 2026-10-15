ALTER TABLE invoices
    ADD COLUMN invoice_number VARCHAR(40) NULL AFTER company_id,
    ADD COLUMN due_date DATE NULL AFTER invoice_date,
    ADD COLUMN event_label VARCHAR(255) NULL AFTER title,
    ADD COLUMN venue VARCHAR(255) NULL AFTER event_label;

UPDATE invoices
SET invoice_number = CONCAT('INV-', LPAD(id, 3, '0'))
WHERE invoice_number IS NULL OR invoice_number = '';
