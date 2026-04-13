-- =============================================
-- ZACK PLATFORM - COMPLETE DATABASE SCHEMA
-- MySQL / MariaDB
-- =============================================

CREATE DATABASE IF NOT EXISTS ecom
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE ecom;

-- 1. USERS TABLE (Institution, Parent, Student, Admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('institution', 'parent', 'student', 'admin') NOT NULL DEFAULT 'parent',
    
    -- Institution only fields
    school_code VARCHAR(50) UNIQUE DEFAULT NULL,
    po_box VARCHAR(100) DEFAULT NULL,
    
    -- Common fields
    county VARCHAR(100) DEFAULT NULL,
    institution_id INT DEFAULT NULL,        -- For parents/students: which school they belong to
    
    status ENUM('pending', 'active', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (institution_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default Admin Account (Password: admin123)
-- Change password immediately after first login!
INSERT INTO users (name, email, phone, password_hash, role, status) 
VALUES ('ZACK Super Admin', 'admin@zack.com', '0700000000', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'admin', 'active');

-- 2. PRODUCTS TABLE
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    category ENUM('stationery', 'uniform', 'lab', 'kit_component', 'other') NOT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    stock INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample Products
INSERT INTO products (name, description, base_price, category, stock) VALUES
('Mathematical Set (Box of 50)', 'Complete geometry set', 150.00, 'stationery', 500),
('A4 Exercise Books (Carton)', '100 books per carton', 85.00, 'stationery', 300),
('School Blazer', 'Premium quality school blazer', 560.00, 'uniform', 200),
('Lab Coat (Pack of 20)', 'White lab coat', 200.00, 'lab', 150),
('School Backpack', 'Durable student backpack', 450.00, 'uniform', 400);

-- 3. KITS TABLE (Full Kit, Level & Class Specific)
CREATE TABLE kits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,           -- e.g. "Form 1 Full Kit"
    level VARCHAR(100) NOT NULL,          -- Pre Primary, Primary, Junior Secondary, High School
    class_grade VARCHAR(50) NOT NULL,     -- Grade 1, Form 1, Form 2, etc.
    institution_id INT DEFAULT NULL,      -- NULL = Global kit, else school-specific
    total_base_price DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (institution_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. KIT ITEMS (Products inside each kit)
CREATE TABLE kit_items (
    kit_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    PRIMARY KEY (kit_id, product_id),
    FOREIGN KEY (kit_id) REFERENCES kits(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. ORDERS TABLE (Supports all order types)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                    -- Who placed the order
    order_type ENUM(
        'full_kit', 
        'individual_items', 
        'top_up_restock', 
        'bulk_order', 
        'quotation_request', 
        'contract_inquiry'
    ) NOT NULL,
    institution_id INT NOT NULL,             -- Which school the order is for
    status ENUM('pending', 'quoted', 'approved', 'paid', 'processing', 'shipped', 'delivered', 'cancelled') 
        DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (institution_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- 6. ORDER ITEMS
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,          -- For individual items
    kit_id INT NULL,              -- For full kit orders
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL, -- Price at time of order
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (kit_id) REFERENCES kits(id)
) ENGINE=InnoDB;

-- 7. QUOTATION RESPONSES (Admin replies to quotation requests)
CREATE TABLE quotation_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    admin_id INT NOT NULL,
    quoted_total DECIMAL(10,2) NOT NULL,
    response_notes TEXT,
    responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- =============================================
-- USEFUL INDEXES
-- =============================================
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_phone ON users(phone);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_institution ON orders(institution_id);
CREATE INDEX idx_orders_type_status ON orders(order_type, status);

-- =============================================
-- HELPFUL VIEWS FOR ADMIN REPORTS
-- =============================================
CREATE OR REPLACE VIEW vw_active_institutions AS
SELECT id, name, school_code, county, created_at 
FROM users 
WHERE role = 'institution' AND status = 'active';

CREATE OR REPLACE VIEW vw_order_summary AS
SELECT 
    o.id AS order_id,
    u.name AS customer,
    i.name AS school,
    o.order_type,
    o.status,
    o.total_amount,
    o.created_at
FROM orders o
JOIN users u ON o.user_id = u.id
JOIN users i ON o.institution_id = i.id;