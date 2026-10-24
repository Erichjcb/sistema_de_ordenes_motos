ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS ciudad VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS distrito VARCHAR(100) NULL;

ALTER TABLE pedidos
    ADD COLUMN IF NOT EXISTS ciudad VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS distrito VARCHAR(100) NULL;

ALTER TABLE catalogo_productos
    ADD COLUMN IF NOT EXISTS imagen_url VARCHAR(500) NULL;

UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/172554/FFFFFF?text=Llanta+delantera' WHERE sku = 'LLA-FR-9090-17' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/1E3A8A/FFFFFF?text=Llanta+trasera' WHERE sku = 'LLA-TR-11090-17' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/334155/FFFFFF?text=Bujia+NGK' WHERE sku = 'BUJ-NGK-CR7HSA' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/365314/FFFFFF?text=Filtro+de+aceite' WHERE sku = 'FIL-ACE-GEN' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/7F1D1D/FFFFFF?text=Pastillas+de+freno' WHERE sku = 'PAS-FRE-DEL-GEN' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/713F12/FFFFFF?text=Bateria+12V' WHERE sku = 'BAT-12V-5AH' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/3F3F46/FFFFFF?text=Kit+de+arrastre' WHERE sku = 'KIT-ARR-428' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/064E3B/FFFFFF?text=Aceite+4T' WHERE sku = 'ACE-4T-20W50-1L' AND imagen_url IS NULL;
UPDATE catalogo_productos SET imagen_url = 'https://placehold.co/640x420/581C87/FFFFFF?text=Cable+de+embrague' WHERE sku = 'CAB-EMB-GEN' AND imagen_url IS NULL;
