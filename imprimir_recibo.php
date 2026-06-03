<?php
/**
 * imprimir_recibo.php
 * Genera e imprime un Recibo o Factura de Servicio en formato de hoja tamaño carta.
 * GET ?ot_num=OT-XXXX&tipo=recibo|factura
 */
require_once __DIR__ . '/php/auth.php';
requerir_autenticacion();
require_once __DIR__ . '/php/databaseS.php';

$ot_num = trim($_GET['ot_num'] ?? '');
$tipo   = trim($_GET['tipo'] ?? 'recibo'); // 'recibo' o 'factura'

if ($ot_num === '') {
    die('Error: El número de orden de trabajo (ot_num) es requerido.');
}

// Consultar la orden de trabajo
$stmt = mysqli_prepare($db,
    "SELECT o.*, 
            c.Nombre AS ClienteNombre, c.Telefono AS ClienteTelefono, c.Email AS ClienteEmail,
            v.Marca, v.Modelo, v.Anio, v.Placa, v.Vin
     FROM OT o
     INNER JOIN Clientes c ON o.Clave_Cliente = c.Clave_Cliente
     INNER JOIN Vehiculos v ON o.Clave_Vehiculo = v.Clave_Vehiculo
     WHERE o.OT_Num = ?
     LIMIT 1"
);

if (!$stmt) {
    die('Error al preparar la consulta de base de datos.');
}

mysqli_stmt_bind_param($stmt, 's', $ot_num);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$ot = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$ot) {
    die('Error: La orden de trabajo especificada no existe.');
}

// Parsear descripción de trabajo por saltos de línea para la tabla de servicios
$lines = explode("\n", $ot['Trabajo_Realizado']);
$items = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;
    // Si contiene dos puntos, separar categoría y servicio
    if (strpos($line, ':') !== false) {
        list($cat, $srv) = explode(':', $line, 2);
        $items[] = [
            'categoria' => trim($cat),
            'servicio'  => trim($srv)
        ];
    } else {
        $items[] = [
            'categoria' => 'Servicio',
            'servicio'  => $line
        ];
    }
}
if (empty($items)) {
    $items[] = [
        'categoria' => 'General',
        'servicio'  => $ot['Trabajo_Realizado'] ?: 'Servicio de mantenimiento automotriz.'
    ];
}

// Configurar datos para la factura o recibo
$total_cobrado = (float)$ot['Total_Cobrado'];

