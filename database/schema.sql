-- ============================================================
-- DATABASE: medistock_mvc
-- Purpose: Medicine Inventory Management System (MVC version)
-- Notes:   Redesigned schema with proper indexes, constraints,
--          audit trail, and improved normalization.
-- ============================================================

CREATE DATABASE IF NOT EXISTS medistock_mvc
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE medistock_mvc;

-- ============================================================
-- TABLE: users
-- Purpose: Login credentials and role management.
-- Improvements: Added phone, last_login, login_attempts, locked_until
-- ============================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    login_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_username (username),
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: categories
-- Purpose: Medicine categories (Tablet, Syrup, etc.)
-- Improvements: Added description, sort_order, is_active
-- ============================================================
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_categories_name (name),
    INDEX idx_categories_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: suppliers
-- Purpose: Supplier information.
-- Improvements: Added contact details, is_active flag
-- ============================================================
CREATE TABLE suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_suppliers_name (name),
    INDEX idx_suppliers_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: medicines
-- Purpose: Core inventory table.
-- Improvements: Added unit, description, reorder_level,
--               created_by/updated_by audit fields, better indexes
-- ============================================================
CREATE TABLE medicines (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    category_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    batch_no VARCHAR(50) NOT NULL,
    expiry_date DATE NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL DEFAULT 'piece',
    price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2) DEFAULT NULL,
    requires_prescription TINYINT(1) NOT NULL DEFAULT 0,
    reorder_level INT NOT NULL DEFAULT 10,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED DEFAULT NULL,
    updated_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_medicines_category FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_medicines_supplier FOREIGN KEY (supplier_id)
        REFERENCES suppliers(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_medicines_created_by FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_medicines_updated_by FOREIGN KEY (updated_by)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_medicines_category (category_id),
    INDEX idx_medicines_supplier (supplier_id),
    INDEX idx_medicines_expiry (expiry_date),
    INDEX idx_medicines_batch (batch_no),
    INDEX idx_medicines_active (is_active),
    INDEX idx_medicines_low_stock (quantity, reorder_level)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: sales
-- Purpose: Records every sale/issue of medicine.
-- Improvements: Added sale_number (human-readable), created_by,
--               payment_method
-- ============================================================
CREATE TABLE sales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_number VARCHAR(20) NOT NULL,
    medicine_id INT UNSIGNED NOT NULL,
    quantity_sold INT UNSIGNED NOT NULL,
    sale_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'digital', 'credit') NOT NULL DEFAULT 'cash',
    notes TEXT DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sales_medicine FOREIGN KEY (medicine_id)
        REFERENCES medicines(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_sales_created_by FOREIGN KEY (created_by)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY uq_sales_number (sale_number),
    INDEX idx_sales_medicine (medicine_id),
    INDEX idx_sales_date (sale_date),
    INDEX idx_sales_created_by (created_by),
    INDEX idx_sales_payment (payment_method)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: prescription_details
-- Purpose: Prescription info for medicines that require one.
-- Improvements: Added license_type, better column naming
-- ============================================================
CREATE TABLE prescription_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id INT UNSIGNED NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    nmc_number VARCHAR(30) NOT NULL,
    license_type ENUM('medical', 'dental', 'ayurveda', 'other') NOT NULL DEFAULT 'medical',
    prescription_date DATE NOT NULL,
    prescription_number VARCHAR(50) DEFAULT NULL,
    hospital_name VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rxdetails_sale FOREIGN KEY (sale_id)
        REFERENCES sales(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_rxdetails_sale (sale_id),
    INDEX idx_rxdetails_doctor (doctor_name),
    INDEX idx_rxdetails_nmc (nmc_number)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: stock_adjustments
-- Purpose: Audit trail for non-sale stock changes.
-- Improvements: Added adjusted_by (user who made the change)
-- ============================================================
CREATE TABLE stock_adjustments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT UNSIGNED NOT NULL,
    quantity_change INT NOT NULL,
    reason ENUM('damaged', 'expired', 'lost', 'returned', 'correction', 'other') NOT NULL,
    note TEXT DEFAULT NULL,
    adjusted_by INT UNSIGNED DEFAULT NULL,
    adjusted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_adj_medicine FOREIGN KEY (medicine_id)
        REFERENCES medicines(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_adj_user FOREIGN KEY (adjusted_by)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_adj_medicine (medicine_id),
    INDEX idx_adj_date (adjusted_at),
    INDEX idx_adj_reason (reason)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: settings
-- Purpose: Configurable system settings (key-value).
-- ============================================================
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: audit_log
-- Purpose: Tracks important actions for security/compliance.
-- ============================================================
CREATE TABLE audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED DEFAULT NULL,
    old_values JSON DEFAULT NULL,
    new_values JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_date (created_at)
) ENGINE=InnoDB;


-- ============================================================
-- SEED DATA
-- ============================================================

-- Default categories
INSERT INTO categories (name, description, sort_order) VALUES
    ('Tablet', 'Oral solid dosage form', 1),
    ('Capsule', 'Oral solid dosage in capsule form', 2),
    ('Syrup', 'Liquid oral formulation', 3),
    ('Injection', 'Parenteral formulation', 4),
    ('Cream', 'Topical semi-solid formulation', 5),
    ('Drops', 'Ophthalmic/otic/ nasal drops', 6),
    ('Inhaler', 'Pulmonary delivery device', 7),
    ('Ointment', 'Topical semi-solid for skin application', 8);

-- Default suppliers
INSERT INTO suppliers (name, contact_person, phone, email) VALUES
    ('Himalayan Pharma', 'Ram Shrestha', '9841000001', 'ram@himalayanpharma.com'),
    ('Nepal MediCorp', 'Sita Thapa', '9841000002', 'sita@nepalmedicorp.com'),
    ('Everest Drugs', 'Kiran Rai', '9841000003', 'kiran@everestdrugs.com'),
    ('Gorkha Pharmaceuticals', 'Anita Gurung', '9841000004', 'anita@gorkhapharma.com');

-- Default system settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
    ('low_stock_threshold', '10', 'Alert when stock falls below this quantity'),
    ('expiry_alert_days', '30', 'Alert for medicines expiring within this many days'),
    ('sale_number_prefix', 'INV', 'Prefix for auto-generated sale numbers'),
    ('currency', 'NPR', 'Currency symbol for price display'),
    ('pharmacy_name', 'MediStock Pharmacy', 'Name displayed on receipts and reports');

-- Default admin user
-- username: admin  password: admin123
INSERT INTO users (username, password, full_name, email, role) VALUES
    ('admin', '$2y$10$DSHCiy56I3dsioriXBnoPeVZUazu7FFQW0hP0lyeVOl55fzdtRcwe', 'Administrator', 'admin@medistock.com', 'admin');

-- Sample medicines
INSERT INTO medicines (name, category_id, supplier_id, batch_no, expiry_date, quantity, unit, price, requires_prescription, reorder_level, created_by) VALUES
    ('Paracetamol 500mg', 1, 1, 'BATCH-2025-001', '2026-12-31', 200, 'piece', 5.00, 0, 20, 1),
    ('Amoxicillin 250mg', 2, 2, 'BATCH-2025-002', '2026-06-30', 150, 'piece', 12.00, 1, 15, 1),
    ('Cough Syrup DX', 3, 1, 'BATCH-2025-003', '2026-09-15', 80, 'bottle', 85.00, 0, 10, 1),
    ('Diclofenac Injection', 4, 3, 'BATCH-2025-004', '2025-12-31', 50, 'piece', 45.00, 1, 10, 1),
    ('Betamethasone Cream', 5, 2, 'BATCH-2025-005', '2026-03-20', 35, 'tube', 65.00, 0, 8, 1),
    ('Omeprazole 20mg', 1, 4, 'BATCH-2025-006', '2027-01-15', 120, 'piece', 18.00, 0, 15, 1),
    ('Cetirizine 10mg', 1, 1, 'BATCH-2025-007', '2026-11-30', 90, 'piece', 8.00, 0, 10, 1),
    ('Metformin 500mg', 1, 3, 'BATCH-2025-008', '2026-08-25', 180, 'piece', 22.00, 1, 20, 1),
    ('Salbutamol Inhaler', 7, 4, 'BATCH-2025-009', '2026-07-10', 25, 'piece', 320.00, 1, 5, 1),
    ('Azithromycin 500mg', 1, 2, 'BATCH-2025-010', '2025-09-01', 3, 'piece', 35.00, 1, 10, 1);
