<?php
require_once 'Conexion.php';

class TiendaDAO extends Conexion {
    public function listarCatalogo() {
        $conexion = $this->conectar();
        $sql = "SELECT o.id AS oferta_id, p.sku, p.nombre AS producto, p.descripcion, p.imagen_url, pr.nombre AS proveedor, o.precio, o.stock
                FROM catalogo_ofertas o
                JOIN catalogo_productos p ON p.id = o.producto_id
                JOIN catalogo_proveedores pr ON pr.id = o.proveedor_id
                WHERE o.activo = 1 AND p.activo = 1 AND pr.activo = 1
                ORDER BY p.nombre, pr.nombre";
        return $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerOferta($ofertaId) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("SELECT o.id AS oferta_id, p.sku, p.nombre AS producto, p.descripcion, p.imagen_url, pr.nombre AS proveedor, o.precio, o.stock
                                    FROM catalogo_ofertas o
                                    JOIN catalogo_productos p ON p.id = o.producto_id
                                    JOIN catalogo_proveedores pr ON pr.id = o.proveedor_id
                                    WHERE o.id = ? AND o.activo = 1 AND p.activo = 1 AND pr.activo = 1");
        $stmt->execute([$ofertaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarCarrito($customerId) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("SELECT c.oferta_id, c.cantidad, c.precio_referencia, p.sku, p.nombre AS producto, p.descripcion, p.imagen_url, pr.nombre AS proveedor, o.precio, o.stock
                                    FROM carritos_items c
                                    JOIN catalogo_ofertas o ON o.id = c.oferta_id
                                    JOIN catalogo_productos p ON p.id = o.producto_id
                                    JOIN catalogo_proveedores pr ON pr.id = o.proveedor_id
                                    WHERE c.customer_id = ? ORDER BY p.nombre, pr.nombre");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarAlCarrito($customerId, $ofertaId, $cantidad) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("INSERT INTO carritos_items (customer_id, oferta_id, cantidad, precio_referencia)
                                    SELECT ?, o.id, ?, o.precio
                                    FROM catalogo_ofertas o
                                    JOIN catalogo_productos p ON p.id = o.producto_id
                                    WHERE o.id = ? AND o.activo = 1 AND p.activo = 1
                                    ON DUPLICATE KEY UPDATE cantidad = LEAST(cantidad + VALUES(cantidad), 999)");
        $stmt->execute([$customerId, $cantidad, $ofertaId]);
        return $stmt->rowCount() > 0;
    }

    public function actualizarCarrito($customerId, $ofertaId, $cantidad) {
        $conexion = $this->conectar();
        if ($cantidad < 1) {
            $stmt = $conexion->prepare("DELETE FROM carritos_items WHERE customer_id = ? AND oferta_id = ?");
            return $stmt->execute([$customerId, $ofertaId]);
        }
        $stmt = $conexion->prepare("UPDATE carritos_items SET cantidad = ? WHERE customer_id = ? AND oferta_id = ?");
        return $stmt->execute([$cantidad, $customerId, $ofertaId]);
    }

    public function fusionarCarritoInvitado($customerId, $carrito) {
        if (!$carrito) {
            return;
        }
        $conexion = $this->conectar();
        $conexion->beginTransaction();
        try {
            $stmt = $conexion->prepare("INSERT INTO carritos_items (customer_id, oferta_id, cantidad, precio_referencia)
                                        SELECT ?, o.id, ?, ?
                                        FROM catalogo_ofertas o
                                        WHERE o.id = ? AND o.activo = 1
                                        ON DUPLICATE KEY UPDATE cantidad = LEAST(cantidad + VALUES(cantidad), 999)");
            foreach ($carrito as $ofertaId => $cantidad) {
                if (is_array($cantidad)) {
                    $cantidadInvitado = (int) ($cantidad['cantidad'] ?? 0);
                    $precioReferencia = (float) ($cantidad['precio_referencia'] ?? 0);
                } else {
                    $cantidadInvitado = (int) $cantidad;
                    $precioReferencia = 0;
                }
                if ($cantidadInvitado > 0) {
                    $stmt->execute([$customerId, $cantidadInvitado, $precioReferencia, $ofertaId]);
                }
            }
            $conexion->commit();
        } catch (Throwable $e) {
            $conexion->rollBack();
            throw $e;
        }
    }

    public function crearPedido($customerId, $datosEnvio, $ciudad, $distrito, $guardarDireccion, $aceptarCambioPrecio) {
        $conexion = $this->conectar();
        $conexion->beginTransaction();
        try {
            $stmt = $conexion->prepare("SELECT c.oferta_id, c.cantidad, c.precio_referencia, o.precio, o.stock, p.sku, p.nombre AS producto, pr.nombre AS proveedor
                                        FROM carritos_items c
                                        JOIN catalogo_ofertas o ON o.id = c.oferta_id
                                        JOIN catalogo_productos p ON p.id = o.producto_id
                                        JOIN catalogo_proveedores pr ON pr.id = o.proveedor_id
                                        WHERE c.customer_id = ? AND o.activo = 1 AND p.activo = 1 AND pr.activo = 1
                                        ORDER BY c.oferta_id FOR UPDATE");
            $stmt->execute([$customerId]);
            $lineas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$lineas) {
                throw new RuntimeException('El carrito está vacío o ya no hay ofertas activas.');
            }

            $cambioPrecio = false;
            foreach ($lineas as $linea) {
                if (abs((float) $linea['precio_referencia'] - (float) $linea['precio']) >= 0.005) {
                    $cambioPrecio = true;
                    break;
                }
            }
            if ($cambioPrecio && !$aceptarCambioPrecio) {
                throw new RuntimeException('El precio de uno o más productos cambió. Revisa el total actualizado y confirma el nuevo precio.');
            }

            $subtotal = 0;
            foreach ($lineas as $linea) {
                $subtotal += (float) $linea['precio'] * (int) $linea['cantidad'];
            }
            $ciudadNormalizada = strtolower(trim($ciudad));
            $esEnvioLocal = in_array($ciudadNormalizada, ['lima', 'oxapampa'], true);
            $tipoEnvio = $esEnvioLocal ? 'local' : 'nacional';
            $costoEnvio = $esEnvioLocal ? 10.00 : 50.00;
            $total = $subtotal + $costoEnvio;

            $stmt = $conexion->prepare("INSERT INTO pedidos (numero, customer_id, fecha, tipo_envio, destino, titular, direccion, ciudad, distrito, subtotal, costo_envio, total, estado, estado_pago)
                                        VALUES (NULL, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Procesando', 'Aprobado (demo)')");
            $stmt->execute([
                $customerId, $tipoEnvio, $ciudad, $datosEnvio['titular'], $datosEnvio['direccion'],
                $ciudad, $distrito, $subtotal, $costoEnvio, $total
            ]);
            $pedidoId = (int) $conexion->lastInsertId();
            $numero = 'PED-' . str_pad((string) $pedidoId, 6, '0', STR_PAD_LEFT);
            $stmt = $conexion->prepare("UPDATE pedidos SET numero = ? WHERE id = ?");
            $stmt->execute([$numero, $pedidoId]);

            if ($guardarDireccion) {
                $stmt = $conexion->prepare("UPDATE usuarios SET ciudad = ?, distrito = ? WHERE id = ? AND rol = 'cliente'");
                $stmt->execute([$ciudad, $distrito, $customerId]);
            }

            $insertarLinea = $conexion->prepare("INSERT INTO pedido_items
                (pedido_id, oferta_id, sku, producto, proveedor, cantidad, cantidad_asignada, cantidad_backorder, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $actualizarStock = $conexion->prepare("UPDATE catalogo_ofertas SET stock = stock - ? WHERE id = ? AND stock >= ?");
            $movimiento = $conexion->prepare("INSERT INTO inventario_movimientos (oferta_id, pedido_item_id, tipo, cantidad, fecha, detalle)
                                               VALUES (?, ?, 'venta', ?, NOW(), ?)");
            $hayBackorder = false;

            foreach ($lineas as $linea) {
                $cantidad = (int) $linea['cantidad'];
                $asignada = min($cantidad, (int) $linea['stock']);
                $pendiente = $cantidad - $asignada;
                $lineaSubtotal = (float) $linea['precio'] * $cantidad;
                $insertarLinea->execute([
                    $pedidoId, $linea['oferta_id'], $linea['sku'], $linea['producto'], $linea['proveedor'],
                    $cantidad, $asignada, $pendiente, $linea['precio'], $lineaSubtotal
                ]);
                $pedidoItemId = (int) $conexion->lastInsertId();

                if ($asignada > 0) {
                    $actualizarStock->execute([$asignada, $linea['oferta_id'], $asignada]);
                    if ($actualizarStock->rowCount() !== 1) {
                        throw new RuntimeException('Cambió la disponibilidad durante el checkout. Revisa el carrito e inténtalo nuevamente.');
                    }
                    $movimiento->execute([
                        $linea['oferta_id'], $pedidoItemId, $asignada,
                        'Asignación al pedido ' . $numero
                    ]);
                }
                if ($pendiente > 0) {
                    $hayBackorder = true;
                }
            }

            if ($hayBackorder) {
                $stmt = $conexion->prepare("UPDATE pedidos SET estado = 'Pendiente de abastecimiento' WHERE id = ?");
                $stmt->execute([$pedidoId]);
            }
            $stmt = $conexion->prepare("DELETE FROM carritos_items WHERE customer_id = ?");
            $stmt->execute([$customerId]);
            $conexion->commit();

            return ['id' => $pedidoId, 'numero' => $numero, 'total' => $total, 'estado' => $hayBackorder ? 'Pendiente de abastecimiento' : 'Procesando'];
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerPedidoCompleto($pedidoId, $customerId) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("SELECT id, numero, customer_id, fecha, tipo_envio, destino, titular, direccion, ciudad, distrito,
                                           subtotal, costo_envio, total, estado, estado_pago,
                                           COALESCE((SELECT SUM(cantidad_backorder) FROM pedido_items WHERE pedido_id = pedidos.id), 0) AS unidades_backorder
                                    FROM pedidos
                                    WHERE id = ? AND customer_id = ?");
        $stmt->execute([$pedidoId, $customerId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pedido) {
            return null;
        }
        $stmt = $conexion->prepare("SELECT sku, producto, proveedor, cantidad, cantidad_asignada, cantidad_backorder,
                                           precio_unitario, subtotal
                                    FROM pedido_items
                                    WHERE pedido_id = ?
                                    ORDER BY id");
        $stmt->execute([$pedidoId]);
        $pedido['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $pedido;
    }

    public function hayCambiosPrecio($lineas) {
        foreach ($lineas as $linea) {
            if (isset($linea['precio_referencia']) && abs((float) $linea['precio_referencia'] - (float) $linea['precio']) >= 0.005) {
                return true;
            }
        }
        return false;
    }

    public function listarPedidos($customerId = null) {
        $conexion = $this->conectar();
        $sql = "SELECT p.*, u.usuario, items.resumen_items, items.unidades_asignadas, items.unidades_backorder
                FROM pedidos p
                JOIN usuarios u ON u.id = p.customer_id
                JOIN (
                    SELECT pedido_id,
                           GROUP_CONCAT(CONCAT(producto, ' x', cantidad, ' (', proveedor, ')') ORDER BY id SEPARATOR '; ') AS resumen_items,
                           SUM(cantidad_asignada) AS unidades_asignadas,
                           SUM(cantidad_backorder) AS unidades_backorder
                    FROM pedido_items
                    GROUP BY pedido_id
                ) items ON items.pedido_id = p.id";
        if ($customerId !== null) {
            $sql .= " WHERE p.customer_id = ?";
        }
        $sql .= " ORDER BY p.id DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute($customerId === null ? [] : [$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarOfertasAdministracion() {
        $conexion = $this->conectar();
        return $conexion->query("SELECT o.id AS oferta_id, p.id AS producto_id, pr.id AS proveedor_id, p.sku, p.nombre AS producto, p.descripcion, p.imagen_url, pr.nombre AS proveedor, o.precio, o.stock, o.activo
                                 FROM catalogo_ofertas o
                                 JOIN catalogo_productos p ON p.id = o.producto_id
                                 JOIN catalogo_proveedores pr ON pr.id = o.proveedor_id
                                 ORDER BY p.nombre, pr.nombre")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarProveedores() {
        $conexion = $this->conectar();
        return $conexion->query("SELECT id, nombre FROM catalogo_proveedores WHERE activo = 1 ORDER BY nombre")
                        ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarProductoOferta($sku, $nombre, $descripcion, $imagenUrl, $proveedorId, $precio, $stock) {
        $conexion = $this->conectar();
        $conexion->beginTransaction();
        try {
            $stmt = $conexion->prepare("SELECT id FROM catalogo_productos WHERE sku = ? FOR UPDATE");
            $stmt->execute([$sku]);
            $productoId = $stmt->fetchColumn();
            if ($productoId) {
                $stmt = $conexion->prepare("SELECT nombre, descripcion FROM catalogo_productos WHERE id = ?");
                $stmt->execute([$productoId]);
                $productoExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($productoExistente['nombre'] !== $nombre) {
                    throw new RuntimeException('Ese SKU ya pertenece a otro producto. Revisa el catálogo antes de reutilizarlo.');
                }
                if (strpos($imagenUrl, 'assets/uploads/catalogo/') === 0) {
                    $stmt = $conexion->prepare("UPDATE catalogo_productos SET imagen_url = ? WHERE id = ?");
                    $stmt->execute([$imagenUrl, $productoId]);
                }
            } else {
                $stmt = $conexion->prepare("INSERT INTO catalogo_productos (sku, nombre, descripcion, imagen_url, activo) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$sku, $nombre, $descripcion, $imagenUrl]);
                $productoId = (int) $conexion->lastInsertId();
            }

            $stmt = $conexion->prepare("INSERT INTO catalogo_ofertas (producto_id, proveedor_id, precio, stock, activo)
                                        SELECT ?, id, ?, ?, 1 FROM catalogo_proveedores WHERE id = ? AND activo = 1");
            $stmt->execute([$productoId, $precio, $stock, $proveedorId]);
            if ($stmt->rowCount() !== 1) {
                throw new RuntimeException('El proveedor seleccionado no existe o ya hay una oferta para este producto.');
            }
            $conexion->commit();
        } catch (PDOException $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new RuntimeException('El SKU o la combinación de producto y proveedor ya existe.');
            }
            throw $e;
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    public function actualizarPrecio($ofertaId, $precio) {
        $conexion = $this->conectar();
        $stmt = $conexion->prepare("UPDATE catalogo_ofertas SET precio = ? WHERE id = ?");
        return $stmt->execute([$precio, $ofertaId]);
    }

    public function reponerStock($ofertaId, $cantidad) {
        $conexion = $this->conectar();
        $conexion->beginTransaction();
        try {
            $stmt = $conexion->prepare("SELECT id FROM catalogo_ofertas WHERE id = ? FOR UPDATE");
            $stmt->execute([$ofertaId]);
            if (!$stmt->fetchColumn()) {
                throw new RuntimeException('No se encontró la oferta seleccionada.');
            }
            $stmt = $conexion->prepare("UPDATE catalogo_ofertas SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$cantidad, $ofertaId]);
            $stmt = $conexion->prepare("INSERT INTO inventario_movimientos (oferta_id, tipo, cantidad, fecha, detalle)
                                        VALUES (?, 'reposicion', ?, NOW(), 'Reposición registrada por Supplier/Admin')");
            $stmt->execute([$ofertaId, $cantidad]);

            $pendientes = $conexion->prepare("SELECT pi.id, pi.pedido_id, pi.cantidad_backorder
                                              FROM pedido_items pi
                                              JOIN pedidos p ON p.id = pi.pedido_id
                                              WHERE pi.oferta_id = ? AND pi.cantidad_backorder > 0
                                                AND p.estado = 'Pendiente de abastecimiento'
                                              ORDER BY p.fecha, p.id, pi.id FOR UPDATE");
            $pendientes->execute([$ofertaId]);
            $lineas = $pendientes->fetchAll(PDO::FETCH_ASSOC);
            $stockDisponible = $cantidad;
            foreach ($lineas as $linea) {
                if ($stockDisponible <= 0) {
                    break;
                }
                $asignar = min($stockDisponible, (int) $linea['cantidad_backorder']);
                $stmt = $conexion->prepare("UPDATE pedido_items
                                            SET cantidad_asignada = cantidad_asignada + ?, cantidad_backorder = cantidad_backorder - ?
                                            WHERE id = ?");
                $stmt->execute([$asignar, $asignar, $linea['id']]);
                $stmt = $conexion->prepare("INSERT INTO inventario_movimientos (oferta_id, pedido_item_id, tipo, cantidad, fecha, detalle)
                                            VALUES (?, ?, 'asignacion_backorder', ?, NOW(), ?)");
                $stmt->execute([$ofertaId, $linea['id'], $asignar, 'Asignación FIFO a pedido #' . $linea['pedido_id']]);
                $stockDisponible -= $asignar;

                $stmt = $conexion->prepare("UPDATE pedidos p
                                            SET p.estado = 'Procesando'
                                            WHERE p.id = ?
                                              AND p.estado = 'Pendiente de abastecimiento'
                                              AND NOT EXISTS (
                                                  SELECT 1 FROM pedido_items pi
                                                  WHERE pi.pedido_id = p.id AND pi.cantidad_backorder > 0
                                              )");
                $stmt->execute([$linea['pedido_id']]);
            }
            $stmt = $conexion->prepare("UPDATE catalogo_ofertas SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$cantidad - $stockDisponible, $ofertaId]);
            $conexion->commit();
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }

    public function actualizarEstadoPedido($pedidoId, $estado, $customerId = null) {
        $conexion = $this->conectar();
        $conexion->beginTransaction();
        try {
            $stmt = $conexion->prepare("SELECT estado, customer_id FROM pedidos WHERE id = ? FOR UPDATE");
            $stmt->execute([$pedidoId]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$pedido) {
                throw new RuntimeException('No se encontró el pedido.');
            }
            $estadoActual = $pedido['estado'];
            if ($customerId !== null) {
                if ((int) $pedido['customer_id'] !== (int) $customerId) {
                    throw new RuntimeException('No tienes permiso para cancelar este pedido.');
                }
            }

            if ($estado === 'Cancelado') {
                if (in_array($estadoActual, ['Enviado', 'Entregado', 'Cancelado'], true)) {
                    throw new RuntimeException('Este pedido ya no se puede cancelar.');
                }
                if ($customerId !== null && !in_array($estadoActual, ['Procesando', 'Pendiente de abastecimiento'], true)) {
                    throw new RuntimeException('Como customer, solo puedes cancelar pedidos antes de que sean confirmados.');
                }
                $stmt = $conexion->prepare("SELECT id, oferta_id, cantidad_asignada FROM pedido_items WHERE pedido_id = ? FOR UPDATE");
                $stmt->execute([$pedidoId]);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $linea) {
                    if ((int) $linea['cantidad_asignada'] > 0) {
                        $stmtStock = $conexion->prepare("UPDATE catalogo_ofertas SET stock = stock + ? WHERE id = ?");
                        $stmtStock->execute([$linea['cantidad_asignada'], $linea['oferta_id']]);
                        $stmtMovimiento = $conexion->prepare("INSERT INTO inventario_movimientos (oferta_id, pedido_item_id, tipo, cantidad, fecha, detalle)
                                                              VALUES (?, ?, 'cancelacion', ?, NOW(), ?)");
                        $stmtMovimiento->execute([
                            $linea['oferta_id'], $linea['id'], $linea['cantidad_asignada'],
                            'Devolución de unidades del pedido #' . $pedidoId
                        ]);
                    }
                }
                $stmt = $conexion->prepare("UPDATE pedido_items SET cantidad_backorder = 0 WHERE pedido_id = ?");
                $stmt->execute([$pedidoId]);
            } elseif ($estado === 'Confirmado') {
                if ($estadoActual !== 'Procesando') {
                    throw new RuntimeException('Solo se puede confirmar un pedido completo en procesamiento.');
                }
            } elseif ($estado === 'Enviado') {
                if ($estadoActual !== 'Confirmado') {
                    throw new RuntimeException('Confirma el pedido antes de marcarlo como enviado.');
                }
            } elseif ($estado === 'Entregado') {
                if ($estadoActual !== 'Enviado') {
                    throw new RuntimeException('El pedido debe estar enviado antes de marcarlo como entregado.');
                }
            } else {
                throw new RuntimeException('Estado de pedido no válido.');
            }

            $stmt = $conexion->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
            $stmt->execute([$estado, $pedidoId]);
            $conexion->commit();
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $e;
        }
    }
}
