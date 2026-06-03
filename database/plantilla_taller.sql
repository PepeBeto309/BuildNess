-- =============================================
-- BASE DE DATOS: TALLER MECANICO (BuildNess)
-- =============================================
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
USE salvatori;

-- Catálogo de servicios
CREATE TABLE Catalogo_Servicios (
    ID          INT             NOT NULL AUTO_INCREMENT,
    Categoria   VARCHAR(100)    NOT NULL,
    Servicio    VARCHAR(255)    NOT NULL,
    PRIMARY KEY (ID)
);

-- Mecánicos
CREATE TABLE Mecanicos (
    MecanicoID      VARCHAR(20)     NOT NULL,
    Nombre          VARCHAR(150)    NOT NULL,
    SueldoMensual   DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    FechaAlta       DATE            NOT NULL,
    FechaBaja       DATE            NULL,
    PRIMARY KEY (MecanicoID)
);

-- Clientes
CREATE TABLE Clientes (
    Clave_Cliente   VARCHAR(20)     NOT NULL,
    Nombre          VARCHAR(150)    NOT NULL,
    Telefono        VARCHAR(20)     NULL,
    Email           VARCHAR(150)    NULL,
    Conductor       VARCHAR(150)    NULL,
    Activo          BOOLEAN         NOT NULL DEFAULT TRUE,
    PRIMARY KEY (Clave_Cliente)
);

