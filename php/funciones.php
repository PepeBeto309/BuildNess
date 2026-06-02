<?php

//$id = $_POST['id'];
function obtener_clientes() {
    try {
        require 'databaseS.php';


        $sql = "SELECT * FROM clientes;";

        $consulta = mysqli_query($db, $sql);

        return $consulta;
        
    }catch (\Throwable $th){
        var_dump($th);
    }
}

function obtener_vehiculos() {
    try {
        require 'databaseS.php';

        $sql = "SELECT v.id, v.marca, v.modelo, v.anio, v.placa, v.vin, v.cliente_id,
                TRIM(CONCAT(
                    c.nombres, ' ',
                    c.apellido_paterno, ' ',
                    IFNULL(c.apellido_materno, '')
                )) AS dueno
                FROM vehiculos v
                INNER JOIN clientes c ON v.cliente_id = c.id
                ORDER BY v.id DESC;";

        $consulta = mysqli_query($db, $sql);

        return $consulta;
        
    }catch (\Throwable $th){
        var_dump($th);
    }
}

function borrar_clientes($id) {
    try {
        require 'databaseS.php';

        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        $stmtV = mysqli_prepare($db, "DELETE FROM vehiculos WHERE cliente_id = ?");
        if (!$stmtV) {
            return false;
        }
        mysqli_stmt_bind_param($stmtV, 'i', $id);
        mysqli_stmt_execute($stmtV);
        mysqli_stmt_close($stmtV);

        $stmt = mysqli_prepare($db, "DELETE FROM clientes WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $resultado;
        
    }catch (\Throwable $th){
        var_dump($th);
        return false;
    }
}

function borrar_vehiculos($id) {
    try {
        require 'databaseS.php';

        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        $stmt = mysqli_prepare($db, "DELETE FROM vehiculos WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $resultado;
        
    }catch (\Throwable $th){
        var_dump($th);
        return false;
    }
}

function inventario_asegurar_tabla() {
    require 'databaseS.php';
    $sql = "CREATE TABLE IF NOT EXISTS inventario_refacciones (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(64) NOT NULL DEFAULT '',
        nombre VARCHAR(200) NOT NULL,
        cantidad INT NOT NULL DEFAULT 0,
        unidad VARCHAR(32) NOT NULL DEFAULT 'pz',
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    mysqli_query($db, $sql);
}

function obtener_inventario_refacciones() {
    try {
        require 'databaseS.php';
        inventario_asegurar_tabla();
        $sql = "SELECT id, codigo, nombre, cantidad, unidad, creado_en FROM inventario_refacciones ORDER BY id DESC;";
        return mysqli_query($db, $sql);
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function inventario_asegurar_movimientos() {
    require 'databaseS.php';
    inventario_asegurar_tabla();
    $sql = "CREATE TABLE IF NOT EXISTS inventario_movimientos (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        refaccion_id INT UNSIGNED NULL,
        tipo ENUM('entrada','salida') NOT NULL,
        codigo VARCHAR(64) NOT NULL DEFAULT '',
        nombre VARCHAR(200) NOT NULL,
        cantidad INT NOT NULL DEFAULT 0,
        unidad VARCHAR(32) NOT NULL DEFAULT 'pz',
        nota VARCHAR(255) NOT NULL DEFAULT '',
        registrado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_tipo (tipo),
        INDEX idx_fecha (registrado_en)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    mysqli_query($db, $sql);
}

function registrar_movimiento_inventario($tipo, $refaccion_id, $codigo, $nombre, $cantidad, $unidad, $nota = '') {
    try {
        require 'databaseS.php';
        inventario_asegurar_movimientos();
        $tipo = $tipo === 'salida' ? 'salida' : 'entrada';
        $refaccion_id = $refaccion_id > 0 ? (int) $refaccion_id : null;
        $codigo = trim((string) $codigo);
        $nombre = trim((string) $nombre);
        $unidad = trim((string) $unidad) !== '' ? trim($unidad) : 'pz';
        $cantidad = max(0, (int) $cantidad);
        $nota = trim((string) $nota);

        $stmt = mysqli_prepare(
            $db,
            "INSERT INTO inventario_movimientos (refaccion_id, tipo, codigo, nombre, cantidad, unidad, nota)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'isssiss', $refaccion_id, $tipo, $codigo, $nombre, $cantidad, $unidad, $nota);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function obtener_inventario_refaccion_por_id($id) {
    try {
        require 'databaseS.php';
        inventario_asegurar_tabla();
        $id = (int) $id;
        if ($id <= 0) {
            return null;
        }
        $stmt = mysqli_prepare($db, "SELECT id, codigo, nombre, cantidad, unidad FROM inventario_refacciones WHERE id = ?");
        if (!$stmt) {
            return null;
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    } catch (\Throwable $th) {
        var_dump($th);
        return null;
    }
}

function agregar_inventario_refaccion($codigo, $nombre, $cantidad, $unidad) {
    try {
        require 'databaseS.php';
        inventario_asegurar_tabla();
        $codigo = trim((string) $codigo);
        $nombre = trim((string) $nombre);
        $unidad = trim((string) $unidad) !== '' ? trim($unidad) : 'pz';
        $cantidad = (int) $cantidad;
        if ($nombre === '' || $cantidad < 0) {
            return false;
        }
        $stmt = mysqli_prepare($db, "INSERT INTO inventario_refacciones (codigo, nombre, cantidad, unidad) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'ssis', $codigo, $nombre, $cantidad, $unidad);
        $ok = mysqli_stmt_execute($stmt);
        if ($ok) {
            $nuevoId = (int) mysqli_insert_id($db);
            registrar_movimiento_inventario(
                'entrada',
                $nuevoId,
                $codigo,
                $nombre,
                $cantidad,
                $unidad,
                'Alta de refacción en inventario'
            );
        }
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function actualizar_inventario_refaccion($id, $codigo, $nombre, $cantidad, $unidad) {
    try {
        require 'databaseS.php';
        inventario_asegurar_tabla();
        $id = (int) $id;
        $codigo = trim((string) $codigo);
        $nombre = trim((string) $nombre);
        $unidad = trim((string) $unidad) !== '' ? trim($unidad) : 'pz';
        $cantidad = (int) $cantidad;

        if ($id <= 0 || $nombre === '' || $cantidad < 0) {
            return false;
        }

        $anterior = obtener_inventario_refaccion_por_id($id);
        if (!$anterior) {
            return false;
        }

        $stmt = mysqli_prepare(
            $db,
            "UPDATE inventario_refacciones SET codigo = ?, nombre = ?, cantidad = ?, unidad = ? WHERE id = ?"
        );
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'ssisi', $codigo, $nombre, $cantidad, $unidad, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            $diff = $cantidad - (int) $anterior['cantidad'];
            if ($diff > 0) {
                registrar_movimiento_inventario(
                    'entrada',
                    $id,
                    $codigo,
                    $nombre,
                    $diff,
                    $unidad,
                    'Entrada por edición de stock'
                );
            } elseif ($diff < 0) {
                registrar_movimiento_inventario(
                    'salida',
                    $id,
                    $codigo,
                    $nombre,
                    abs($diff),
                    $unidad,
                    'Salida por edición de stock'
                );
            }
        }

        return $ok;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function borrar_inventario_refaccion($id) {
    try {
        require 'databaseS.php';
        inventario_asegurar_tabla();
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        $item = obtener_inventario_refaccion_por_id($id);
        if (!$item) {
            return false;
        }

        registrar_movimiento_inventario(
            'salida',
            $id,
            $item['codigo'],
            $item['nombre'],
            (int) $item['cantidad'],
            $item['unidad'],
            'Baja de refacción eliminada del inventario'
        );

        $stmt = mysqli_prepare($db, "DELETE FROM inventario_refacciones WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function obtener_movimientos_inventario($tipo = null) {
    try {
        require 'databaseS.php';
        inventario_asegurar_movimientos();

        if ($tipo === 'entrada' || $tipo === 'salida') {
            $stmt = mysqli_prepare(
                $db,
                "SELECT id, refaccion_id, tipo, codigo, nombre, cantidad, unidad, nota, registrado_en
                 FROM inventario_movimientos WHERE tipo = ? ORDER BY registrado_en DESC, id DESC"
            );
            if (!$stmt) {
                return false;
            }
            mysqli_stmt_bind_param($stmt, 's', $tipo);
            mysqli_stmt_execute($stmt);
            $consulta = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $consulta;
        }

        $sql = "SELECT id, refaccion_id, tipo, codigo, nombre, cantidad, unidad, nota, registrado_en
                FROM inventario_movimientos ORDER BY registrado_en DESC, id DESC";
        return mysqli_query($db, $sql);
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}
?>