// Generar objeto JSON para inyectar en la página
$datos_recibo = [
    'taller' => [
        'nombre'      => "Taller Mecánico Salvatori",
        'logo'        => "", // URL o base64
        'direccion'   => "Francisco I. Madero 453 Nte., Col. Centro, Gómez Palacio, Durango. CP 35000",
        'telefono'    => "(871) 7230430",
        'whatsapp'    => "(871) 1562351",
        'rfc'         => "SAMH651027UN4",
        'clabe'       => "002060015676484313",
        'banco'       => "Banamex",
        'propietario' => "Hernán Salvatori Morales",
        'garantia'    => "1 año o 10,000 km, lo que ocurra primero.\nTodas las refacciones y procedimientos son de máxima calidad.",
    ],
    'servicio' => [
        'folio'            => $ot['OT_Num'],
        'fecha'            => date('d/m/Y', strtotime($ot['Fecha'])),
        'cliente'          => $ot['ClienteNombre'],
        'vehiculo'         => trim($ot['Marca'] . ' ' . $ot['Modelo']),
        'placa'            => $ot['Placa'] ?: ($ot['Vin'] ?: 'Sin placa/VIN'),
        'trabajoRealizado' => $ot['Trabajo_Realizado'],
        'montoCobrado'     => $total_cobrado,
        'fechaTerminacion' => date('d/m/Y', strtotime($ot['Fecha'])),
    ],
    'itemsServicio' => $items
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $tipo === 'factura' ? 'Factura' : 'Recibo'; ?> de Servicio - <?php echo htmlspecialchars($ot['OT_Num']); ?></title>

  <script>
    const TIPO_DOC = <?php echo json_encode($tipo); ?>;
    const DATOS_RECIBO = <?php echo json_encode($datos_recibo, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  </script>

  <style>
    /* ================================================================
       FUENTES & RESET
       ================================================================ */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
      font-size: 11pt;
      color: #1a1a2e;
      background: #e8eaf6;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      min-height: 100vh;
      padding: 24px 16px 40px;
    }

    /* ================================================================
       HOJA TAMAÑO CARTA
       ================================================================ */
    #recibo {
      width: 215.9mm;          /* 8.5 in */
      min-height: 279.4mm;     /* 11 in  */
      background: #ffffff;
      padding: 14mm 16mm 12mm;
      display: flex;
      flex-direction: column;
      box-shadow: 0 8px 40px rgba(0,0,0,.18);
      border-radius: 4px;
      position: relative;
    }

    /* ================================================================
       ENCABEZADO
       ================================================================ */
    #encabezado {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 3px solid #1a237e;
      padding-bottom: 10px;
      margin-bottom: 6px;
    }

    #logo-area {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 60px;
    }

    #logo-placeholder {
      width: 64px;
      height: 64px;
      border-radius: 6px;
      background: linear-gradient(135deg, #1a237e, #283593);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 22px;
      font-weight: 800;
      letter-spacing: -1px;
      border: 2px solid #9fa8da;
      flex-shrink: 0;
    }

    #titulo-area {
      text-align: center;
      flex: 1;
    }

    #titulo-area h1 {
      font-size: 20pt;
      font-weight: 800;
      color: #1a237e;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    #titulo-area p {
      font-size: 8.5pt;
      color: #5c6bc0;
      margin-top: 2px;
    }

    #folio-area {
      text-align: right;
      min-width: 100px;
    }

    #folio-area .folio-label {
      font-size: 7.5pt;
      color: #9e9e9e;
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    #folio-area .folio-val {
      font-size: 11pt;
      font-weight: 700;
      color: #1a237e;
    }

    /* ================================================================
       FRANJA DECORATIVA
       ================================================================ */
    .stripe {
      height: 4px;
      background: linear-gradient(90deg, #1a237e 0%, #3949ab 50%, #7986cb 100%);
      margin-bottom: 14px;
      border-radius: 2px;
    }

    /* ================================================================
       SECCIÓN DE INFORMACIÓN
       ================================================================ */
    #info-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0 24px;
      margin-bottom: 18px;
      border: 1.5px solid #c5cae9;
      border-radius: 6px;
      overflow: hidden;
    }

    .info-row {
      display: flex;
      align-items: baseline;
      padding: 5px 10px;
      border-bottom: 1px solid #e8eaf6;
      gap: 6px;
    }

    .info-row:nth-child(odd)  { background: #f5f5ff; }
    .info-row:nth-child(even) { background: #ffffff;  }

    .info-label {
      font-size: 8pt;
      font-weight: 700;
      color: #3949ab;
      text-align: right;
      white-space: nowrap;
      min-width: 110px;
      text-transform: uppercase;
      letter-spacing: .04em;
      flex-shrink: 0;
    }

    .info-label::after { content: ":"; }

    .info-val {
      font-size: 9.5pt;
      color: #1a1a2e;
      font-weight: 500;
      word-break: break-word;
    }

    .info-val.monto {
      font-size: 12pt;
      font-weight: 800;
      color: #1b5e20;
    }

    /* Fila que ocupa todo el ancho (grid span) */
    .info-row.full {
      grid-column: 1 / -1;
    }

    /* ================================================================
       TABLA DE SERVICIOS
       ================================================================ */
    #servicios-section {
      margin-bottom: 18px;
    }

    #servicios-section h2 {
      font-size: 9pt;
      font-weight: 800;
      color: #1a237e;
      text-transform: uppercase;
      letter-spacing: .07em;
      margin-bottom: 6px;
      border-left: 4px solid #3949ab;
      padding-left: 8px;
    }

    #tabla-servicios {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.5pt;
    }

    #tabla-servicios thead th {
      padding: 7px 10px;
      text-align: left;
      font-weight: 700;
      font-size: 8.5pt;
      text-transform: uppercase;
      letter-spacing: .05em;
      border: 1px solid #283593;
      background: linear-gradient(90deg, #1a237e, #3949ab);
      color: #ffffff;
    }

    #tabla-servicios tbody tr:nth-child(odd)  { background: #f5f5ff; }
    #tabla-servicios tbody tr:nth-child(even) { background: #ffffff;  }
    #tabla-servicios td {
      padding: 6px 10px;
      border: 1px solid #c5cae9;
      vertical-align: middle;
    }

    #tabla-servicios td:first-child {
      font-weight: 600;
      color: #3949ab;
      width: 34%;
    }

    .fila-vacia td {
      height: 22px;
      background: #fafafa !important;
      border-color: #e0e0e0 !important;
    }

    /* ================================================================
       TOTALES
       ================================================================ */
    #totales-section {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 20px;
    }

    #totales-box {
      border: 2px solid #1a237e;
      border-radius: 6px;
      overflow: hidden;
      min-width: 240px;
    }

    #totales-box .tot-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 6px 14px;
      gap: 24px;
      font-size: 9pt;
      border-bottom: 1px solid #e8eaf6;
    }

    #totales-box .tot-row:last-child {
      border-bottom: none;
    }

    #totales-box .tot-row.header {
      background: linear-gradient(90deg, #1a237e, #3949ab);
      color: #fff;
      font-weight: 700;
      font-size: 8.5pt;
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    #totales-box .tot-row.total {
      background: #e8f5e9;
      border-top: 2px solid #1a237e;
    }

    .tot-label { font-weight: 600; color: #1a237e; }
    .tot-val   { font-weight: 800; font-size: 13pt; color: #1b5e20; }
    .tot-sub-val { font-weight: 600; color: #37474f; }

    /* ================================================================
       FIRMA
       ================================================================ */
    #firma-section {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-bottom: 18px;
      gap: 24px;
    }

    .firma-bloque {
      flex: 1;
      text-align: center;
    }

    .firma-linea {
      border-top: 1.5px solid #546e7a;
      margin: 0 auto 4px;
      width: 85%;
    }

    .firma-nombre {
      font-size: 8.5pt;
      font-weight: 600;
      color: #37474f;
    }

    .firma-cargo {
      font-size: 7.5pt;
      color: #90a4ae;
    }

    /* ================================================================
       PIE DE PÁGINA
       ================================================================ */
    #footer {
      margin-top: auto;
      border-top: 3px solid #1a237e;
      padding-top: 8px;
      text-align: center;
    }

    #footer .garantia {
      font-size: 8pt;
      font-style: italic;
      color: #3949ab;
      font-weight: 600;
      margin-bottom: 4px;
      white-space: pre-line;
    }

    #footer .linea-sep {
      border: none;
      border-top: 1px dashed #c5cae9;
      margin: 5px 0;
    }

    #footer .contacto {
      font-size: 8pt;
      color: #37474f;
      margin-bottom: 3px;
    }

    #footer .fiscal {
      font-size: 7.5pt;
      color: #546e7a;
      letter-spacing: .02em;
      margin-bottom: 2px;
    }

    #footer .propietario {
      font-size: 8.5pt;
      font-weight: 700;
      color: #1a237e;
      margin-top: 3px;
    }

    /* ================================================================
       BOTÓN DE IMPRESIÓN (solo pantalla)
       ================================================================ */
    #btn-imprimir {
      margin-top: 20px;
      padding: 12px 36px;
      background: linear-gradient(135deg, #1a237e, #3949ab);
      color: #fff;
      font-family: inherit;
      font-size: 13pt;
      font-weight: 700;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 0 4px 18px rgba(26,35,126,.35);
      letter-spacing: .04em;
      transition: transform .15s, box-shadow .15s;
    }

    #btn-imprimir:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 28px rgba(26,35,126,.45);
    }

    /* ================================================================
       ESTILOS DE IMPRESIÓN  @media print
       ================================================================ */
    @media print {
      @page {
        size: letter portrait;
        margin: 0;
      }

      body {
        background: none;
        padding: 0;
        margin: 0;
        display: block;
      }

      #recibo {
        width: 100%;
        min-height: 100vh;
        margin: 0;
        padding: 14mm 16mm 12mm;
        box-shadow: none;
        border-radius: 0;
        page-break-after: avoid;
      }

      #btn-imprimir { display: none !important; }
      #tabla-servicios tbody tr { page-break-inside: avoid; }

      #footer {
        position: fixed;
        bottom: 12mm;
        left: 16mm;
        right: 16mm;
      }

      #espaciador-footer { display: block; height: 60mm; }
    }

    @media screen {
      #espaciador-footer { display: none; }
    }
  </style>