-- Vehículos
CREATE TABLE Vehiculos (
    Clave_Vehiculo          VARCHAR(20)     NOT NULL,
    Clave_Cliente           VARCHAR(20)     NOT NULL,
    Marca                   VARCHAR(80)     NULL,
    Modelo                  VARCHAR(80)     NULL,
    Anio                    INT             NULL,
    Placa                   VARCHAR(20)     NULL,
    Vin                     VARCHAR(50)     NULL,
    TipoPlataforma          VARCHAR(50)     NULL,
    FechaInicioServicio     DATE            NULL,
    UltimaAfinacion         DATE            NULL,
    UltimosFrenos           DATE            NULL,
    Rango_Afinacion         INT             NULL,
    ProxAfinacion           DATE            NULL,
    ProxFrenos              DATE            NULL,
    EstadoAfinacion         VARCHAR(50)     NULL,
    EstadoFrenos            VARCHAR(50)     NULL,
    UltimoRecordatorioAfinacion DATE        NULL,
    UltimoRecordatorioFrenos    DATE        NULL,
    Activo                  BOOLEAN         NOT NULL DEFAULT TRUE,
    PRIMARY KEY (Clave_Vehiculo),
    CONSTRAINT fk_vehiculo_cliente
        FOREIGN KEY (Clave_Cliente) REFERENCES Clientes(Clave_Cliente)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Órdenes de trabajo
CREATE TABLE OT (
    OT_Num              VARCHAR(30)     NOT NULL,
    Fecha               DATE            NOT NULL,
    Clave_Cliente       VARCHAR(20)     NOT NULL,
    Clave_Vehiculo      VARCHAR(20)     NOT NULL,
    MecanicoID          VARCHAR(20)     NULL,
    Trabajo_Realizado   TEXT            NULL,
    Horas_Facturadas    DECIMAL(6,2)    NULL DEFAULT 0,
    Costo_MO_Hr         DECIMAL(10,2)   NULL DEFAULT 0,
    Costo_MO            DECIMAL(10,2)   NULL DEFAULT 0,
    Refacciones         DECIMAL(10,2)   NULL DEFAULT 0,
    Total_Sugerido      DECIMAL(10,2)   NULL DEFAULT 0,
    Total_Cobrado       DECIMAL(10,2)   NULL DEFAULT 0,
    Utilidad            DECIMAL(10,2)   NULL DEFAULT 0,
    Estado              VARCHAR(50)     NULL,
    Fecha_Salida        DATE            NULL,
    Odometer_In         INT             NULL,
    Tipo_Servicio       VARCHAR(80)     NULL,
    Prox_Servicio_Km    INT             NULL,
    Prox_Servicio_Fecha DATE            NULL,
    Garantia            BOOLEAN         NULL DEFAULT FALSE,
    Motivo              VARCHAR(255)    NULL,
    Razon               VARCHAR(255)    NULL,
    Duracion            DECIMAL(6,2)    NULL,
    Rentabilidad        DECIMAL(6,2)    NULL,
    Factura             VARCHAR(80)     NULL,
    Subtotal            DECIMAL(10,2)   NULL DEFAULT 0,
    Iva                 DECIMAL(10,2)   NULL DEFAULT 0,
    WebRowID            VARCHAR(80)     NULL,
    Ult_Km              INT             NULL,
    Fecha_Cotizacion    DATE            NULL,
    Fecha_NoIniciado    DATE            NULL,
    Fecha_EnProgreso    DATE            NULL,
    Fecha_Completo      DATE            NULL,
    Fecha_Salida_Cotizacion DATE        NULL,
    PRIMARY KEY (OT_Num),
    CONSTRAINT fk_ot_cliente
        FOREIGN KEY (Clave_Cliente) REFERENCES Clientes(Clave_Cliente)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ot_vehiculo
        FOREIGN KEY (Clave_Vehiculo) REFERENCES Vehiculos(Clave_Vehiculo)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_ot_mecanico
        FOREIGN KEY (MecanicoID) REFERENCES Mecanicos(MecanicoID)
        ON UPDATE CASCADE ON DELETE SET NULL
);

-- Detalles de servicios por OT
CREATE TABLE OT_Detalles (
    ID              INT             NOT NULL AUTO_INCREMENT,
    OT_Num          VARCHAR(30)     NOT NULL,
    Categoria       VARCHAR(100)    NOT NULL,
    Servicio        VARCHAR(255)    NOT NULL,
    Fecha           DATE            NULL,
    Estado          VARCHAR(50)     NULL,
    Observaciones   TEXT            NULL,
    PRIMARY KEY (ID),
    CONSTRAINT fk_otdet_ot
        FOREIGN KEY (OT_Num) REFERENCES OT(OT_Num)
        ON UPDATE CASCADE ON DELETE CASCADE
);

-- Inventario / refacciones
CREATE TABLE Inventario (
    Codigo          VARCHAR(40)     NOT NULL,
    Fecha_Salida    DATE            NOT NULL,
    OT_Num          VARCHAR(30)     NOT NULL,
    Pieza           VARCHAR(255)    NOT NULL,
    Cantidad        INT             NOT NULL DEFAULT 1,
    Precio_Unitario DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Proveedor       VARCHAR(150)    NULL,
    Tipo            VARCHAR(80)     NULL,
    Categoria       VARCHAR(100)    NULL,
    Total           DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Estado          VARCHAR(50)     NULL,
    PRIMARY KEY (Codigo),
    CONSTRAINT fk_inv_ot
        FOREIGN KEY (OT_Num) REFERENCES OT(OT_Num)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Recepciones de vehículos
CREATE TABLE Recepciones (
    ID                      INT             NOT NULL AUTO_INCREMENT,
    Clave_Cliente           VARCHAR(20)     NOT NULL,
    Clave_Vehiculo          VARCHAR(20)     NOT NULL,
    OT_Num                  VARCHAR(30)     NULL,
    FechaRecepcion          DATE            NOT NULL,
    HoraRecepcion           TIME            NULL,
    Kilometraje             INT             NULL,
    Combustible             VARCHAR(20)     NULL,
    Carroceria              VARCHAR(20)     NULL,
    CarroceriaObs           TEXT            NULL,
    Pintura                 VARCHAR(20)     NULL,
    PinturaObs              TEXT            NULL,
    Cristales               VARCHAR(20)     NULL,
    CristalesObs            TEXT            NULL,
    Llantas                 VARCHAR(20)     NULL,
    LlantasObs              TEXT            NULL,
    Luces                   VARCHAR(20)     NULL,
    LucesObs                TEXT            NULL,
    Interior                VARCHAR(20)     NULL,
    InteriorObs             TEXT            NULL,
    Tablero                 VARCHAR(20)     NULL,
    TableroObs              TEXT            NULL,
    Estereo                 VARCHAR(20)     NULL,
    EstereoObs              TEXT            NULL,
    Clima                   VARCHAR(20)     NULL,
    ClimaObs                TEXT            NULL,
    TarjetaCirc             VARCHAR(20)     NULL,
    Poliza                  VARCHAR(20)     NULL,
    Llaves                  VARCHAR(20)     NULL,
    ControlAlarma           VARCHAR(20)     NULL,
    Gato                    VARCHAR(20)     NULL,
    Triangulos              VARCHAR(20)     NULL,
    Tapetes                 VARCHAR(20)     NULL,
    Herramientas            VARCHAR(20)     NULL,
    TAG                     VARCHAR(20)     NULL,
    Otros                   VARCHAR(20)     NULL,
    OtrosObs                TEXT            NULL,
    ObservacionesGenerales  TEXT            NULL,
    AceptaTerminos          VARCHAR(10)     NULL,
    FirmaCliente            VARCHAR(150)    NULL,
    FirmaAsesor             VARCHAR(150)    NULL,
    FechaEnvio              DATE            NULL,
    HoraEnvio               TIME            NULL,
    Origen                  VARCHAR(80)     NULL,
    PDF_Recepcion           VARCHAR(10)     NULL,
    PRIMARY KEY (ID),
    CONSTRAINT fk_rec_cliente
        FOREIGN KEY (Clave_Cliente) REFERENCES Clientes(Clave_Cliente)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_rec_vehiculo
        FOREIGN KEY (Clave_Vehiculo) REFERENCES Vehiculos(Clave_Vehiculo)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_rec_ot
        FOREIGN KEY (OT_Num) REFERENCES OT(OT_Num)
        ON UPDATE CASCADE ON DELETE SET NULL
);

-- Gastos fijos mensuales
CREATE TABLE Gastos_Mensuales (
    ID              INT             NOT NULL AUTO_INCREMENT,
    Fecha_Salida    DATE            NOT NULL,
    Anio            INT             NOT NULL,
    Mes             INT             NOT NULL,
    Renta           DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Luz             DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Agua            DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Internet        DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Publicidad      DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Sueldos         DECIMAL(10,2)   NOT NULL DEFAULT 0,
    Otros           DECIMAL(10,2)   NOT NULL DEFAULT 0,
    TotalMes        DECIMAL(10,2)   NOT NULL DEFAULT 0,
    GastosVariosMes DECIMAL(10,2)   NOT NULL DEFAULT 0,
    PRIMARY KEY (ID),
    UNIQUE KEY uq_gasto_mes (Año, Mes)
);

-- Gastos variables (publicidad, insumos, otros)
CREATE TABLE Gastos_Variables (
    ID              INT             NOT NULL AUTO_INCREMENT,
    Fecha_Salida    DATE            NOT NULL,
    Anio            INT             NOT NULL,
    Mes             INT             NOT NULL,
    Categoria       VARCHAR(100)    NOT NULL,
    Descripcion     VARCHAR(255)    NULL,
    Monto           DECIMAL(10,2)   NOT NULL DEFAULT 0,
    OT_Num          VARCHAR(30)     NULL,
    Movimiento      VARCHAR(20)     NOT NULL DEFAULT 'Egreso',
    PRIMARY KEY (ID),
    CONSTRAINT fk_gasvar_ot
        FOREIGN KEY (OT_Num) REFERENCES OT(OT_Num)
        ON UPDATE CASCADE ON DELETE SET NULL
);

-- =============================================
-- USUARIOS
-- =============================================

CREATE TABLE IF NOT EXISTS Usuarios (
  id_usuario    INT           NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(100)  NOT NULL,
  email         VARCHAR(100)  NOT NULL,
  password      VARCHAR(255)  NOT NULL,
  rol           ENUM('administrador', 'usuario') NOT NULL DEFAULT 'usuario',
  activo        BOOLEAN       NOT NULL DEFAULT TRUE,
  fecha_alta    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_baja    DATETIME      NULL,
  PRIMARY KEY (id_usuario),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- ADMINISTRADOR POR DEFECTO
-- Contraseña: Admin2025* (Sin encriptar)
-- ¡Cambiar en el primer login!
-- =============================================

INSERT INTO Usuarios (nombre, email, password, rol)
SELECT 'Administrador', 'admin@taller.com',
       'Admin2025*',
       'administrador'
WHERE NOT EXISTS (
  SELECT 1 FROM Usuarios WHERE rol = 'administrador'
);

