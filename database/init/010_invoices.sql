CREATE TABLE IF NOT EXISTS invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(40) NULL,
    title VARCHAR(180) NOT NULL,
    event_label VARCHAR(255) NULL,
    venue VARCHAR(255) NULL,
    amount DECIMAL(12, 2) NULL,
    invoice_date DATE NULL,
    due_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoices_company
        FOREIGN KEY (company_id) REFERENCES companies (id)
        ON DELETE CASCADE
);
