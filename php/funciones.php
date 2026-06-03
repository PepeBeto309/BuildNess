<?php

function inventario_asegurar_sistema($db) {
    $sql1 = "INSERT IGNORE INTO Clientes (Clave_Cliente, Nombre, Activo) VALUES ('CLI-SYSTEM', 'SISTEMA', 1);";
    mysqli_query($db, $sql1);

    $sql2 = "INSERT IGNORE INTO Vehiculos (Clave_Vehiculo, Clave_Cliente, Activo) VALUES ('VEH-SYSTEM', 'CLI-SYSTEM', 1);";
    mysqli_query($db, $sql2);

    $sql3 = "INSERT IGNORE INTO OT (OT_Num, Fecha, Clave_Cliente, Clave_Vehiculo, Estado) VALUES ('OT-SYSTEM', CURDATE(), 'CLI-SYSTEM', 'VEH-SYSTEM', 'SISTEMA');";
    mysqli_query($db, $sql3);

    $sql4 = "INSERT IGNORE INTO OT (OT_Num, Fecha, Clave_Cliente, Clave_Vehiculo, Estado) VALUES ('OT-BAJA', CURDATE(), 'CLI-SYSTEM', 'VEH-SYSTEM', 'BAJA');";
    mysqli_query($db, $sql4);
}

function obtener_clientes() {
    try {
        require 'databaseS.php';
        $sql = "SELECT Clave_Cliente, Nombre, Telefono, Email FROM Clientes WHERE Clave_Cliente != 'CLI-SYSTEM' ORDER BY Clave_Cliente DESC;";
        return mysqli_query($db, $sql);
    } catch (\Throwable $th) {
        var_dump($th);
    }
}

function obtener_vehiculos() {
    try {
        require 'databaseS.php';
        $sql = "SELECT v.Clave_Vehiculo, v.Marca, v.Modelo, v.Anio AS `Año`, v.Placa, v.Vin, v.Clave_Cliente, c.Nombre AS dueno
                FROM Vehiculos v
                INNER JOIN Clientes c ON v.Clave_Cliente = c.Clave_Cliente
                WHERE v.Clave_Vehiculo != 'VEH-SYSTEM'
                ORDER BY v.Clave_Vehiculo DESC;";
        return mysqli_query($db, $sql);
    } catch (\Throwable $th) {
        var_dump($th);
    }
}

