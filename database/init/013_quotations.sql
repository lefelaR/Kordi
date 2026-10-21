CREATE TABLE IF NOT EXISTS quotations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    quotation_number VARCHAR(40) NULL,
    title VARCHAR(180) NOT NULL,
    event_label VARCHAR(255) NULL,
    venue VARCHAR(255) NULL,
    amount DECIMAL(12, 2) NULL,
    quotation_date DATE NULL,
    due_date DATE NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quotations_company
        FOREIGN KEY (company_id) REFERENCES companies (id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quotation_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quotation_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(12, 2) NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_quotation_items_quotation
        FOREIGN KEY (quotation_id) REFERENCES quotations (id)
        ON DELETE CASCADE
);