</head>
<body>

  <div id="recibo">

    <!-- ── ENCABEZADO ─────────────────────────────────────────────── -->
    <header id="encabezado">
      <div id="logo-area">
        <div id="logo-placeholder"></div>
      </div>

      <div id="titulo-area">
        <h1 id="titulo-doc">Recibo de Servicio</h1>
        <p id="nombre-taller"></p>
      </div>

      <div id="folio-area">
        <div class="folio-label">Folio</div>
        <div class="folio-val" id="folio-val"></div>
      </div>
    </header>

    <div class="stripe"></div>

    <!-- ── INFORMACIÓN DEL SERVICIO ───────────────────────────────── -->
    <section id="info-section">
      <div class="info-row">
        <span class="info-label">Fecha</span>
        <span class="info-val" id="inf-fecha"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Cliente</span>
        <span class="info-val" id="inf-cliente"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Vehículo</span>
        <span class="info-val" id="inf-vehiculo"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Placa</span>
        <span class="info-val" id="inf-placa"></span>
      </div>
      <div class="info-row full">
        <span class="info-label">Trabajo Realizado</span>
        <span class="info-val" id="inf-trabajo"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Monto Cobrado</span>
        <span class="info-val monto" id="inf-monto"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Fecha de Terminación</span>
        <span class="info-val" id="inf-terminacion"></span>
      </div>
    </section>

    <!-- ── TABLA DE SERVICIOS ──────────────────────────────────────── -->
    <section id="servicios-section">
      <h2>Detalle de Servicios Realizados</h2>
      <table id="tabla-servicios">
        <thead>
          <tr>
            <th>Categoría</th>
            <th>Servicio / Descripción</th>
          </tr>
        </thead>
        <tbody id="tbody-servicios"></tbody>
      </table>
    </section>

    <!-- ── TOTALES ─────────────────────────────────────────────────── -->
    <div id="totales-section">
      <div id="totales-box">
        <div class="tot-row header">
          <span id="tot-header-text">Resumen de Pago</span>
        </div>
        <div class="tot-row" id="row-subtotal" style="display:none;">
          <span class="tot-label">Subtotal</span>
          <span class="tot-sub-val" id="tot-subtotal"></span>
        </div>
        <div class="tot-row" id="row-iva" style="display:none;">
          <span class="tot-label">IVA (16%)</span>
          <span class="tot-sub-val" id="tot-iva"></span>
        </div>
        <div class="tot-row total">
          <span class="tot-label">Total Cobrado</span>
          <span class="tot-val" id="tot-monto"></span>
        </div>
      </div>
    </div>

    <!-- ── FIRMAS ──────────────────────────────────────────────────── -->
    <div id="firma-section">
      <div class="firma-bloque">
        <div class="firma-linea"></div>
        <div class="firma-nombre" id="firma-cliente-nombre"></div>
        <div class="firma-cargo">Firma del Cliente</div>
      </div>
      <div class="firma-bloque">
        <div class="firma-linea"></div>
        <div class="firma-nombre" id="firma-propietario-nombre"></div>
        <div class="firma-cargo">Propietario / Responsable</div>
      </div>
    </div>

    <div id="espaciador-footer"></div>

    <!-- ── PIE DE PÁGINA ───────────────────────────────────────────── -->
    <footer id="footer">
      <div class="garantia" id="footer-garantia"></div>
      <hr class="linea-sep">
      <div class="contacto" id="footer-contacto"></div>
      <div class="fiscal" id="footer-fiscal"></div>
      <div class="propietario" id="footer-propietario"></div>
    </footer>

  </div>

  <button id="btn-imprimir" onclick="window.print()">
    🖨️ Imprimir / Guardar PDF
  </button>

  <script>
    (function () {
      const d = DATOS_RECIBO;
      const t = d.taller;
      const s = d.servicio;

      const $  = id => document.getElementById(id);
      const fmt = n => n.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });

      // Cargar info del taller y folios
      $('nombre-taller').textContent = t.nombre;
      $('folio-val').textContent     = s.folio;

      // Iniciales o Logo
      if (t.logo) {
        const placeholder = $('logo-placeholder');
        const img = document.createElement('img');
        img.src = t.logo;
        img.alt = 'Logo';
        img.style.cssText = 'width:64px;height:64px;object-fit:contain;border:2px solid #c5cae9;border-radius:6px;padding:3px;';
        placeholder.replaceWith(img);
      } else {
        const iniciales = t.nombre.split(' ')
          .filter(w => /^[A-ZÁÉÍÓÚÑ]/i.test(w))
          .slice(0, 2)
          .map(w => w[0].toUpperCase())
          .join('');
        $('logo-placeholder').textContent = iniciales;
      }

      // Desglose de montos si es factura o recibo
      const baseMonto = s.montoCobrado;
      if (TIPO_DOC === 'factura') {
        $('titulo-doc').textContent = 'Factura de Servicio';
        $('tot-header-text').textContent = 'Resumen de Facturación';
        
        const subtotal = baseMonto;
        const iva = subtotal * 0.16;
        const total = subtotal + iva;

        $('row-subtotal').style.display = 'flex';
        $('row-iva').style.display = 'flex';

        $('tot-subtotal').textContent = fmt(subtotal);
        $('tot-iva').textContent = fmt(iva);
        
        $('inf-monto').textContent = fmt(total);
        $('tot-monto').textContent = fmt(total);
      } else {
        $('titulo-doc').textContent = 'Recibo de Servicio';
        $('tot-header-text').textContent = 'Resumen de Pago';
        
        $('inf-monto').textContent = fmt(baseMonto);
        $('tot-monto').textContent = fmt(baseMonto);
      }

      // Datos de servicio
      $('inf-fecha').textContent       = s.fecha;
      $('inf-cliente').textContent     = s.cliente;
      $('inf-vehiculo').textContent    = s.vehiculo;
      $('inf-placa').textContent       = s.placa;
      $('inf-trabajo').textContent     = s.trabajoRealizado;
      $('inf-terminacion').textContent = s.fechaTerminacion;

      // Renderizar tabla
      const tbody = $('tbody-servicios');
      const FILAS_MINIMAS = 6;

      d.itemsServicio.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${escapeHtml(item.categoria)}</td><td>${escapeHtml(item.servicio)}</td>`;
        tbody.appendChild(tr);
      });

      // Rellenar vacíos
      const filasRestantes = Math.max(0, FILAS_MINIMAS - d.itemsServicio.length);
      for (let i = 0; i < filasRestantes; i++) {
        const tr = document.createElement('tr');
        tr.className = 'fila-vacia';
        tr.innerHTML = '<td>&nbsp;</td><td>&nbsp;</td>';
        tbody.appendChild(tr);
      }

      // Firmas
      $('firma-cliente-nombre').textContent    = s.cliente;
      $('firma-propietario-nombre').textContent = t.propietario;

      // Footer
      $('footer-garantia').textContent = '⚑ Garantía: ' + t.garantia;
      $('footer-contacto').innerHTML =
        `<strong>Dirección:</strong> ${t.direccion} &nbsp;|&nbsp; ` +
        `<strong>Tel:</strong> ${t.telefono} &nbsp;|&nbsp; ` +
        `<strong>WhatsApp:</strong> ${t.whatsapp}`;

      $('footer-fiscal').innerHTML =
        `<strong>RFC:</strong> ${t.rfc} &nbsp;&nbsp;` +
        `<strong>CLABE:</strong> ${t.clabe} &nbsp;&nbsp;` +
        `<strong>Banco:</strong> ${t.banco}`;

      $('footer-propietario').textContent = t.propietario;

      // Escape HTML helper
      function escapeHtml(str) {
        if (!str) return '';
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }

      // Auto imprimir
      window.onload = function() {
        setTimeout(function() {
          window.print();
        }, 300);
      };

    })();
  </script>

</body>
</html>
