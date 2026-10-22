CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'proveedor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE ordenes
    ADD COLUMN customer_id INT NULL,
    ADD CONSTRAINT fk_ordenes_customer
        FOREIGN KEY (customer_id) REFERENCES usuarios(id) ON DELETE SET NULL;

INSERT IGNORE INTO usuarios (usuario, password_hash, rol) VALUES
('customer1', '$2y$10$A6Y.xJ9RbsOxXOoF4hS0Ee6NjT.pbto87jVxeIwnTeu3wighEJHNC', 'cliente'),
('customer2', '$2y$10$UigROBcW7I3V6ZW0lLLY1.Sw.S.6.TGJqRR4MYUPyiCM7ftEbrX2G', 'cliente'),
('customer3', '$2y$10$63QtbL6MGmsKQ56tDT./8OQJr3RRtvp2aSQOn4kcTqPPvQFn76IjG', 'cliente'),
('supplier.admin', '$2y$10$8AVEFCg1QytZUEjGuRD3AusESZW8WGhwMxiKXNod0YoNUnPAdAsAa', 'proveedor');