function borrar_clientes($clave) {
    try {
        require 'databaseS.php';
        $clave = trim((string)$clave);
        if ($clave === '') return false;

        $stmtV = mysqli_prepare($db, "DELETE FROM Vehiculos WHERE Clave_Cliente = ?");
        if ($stmtV) {
            mysqli_stmt_bind_param($stmtV, 's', $clave);
            mysqli_stmt_execute($stmtV);
            mysqli_stmt_close($stmtV);
        }

        $stmt = mysqli_prepare($db, "DELETE FROM Clientes WHERE Clave_Cliente = ?");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 's', $clave);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $resultado;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function borrar_vehiculos($clave) {
    try {
        require 'databaseS.php';
        $clave = trim((string)$clave);
        if ($clave === '') return false;

        $stmt = mysqli_prepare($db, "DELETE FROM Vehiculos WHERE Clave_Vehiculo = ?");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 's', $clave);
        $resultado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $resultado;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function obtener_inventario_refacciones() {
    try {
        require 'databaseS.php';
        inventario_asegurar_sistema($db);
        $sql = "SELECT Codigo as codigo, Pieza as nombre, Cantidad as cantidad, Tipo as unidad, Fecha_Salida as creado_en 
                FROM Inventario WHERE OT_Num = 'OT-SYSTEM' ORDER BY Fecha_Salida DESC, Codigo DESC;";
        return mysqli_query($db, $sql);
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function obtener_inventario_refaccion_por_id($codigo) {
    try {
        require 'databaseS.php';
        inventario_asegurar_sistema($db);
        $codigo = trim((string)$codigo);
        if ($codigo === '') return null;
        
        $stmt = mysqli_prepare($db, "SELECT Codigo as codigo, Pieza as nombre, Cantidad as cantidad, Tipo as unidad FROM Inventario WHERE Codigo = ? AND OT_Num = 'OT-SYSTEM'");
        if (!$stmt) return null;
        mysqli_stmt_bind_param($stmt, 's', $codigo);
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
        inventario_asegurar_sistema($db);
        
        $codigo = trim((string)$codigo) !== '' ? trim((string)$codigo) : 'REF-' . strtoupper(substr(uniqid(), -10));
        $nombre = trim((string)$nombre);
        $unidad = trim((string)$unidad) !== '' ? trim($unidad) : 'pz';
        $cantidad = (int)$cantidad;
        if ($nombre === '' || $cantidad < 0) return false;
        
        $ot_num = 'OT-SYSTEM';
        $fecha = date('Y-m-d');
        
        $stmt = mysqli_prepare($db, "INSERT INTO Inventario (Codigo, Fecha_Salida, OT_Num, Pieza, Cantidad, Precio_Unitario, Tipo, Total) VALUES (?, ?, ?, ?, ?, 0, ?, 0)");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 'ssssis', $codigo, $fecha, $ot_num, $nombre, $cantidad, $unidad);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function actualizar_inventario_refaccion($codigo_original, $codigo_nuevo, $nombre, $cantidad, $unidad) {
    try {
        require 'databaseS.php';
        inventario_asegurar_sistema($db);
        
        $codigo_original = trim((string)$codigo_original);
        $codigo_nuevo = trim((string)$codigo_nuevo) !== '' ? trim((string)$codigo_nuevo) : $codigo_original;
        $nombre = trim((string)$nombre);
        $unidad = trim((string)$unidad) !== '' ? trim($unidad) : 'pz';
        $cantidad = (int)$cantidad;

        if ($codigo_original === '' || $nombre === '' || $cantidad < 0) return false;

        $stmt = mysqli_prepare($db, "UPDATE Inventario SET Codigo = ?, Pieza = ?, Cantidad = ?, Tipo = ? WHERE Codigo = ? AND OT_Num = 'OT-SYSTEM'");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 'ssiss', $codigo_nuevo, $nombre, $cantidad, $unidad, $codigo_original);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function borrar_inventario_refaccion($codigo, $ot_num = 'OT-BAJA') {
    try {
        require 'databaseS.php';
        inventario_asegurar_sistema($db);
        $codigo = trim((string)$codigo);
        $ot_num = trim((string)$ot_num) !== '' ? trim((string)$ot_num) : 'OT-BAJA';
        if ($codigo === '') return false;

        $fecha = date('Y-m-d');
        $stmt = mysqli_prepare($db, "UPDATE Inventario SET OT_Num = ?, Fecha_Salida = ? WHERE Codigo = ? AND OT_Num = 'OT-SYSTEM'");
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, 'sss', $ot_num, $fecha, $codigo);
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
        inventario_asegurar_sistema($db);
        
        if ($tipo === 'entrada') {
            $sql = "SELECT Codigo as codigo, Pieza as nombre, Cantidad as cantidad, Tipo as unidad, 'Alta de stock' as nota, Fecha_Salida as registrado_en 
                    FROM Inventario WHERE OT_Num = 'OT-SYSTEM' ORDER BY Fecha_Salida DESC, Codigo DESC";
            return mysqli_query($db, $sql);
        } elseif ($tipo === 'salida') {
            $sql = "SELECT Codigo as codigo, Pieza as nombre, Cantidad as cantidad, Tipo as unidad, 
                           IF(OT_Num = 'OT-BAJA', 'Baja de stock', CONCAT('Uso en ', OT_Num)) as nota, 
                           Fecha_Salida as registrado_en 
                    FROM Inventario WHERE OT_Num != 'OT-SYSTEM' ORDER BY Fecha_Salida DESC, Codigo DESC";
            return mysqli_query($db, $sql);
        }
        
        $sql = "SELECT Codigo as codigo, Pieza as nombre, Cantidad as cantidad, Tipo as unidad, 
                       IF(OT_Num = 'OT-SYSTEM', 'Alta de stock', IF(OT_Num = 'OT-BAJA', 'Baja de stock', CONCAT('Uso en ', OT_Num))) as nota, 
                       Fecha_Salida as registrado_en 
                FROM Inventario ORDER BY Fecha_Salida DESC, Codigo DESC";
        return mysqli_query($db, $sql);
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}

function obtener_refacciones_disponibles() {
    try {
        require 'databaseS.php';
        $sql = "SELECT Codigo as codigo, Pieza as nombre, Cantidad as cantidad, Tipo as unidad 
                FROM Inventario 
                WHERE OT_Num = 'OT-SYSTEM' AND Cantidad > 0 
                ORDER BY Pieza ASC";
        return mysqli_query($db, $sql);
    } catch (\Throwable $th) {
        var_dump($th);
        return false;
    }
}
?>