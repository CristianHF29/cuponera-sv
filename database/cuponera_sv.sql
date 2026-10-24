-- =============================================================
--  La Cuponera SV - base de datos
--  Compatible con MySQL 5.7+ y MariaDB 10.3+ (la que trae XAMPP)
--
--  Cómo importarla:
--    phpMyAdmin > pestaña Importar > elegir este archivo > Importar
--  o desde la consola:
--    mysql -u root -p < database/cuponera_sv.sql
--
--  OJO: si la base cuponera_sv ya existe, se borra y se crea de nuevo.
--  Todos los usuarios de prueba tienen la contraseña: Cuponera2026
-- =============================================================

SET NAMES utf8mb4;
SET time_zone = '-06:00';

DROP DATABASE IF EXISTS cuponera_sv;
CREATE DATABASE cuponera_sv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cuponera_sv;

-- -------------------------------------------------------------
-- Administradores del sistema.
-- creado_por guarda qué administrador registró a cada uno.
-- -------------------------------------------------------------
CREATE TABLE administradores (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100) NOT NULL,
    correo      VARCHAR(120) NOT NULL,
    usuario     VARCHAR(30)  NOT NULL,
    contrasena  VARCHAR(255) NOT NULL COMMENT 'hash generado con password_hash()',
    creado_por  INT UNSIGNED NULL,
    creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_correo (correo),
    UNIQUE KEY uq_admin_usuario (usuario),
    CONSTRAINT fk_admin_creado_por FOREIGN KEY (creado_por)
        REFERENCES administradores (id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Empresas ofertantes. Se registran como "pendiente" y un
-- administrador las aprueba (fijando su comisión) o las rechaza.
-- -------------------------------------------------------------
CREATE TABLE empresas (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre              VARCHAR(150) NOT NULL,
    nit                 CHAR(17)     NOT NULL COMMENT 'formato 0000-000000-000-0',
    direccion           VARCHAR(255) NOT NULL,
    telefono            CHAR(9)      NOT NULL COMMENT 'formato 0000-0000',
    correo              VARCHAR(120) NOT NULL,
    usuario             VARCHAR(30)  NOT NULL,
    contrasena          VARCHAR(255) NOT NULL,
    estado              ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    porcentaje_comision DECIMAL(5,2) NULL COMMENT 'se fija al aprobar la solicitud',
    motivo_rechazo      VARCHAR(255) NULL,
    revisado_por        INT UNSIGNED NULL,
    revisado_en         DATETIME     NULL,
    creado_en           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_empresa_nit (nit),
    UNIQUE KEY uq_empresa_correo (correo),
    UNIQUE KEY uq_empresa_usuario (usuario),
    KEY idx_empresa_estado (estado),
    CONSTRAINT fk_empresa_revisor FOREIGN KEY (revisado_por)
        REFERENCES administradores (id) ON DELETE SET NULL,
    CONSTRAINT chk_empresa_comision CHECK (porcentaje_comision IS NULL OR (porcentaje_comision > 0 AND porcentaje_comision <= 100))
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Clientes que compran cupones
-- -------------------------------------------------------------
CREATE TABLE clientes (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario          VARCHAR(30)  NOT NULL,
    correo           VARCHAR(120) NOT NULL,
    contrasena       VARCHAR(255) NOT NULL,
    nombre_completo  VARCHAR(100) NOT NULL,
    apellidos        VARCHAR(100) NOT NULL,
    dui              CHAR(10)     NOT NULL COMMENT 'formato 00000000-0',
    fecha_nacimiento DATE         NOT NULL,
    creado_en        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cliente_usuario (usuario),
    UNIQUE KEY uq_cliente_correo (correo),
    UNIQUE KEY uq_cliente_dui (dui)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Ofertas que publica cada empresa aprobada.
-- cantidad_cupones en NULL significa que no hay límite.
-- -------------------------------------------------------------
CREATE TABLE ofertas (
    id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    empresa_id         INT UNSIGNED  NOT NULL,
    titulo             VARCHAR(150)  NOT NULL,
    precio_regular     DECIMAL(10,2) NOT NULL,
    precio_oferta      DECIMAL(10,2) NOT NULL,
    fecha_inicio       DATE          NOT NULL,
    fecha_fin          DATE          NOT NULL,
    fecha_limite_canje DATE          NOT NULL,
    cantidad_cupones   INT UNSIGNED  NULL COMMENT 'NULL = sin límite',
    descripcion        TEXT          NOT NULL,
    estado             ENUM('disponible', 'no disponible') NOT NULL DEFAULT 'disponible',
    creado_en          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en     DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_oferta_empresa (empresa_id),
    KEY idx_oferta_vigencia (estado, fecha_inicio, fecha_fin),
    CONSTRAINT fk_oferta_empresa FOREIGN KEY (empresa_id)
        REFERENCES empresas (id) ON DELETE CASCADE,
    CONSTRAINT chk_oferta_precios CHECK (precio_oferta > 0 AND precio_oferta < precio_regular),
    CONSTRAINT chk_oferta_fechas CHECK (fecha_fin >= fecha_inicio AND fecha_limite_canje >= fecha_fin)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Compras: cada fila es una factura. Un cliente puede llevar
-- de 1 a 5 cupones de la misma oferta en total.
-- -------------------------------------------------------------
CREATE TABLE compras (
    id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    numero_factura   VARCHAR(20)      NULL COMMENT 'ej. F2026-000001, se llena al registrar la compra',
    cliente_id       INT UNSIGNED     NOT NULL,
    oferta_id        INT UNSIGNED     NOT NULL,
    cantidad         TINYINT UNSIGNED NOT NULL,
    precio_unitario  DECIMAL(10,2)    NOT NULL,
    total            DECIMAL(10,2)    NOT NULL,
    tarjeta_ultimos4 CHAR(4)          NOT NULL COMMENT 'nunca se guarda el número completo ni el CVV',
    creado_en        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_compra_factura (numero_factura),
    KEY idx_compra_cliente (cliente_id),
    KEY idx_compra_oferta (oferta_id),
    CONSTRAINT fk_compra_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id),
    CONSTRAINT fk_compra_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas (id),
    CONSTRAINT chk_compra_cantidad CHECK (cantidad BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Cupones: uno por cada unidad comprada, con su código único.
-- El código es lo que el cliente presenta en el negocio.
-- -------------------------------------------------------------
CREATE TABLE cupones (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo      VARCHAR(20)  NOT NULL,
    compra_id   INT UNSIGNED NOT NULL,
    oferta_id   INT UNSIGNED NOT NULL,
    cliente_id  INT UNSIGNED NOT NULL,
    estado      ENUM('vigente', 'canjeado') NOT NULL DEFAULT 'vigente',
    canjeado_en DATETIME     NULL,
    creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cupon_codigo (codigo),
    KEY idx_cupon_compra (compra_id),
    CONSTRAINT fk_cupon_compra FOREIGN KEY (compra_id) REFERENCES compras (id) ON DELETE CASCADE,
    CONSTRAINT fk_cupon_oferta FOREIGN KEY (oferta_id) REFERENCES ofertas (id),
    CONSTRAINT fk_cupon_cliente FOREIGN KEY (cliente_id) REFERENCES clientes (id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Solicitudes para recuperar contraseña (sirve para los 3 tipos
-- de usuario). Solo se guarda el hash del token: el token real
-- viaja únicamente en el enlace del correo.
-- usuario_id apunta a administradores, empresas o clientes según
-- tipo_usuario, por eso no lleva llave foránea.
-- -------------------------------------------------------------
CREATE TABLE recuperacion_contrasena (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tipo_usuario ENUM('administrador', 'empresa', 'cliente') NOT NULL,
    usuario_id   INT UNSIGNED NOT NULL,
    token_hash   CHAR(64)     NOT NULL COMMENT 'sha256 del token',
    expira_en    DATETIME     NOT NULL,
    usado        TINYINT(1)   NOT NULL DEFAULT 0,
    usado_en     DATETIME     NULL,
    creado_en    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_recuperacion_token (token_hash),
    KEY idx_recuperacion_usuario (tipo_usuario, usuario_id)
) ENGINE=InnoDB;


-- =============================================================
--  Datos de prueba
--  Las fechas se calculan a partir del día en que se importa el
--  script, así las ofertas siempre aparecen vigentes.
-- =============================================================

INSERT INTO administradores (id, nombre, correo, usuario, contrasena, creado_por, creado_en) VALUES
(1, 'Administración General', 'admin@cuponera.test', 'admin', '$2y$10$iBh89tQ4xwBekxyDH8JH4OS/g8N4w.asp5AiP/BXGWd9TfF/1KZ3G', NULL, NOW() - INTERVAL 60 DAY),
(2, 'Karla Méndez', 'karla.mendez@cuponera.test', 'kmendez', '$2y$10$0rz761xPlWwtjhECLr.9HeitjnsrfZJlwxJEygTzhXyE3mSLS5EXi', 1, NOW() - INTERVAL 30 DAY);

INSERT INTO empresas (id, nombre, nit, direccion, telefono, correo, usuario, contrasena, estado, porcentaje_comision, motivo_rechazo, revisado_por, revisado_en, creado_en) VALUES
(1, 'Pupusería La Ceiba', '0614-150318-102-5', 'Calle La Mascota #12, colonia San Benito, San Salvador', '2264-1188', 'contacto@laceiba.test', 'laceiba',
    '$2y$10$Mv7Ztx5R4LSR/oT6NB4knuIY/LvLi/8DWmLDaNPoI/loz1j.PJ1Pi', 'aprobada', 10.00, NULL, 1, NOW() - INTERVAL 50 DAY, NOW() - INTERVAL 52 DAY),
(2, 'Café Ataco Tostadores', '0315-220719-101-8', 'Avenida Central y 2.a calle poniente, Concepción de Ataco, Ahuachapán', '2450-3321', 'hola@cafeataco.test', 'cafeataco',
    '$2y$10$kWLgq6ebsY.cAL5w62v0MuITXmOZc8aMryckPhhW3ids8gziCbPEu', 'aprobada', 12.50, NULL, 1, NOW() - INTERVAL 45 DAY, NOW() - INTERVAL 47 DAY),
(3, 'Escuela de Surf Olas del Tunco', '0511-030520-103-4', 'Playa El Tunco, Tamanique, La Libertad', '7788-4521', 'reservas@olasdeltunco.test', 'olasdeltunco',
    '$2y$10$blMfYVFy4N2sg9r9tSpCGODevXp5bpWhh3Jh.qT308R3yhNvR.oXK', 'aprobada', 15.00, NULL, 2, NOW() - INTERVAL 28 DAY, NOW() - INTERVAL 29 DAY),
(4, 'Spa Flor de Izote', '0614-110821-104-2', 'Paseo General Escalón #3700, San Salvador', '2223-9087', 'citas@flordeizote.test', 'flordeizote',
    '$2y$10$EDP3MogKnUpVRzZEYTlKRu6.uO9wRhCxjJ6VktT2uLnXQ9XUsYZfa', 'aprobada', 8.00, NULL, 2, NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 21 DAY),
(5, 'Ferretería Don Chepe', '0614-250622-105-9', 'Boulevard del Ejército, kilómetro 3, Soyapango', '2277-4410', 'ventas@donchepe.test', 'donchepe',
    '$2y$10$daQskbOP.Zt7sFu6zXEh6uMLQ4K5iNT2/459q5ALTDQwvsSWXfgxC', 'pendiente', NULL, NULL, NULL, NULL, NOW() - INTERVAL 3 DAY),
(6, 'Óptica Mirada Clara', '0210-140923-106-1', '4.a avenida sur #15, Santa Ana', '2441-7720', 'atencion@miradaclara.test', 'miradaclara',
    '$2y$10$tQ1w/be1RemQk3jJEjVBZOGbOI20pRt04Ha5YKUh.W8qkQefXFX2u', 'pendiente', NULL, NULL, NULL, NULL, NOW() - INTERVAL 2 DAY),
(7, 'Gimnasio Volcán Fit', '1217-080224-107-6', 'Avenida Roosevelt sur #208, San Miguel', '2661-0934', 'info@volcanfit.test', 'volcanfit',
    '$2y$10$jAxpGk2TskQNhZD6DlsA6uDkkoS9csgSqQa.vLAsRstvNahN0AswG', 'pendiente', NULL, NULL, NULL, NULL, NOW() - INTERVAL 5 HOUR),
(8, 'Panadería San Rafael', '0614-300125-108-3', 'Calle Arce #940, San Salvador', '2221-5566', 'pedidos@sanrafael.test', 'sanrafael',
    '$2y$10$/xRxcho8z9.3BWRwhNWGSupRdr2wGvqFbpHi7hgSdrzyc0CISSxWu', 'rechazada', NULL,
    'El NIT no coincide con el nombre del negocio. Escríbenos a soporte@cuponera.test para corregirlo.', 1, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 12 DAY);

INSERT INTO clientes (id, usuario, correo, contrasena, nombre_completo, apellidos, dui, fecha_nacimiento, creado_en) VALUES
(1, 'maria.lopez', 'maria.lopez@correo.test', '$2y$10$Mv/FT9UN.97qCY8eA/0nHOSYxrD2v8tIsOjTIDhgnfpePaU9f/vya', 'María José', 'López Hernández', '04567812-3', '1998-05-14', NOW() - INTERVAL 20 DAY),
(2, 'carlos.rivas', 'carlos.rivas@correo.test', '$2y$10$FH4Rb2cL2RCERpyib99VbOycRpwOGgwOoCHpHI9zl.McONdQbx6S.', 'Carlos Ernesto', 'Rivas Portillo', '05123498-7', '1990-11-02', NOW() - INTERVAL 15 DAY),
(3, 'ana.martinez', 'ana.martinez@correo.test', '$2y$10$jME.mXawDqQwTCOfsSQcsOM4MXtyn.8jD.4ouCBSy8LumVeXAsCQK', 'Ana Gabriela', 'Martínez Cruz', '06234567-1', '2001-03-21', NOW() - INTERVAL 8 DAY);

INSERT INTO ofertas (id, empresa_id, titulo, precio_regular, precio_oferta, fecha_inicio, fecha_fin, fecha_limite_canje, cantidad_cupones, descripcion, estado, creado_en) VALUES
(1, 1, 'Combo familiar: 12 pupusas y 4 refrescos', 18.00, 12.50,
    CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 40 DAY, CURDATE() + INTERVAL 55 DAY, 100,
    'Doce pupusas a escoger entre revueltas, frijol con queso, queso con loroco y chicharrón, con su curtido y salsa de tomate, más cuatro refrescos naturales de la casa. Puedes comerlas en el local o pedirlas para llevar.',
    'disponible', NOW() - INTERVAL 10 DAY),
(2, 1, 'Martes de 2x1 en pupusas de queso con loroco', 1.60, 0.80,
    CURDATE() - INTERVAL 3 DAY, CURDATE() + INTERVAL 25 DAY, CURDATE() + INTERVAL 30 DAY, NULL,
    'Cada cupón vale por dos pupusas de queso con loroco al precio de una. Válido solo los martes en el local de la colonia San Benito, de 4:00 p. m. a 9:00 p. m.',
    'disponible', NOW() - INTERVAL 3 DAY),
(3, 1, 'Desayuno típico para dos personas', 9.00, 6.30,
    CURDATE() - INTERVAL 5 DAY, CURDATE() + INTERVAL 20 DAY, CURDATE() + INTERVAL 30 DAY, 40,
    'Huevos picados, frijoles molidos, plátano frito, crema, queso fresco, tortillas y café de olla para dos personas. De lunes a viernes, de 7:00 a 10:00 a. m.',
    'no disponible', NOW() - INTERVAL 5 DAY),
(4, 2, 'Dos libras de café de altura recién tostado', 16.00, 11.20,
    CURDATE() - INTERVAL 15 DAY, CURDATE() + INTERVAL 45 DAY, CURDATE() + INTERVAL 60 DAY, 60,
    'Café de la cordillera Apaneca-Ilamatepec, cultivado sobre los 1,400 metros y tostado la misma semana. Lo pides en grano o molido para cafetera, prensa francesa o greca.',
    'disponible', NOW() - INTERVAL 15 DAY),
(5, 2, 'Tour del café con catación incluida', 25.00, 17.50,
    CURDATE() + INTERVAL 5 DAY, CURDATE() + INTERVAL 60 DAY, CURDATE() + INTERVAL 75 DAY, 20,
    'Recorrido de dos horas por la finca, del cafetal al tostado, con una catación guiada de tres cafés de la zona. Salidas los sábados a las 9:00 a. m.',
    'disponible', NOW() - INTERVAL 1 DAY),
(6, 3, 'Clase de surf de 2 horas con tabla incluida', 45.00, 29.00,
    CURDATE() - INTERVAL 8 DAY, CURDATE() + INTERVAL 35 DAY, CURDATE() + INTERVAL 50 DAY, 30,
    'Clase para principiantes en grupos de máximo cuatro personas, con instructor certificado, tabla, licra y fotos de recuerdo. Reserva tu horario por WhatsApp al presentar el cupón.',
    'disponible', NOW() - INTERVAL 8 DAY),
(7, 3, 'Alquiler de tabla por un día completo', 20.00, 12.00,
    CURDATE() - INTERVAL 40 DAY, CURDATE() - INTERVAL 2 DAY, CURDATE() + INTERVAL 5 DAY, 25,
    'Tabla de surf o de bodyboard de 8:00 a. m. a 5:00 p. m., con leash y cera incluidos.',
    'disponible', NOW() - INTERVAL 40 DAY),
(8, 4, 'Masaje relajante de 60 minutos', 40.00, 24.00,
    CURDATE() - INTERVAL 12 DAY, CURDATE() + INTERVAL 30 DAY, CURDATE() + INTERVAL 45 DAY, 50,
    'Masaje de cuerpo completo con aceites esenciales de lavanda o eucalipto, en cabina privada y con música suave. Incluye té de bienvenida.',
    'disponible', NOW() - INTERVAL 12 DAY),
(9, 4, 'Día de spa para dos personas', 120.00, 79.00,
    CURDATE() - INTERVAL 6 DAY, CURDATE() + INTERVAL 20 DAY, CURDATE() + INTERVAL 40 DAY, 10,
    'Circuito de hidroterapia, exfoliación corporal, masaje de 45 minutos y almuerzo ligero para dos. Ideal para regalar en un cumpleaños o aniversario.',
    'disponible', NOW() - INTERVAL 6 DAY);

INSERT INTO compras (id, cliente_id, oferta_id, cantidad, precio_unitario, total, tarjeta_ultimos4, creado_en) VALUES
(1, 1, 1, 2, 12.50,  25.00, '4242', NOW() - INTERVAL 6 DAY),
(2, 1, 8, 1, 24.00,  24.00, '4242', NOW() - INTERVAL 9 DAY),
(3, 2, 6, 3, 29.00,  87.00, '0005', NOW() - INTERVAL 4 DAY),
(4, 3, 4, 2, 11.20,  22.40, '1881', NOW() - INTERVAL 2 DAY),
(5, 2, 1, 1, 12.50,  12.50, '0005', NOW() - INTERVAL 1 DAY),
(6, 3, 9, 2, 79.00, 158.00, '1881', NOW() - INTERVAL 5 HOUR);

-- número de factura con el año de la compra y el correlativo
UPDATE compras SET numero_factura = CONCAT('F', YEAR(creado_en), '-', LPAD(id, 6, '0')) WHERE id > 0;

INSERT INTO cupones (codigo, compra_id, oferta_id, cliente_id, estado, canjeado_en, creado_en) VALUES
('CUP-7KQ3-M9XA', 1, 1, 1, 'vigente',  NULL,                   NOW() - INTERVAL 6 DAY),
('CUP-H2ZD-4TPW', 1, 1, 1, 'vigente',  NULL,                   NOW() - INTERVAL 6 DAY),
('CUP-R8NF-3KLC', 2, 8, 1, 'canjeado', NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 9 DAY),
('CUP-W5TB-QJ7E', 3, 6, 2, 'vigente',  NULL,                   NOW() - INTERVAL 4 DAY),
('CUP-9GMV-XH2R', 3, 6, 2, 'vigente',  NULL,                   NOW() - INTERVAL 4 DAY),
('CUP-K4PL-8ZND', 3, 6, 2, 'vigente',  NULL,                   NOW() - INTERVAL 4 DAY),
('CUP-T3YH-6FQB', 4, 4, 3, 'vigente',  NULL,                   NOW() - INTERVAL 2 DAY),
('CUP-M7CW-2RJK', 4, 4, 3, 'vigente',  NULL,                   NOW() - INTERVAL 2 DAY),
('CUP-B6XN-P4HT', 5, 1, 2, 'vigente',  NULL,                   NOW() - INTERVAL 1 DAY),
('CUP-Z8QE-5MGL', 6, 9, 3, 'vigente',  NULL,                   NOW() - INTERVAL 5 HOUR),
('CUP-N3VU-J9DA', 6, 9, 3, 'vigente',  NULL,                   NOW() - INTERVAL 5 HOUR);
