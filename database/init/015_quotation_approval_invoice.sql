ALTER TABLE quotations
    ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER send_status;

ALTER TABLE invoices
    ADD COLUMN quotation_id BIGINT UNSIGNED NULL AFTER company_id,
    ADD COLUMN percentage DECIMAL(5, 2) NULL AFTER amount,
    ADD CONSTRAINT fk_invoices_quotation
        FOREIGN KEY (quotation_id) REFERENCES quotations (id)
        ON DELETE SET NULL;
