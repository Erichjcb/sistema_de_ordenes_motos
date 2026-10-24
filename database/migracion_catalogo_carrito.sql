CREATE TABLE IF NOT EXISTS catalogo_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(40) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    imagen_url VARCHAR(500) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS catalogo_proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS catalogo_ofertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    proveedor_id INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_catalogo_producto_proveedor (producto_id, proveedor_id),
    CONSTRAINT fk_ofertas_producto FOREIGN KEY (producto_id) REFERENCES catalogo_productos(id),
    CONSTRAINT fk_ofertas_proveedor FOREIGN KEY (proveedor_id) REFERENCES catalogo_proveedores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS carritos_items (
    customer_id INT NOT NULL,
    oferta_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_referencia DECIMAL(10,2) NOT NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id, oferta_id),
    CONSTRAINT fk_carritos_customer FOREIGN KEY (customer_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_carritos_oferta FOREIGN KEY (oferta_id) REFERENCES catalogo_ofertas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) NULL UNIQUE,
    customer_id INT NOT NULL,
    fecha DATETIME NOT NULL,
    tipo_envio ENUM('local', 'nacional') NOT NULL,
    destino VARCHAR(100) NOT NULL,
    titular VARCHAR(120) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NULL,
    distrito VARCHAR(100) NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    costo_envio DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(40) NOT NULL,
    estado_pago VARCHAR(40) NOT NULL,
    CONSTRAINT fk_pedidos_customer FOREIGN KEY (customer_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedido_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    oferta_id INT NOT NULL,
    sku VARCHAR(40) NOT NULL,
    producto VARCHAR(120) NOT NULL,
    proveedor VARCHAR(120) NOT NULL,
    cantidad INT NOT NULL,
    cantidad_asignada INT NOT NULL,
    cantidad_backorder INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_pedido_items_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_pedido_items_oferta FOREIGN KEY (oferta_id) REFERENCES catalogo_ofertas(id),
    INDEX idx_pedido_items_backorder (oferta_id, cantidad_backorder, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventario_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    oferta_id INT NOT NULL,
    pedido_item_id INT NULL,
    tipo ENUM('venta', 'reposicion', 'cancelacion', 'asignacion_backorder') NOT NULL,
    cantidad INT NOT NULL,
    fecha DATETIME NOT NULL,
    detalle VARCHAR(255) NOT NULL,
    CONSTRAINT fk_movimientos_oferta FOREIGN KEY (oferta_id) REFERENCES catalogo_ofertas(id),
    CONSTRAINT fk_movimientos_pedido_item FOREIGN KEY (pedido_item_id) REFERENCES pedido_items(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO catalogo_productos (sku, nombre, descripcion, imagen_url) VALUES
('LLA-FR-9090-17', 'Llanta delantera 90/90-17', 'Llanta aftermarket de demostración, medida 90/90-17. Verificar compatibilidad antes de instalar.', 'https://placehold.co/640x420/172554/FFFFFF?text=Llanta+delantera'),
('LLA-TR-11090-17', 'Llanta trasera 110/90-17', 'Llanta aftermarket de demostración, medida 110/90-17. Verificar compatibilidad antes de instalar.', 'https://placehold.co/640x420/1E3A8A/FFFFFF?text=Llanta+trasera'),
('BUJ-NGK-CR7HSA', 'Bujía NGK CR7HSA', 'Bujía NGK de demostración, referencia CR7HSA.', 'https://placehold.co/640x420/334155/FFFFFF?text=Bujia+NGK'),
('FIL-ACE-GEN', 'Filtro de aceite (genérico)', 'Filtro de aceite genérico de demostración. Confirmar aplicación con el proveedor.', 'https://placehold.co/640x420/365314/FFFFFF?text=Filtro+de+aceite'),
('PAS-FRE-DEL-GEN', 'Pastillas de freno delanteras (genéricas)', 'Pastillas delanteras genéricas de demostración. Confirmar forma y compatibilidad.', 'https://placehold.co/640x420/7F1D1D/FFFFFF?text=Pastillas+de+freno'),
('BAT-12V-5AH', 'Batería 12 V 5 Ah', 'Batería de demostración de 12 V y 5 Ah. Verificar dimensiones y polaridad.', 'https://placehold.co/640x420/713F12/FFFFFF?text=Bateria+12V'),
('KIT-ARR-428', 'Kit de arrastre cadena 428', 'Kit de arrastre de demostración con cadena paso 428.', 'https://placehold.co/640x420/3F3F46/FFFFFF?text=Kit+de+arrastre'),
('ACE-4T-20W50-1L', 'Aceite 4T 20W-50 (1 L)', 'Aceite para motor 4T de demostración, viscosidad 20W-50, envase de 1 litro.', 'https://placehold.co/640x420/064E3B/FFFFFF?text=Aceite+4T'),
('CAB-EMB-GEN', 'Cable de embrague (genérico)', 'Cable de embrague genérico de demostración. Confirmar largo y terminales.', 'https://placehold.co/640x420/581C87/FFFFFF?text=Cable+de+embrague');

INSERT IGNORE INTO catalogo_proveedores (nombre) VALUES
('MotoPartes S.A.'),
('Repuestos El Motorista'),
('Importaciones TodoMoto'),
('Distribuidora Central'),
('Yamaha Oficial');

INSERT IGNORE INTO catalogo_ofertas (producto_id, proveedor_id, precio, stock)
SELECT p.id, pr.id, datos.precio, datos.stock
FROM (
    SELECT 'LLA-FR-9090-17' sku, 'MotoPartes S.A.' proveedor, 125.00 precio, 8 stock UNION ALL
    SELECT 'LLA-FR-9090-17', 'Importaciones TodoMoto', 145.00, 5 UNION ALL
    SELECT 'LLA-TR-11090-17', 'MotoPartes S.A.', 145.00, 6 UNION ALL
    SELECT 'LLA-TR-11090-17', 'Importaciones TodoMoto', 168.00, 4 UNION ALL
    SELECT 'BUJ-NGK-CR7HSA', 'Repuestos El Motorista', 18.00, 20 UNION ALL
    SELECT 'BUJ-NGK-CR7HSA', 'MotoPartes S.A.', 22.00, 12 UNION ALL
    SELECT 'FIL-ACE-GEN', 'Repuestos El Motorista', 15.00, 15 UNION ALL
    SELECT 'FIL-ACE-GEN', 'Distribuidora Central', 19.00, 10 UNION ALL
    SELECT 'PAS-FRE-DEL-GEN', 'MotoPartes S.A.', 28.00, 10 UNION ALL
    SELECT 'PAS-FRE-DEL-GEN', 'Distribuidora Central', 35.00, 7 UNION ALL
    SELECT 'BAT-12V-5AH', 'Importaciones TodoMoto', 75.00, 6 UNION ALL
    SELECT 'BAT-12V-5AH', 'Yamaha Oficial', 89.00, 4 UNION ALL
    SELECT 'KIT-ARR-428', 'Repuestos El Motorista', 95.00, 5 UNION ALL
    SELECT 'KIT-ARR-428', 'Importaciones TodoMoto', 125.00, 3 UNION ALL
    SELECT 'ACE-4T-20W50-1L', 'Distribuidora Central', 29.00, 18 UNION ALL
    SELECT 'ACE-4T-20W50-1L', 'Yamaha Oficial', 36.00, 12 UNION ALL
    SELECT 'CAB-EMB-GEN', 'MotoPartes S.A.', 18.00, 10 UNION ALL
    SELECT 'CAB-EMB-GEN', 'Repuestos El Motorista', 24.00, 8
) datos
JOIN catalogo_productos p ON p.sku = datos.sku
JOIN catalogo_proveedores pr ON pr.nombre = datos.proveedor;
