# La Cuponera SV

Aplicación web para vender cupones de descuento de negocios salvadoreños.
Proyecto de cátedra de Lenguajes Interpretados en el Servidor (LIS941), Fase 1.

Está hecha con PHP 8 y MySQL, con el patrón MVC usando clases propias (sin framework).

## Qué incluye

- Inicio de sesión para clientes, empresas y administradores.
- Registro de clientes y de empresas. Las empresas quedan pendientes hasta que un administrador las revisa.
- Aprobación o rechazo de empresas, con porcentaje de comisión al aprobar.
- Registro de nuevos administradores desde el panel.
- Recuperación de contraseña para los tres tipos de usuario.
- Validaciones en todos los formularios y páginas propias para los errores 403, 404 y 500.
- Avances extra: catálogo con buscador, compra simulada con factura y código único por cupón,
  historial del cliente, panel de ofertas de la empresa y reportes del administrador.

## Requisitos

- XAMPP 8 (PHP 8 con MySQL o MariaDB)
- Un navegador actualizado

## Instalación

1. Copiar la carpeta del proyecto en `C:\xampp\htdocs\` con el nombre `cuponera-sv`.
2. Encender Apache y MySQL desde el panel de XAMPP.
3. En phpMyAdmin, abrir la pestaña **Importar**, elegir `database/cuponera_sv.sql` e importarlo.
   El script crea la base `cuponera_sv` con datos de prueba (si ya existía, la borra y la crea de nuevo).
4. Abrir `localhost/cuponera-sv/` en el navegador.

Si MySQL tiene contraseña, crear `config/config.local.php` con:

```php
<?php
define('DB_CLAVE', 'tu_clave');
```

Si todas las páginas dan error 404, agregar `define('URL_AMIGABLES', false);` en ese mismo archivo.

También se puede probar sin Apache, desde la carpeta del proyecto:

```
php -S localhost:8000 index.php
```

## Usuarios de prueba

Todos tienen la contraseña `Cuponera2026` y se puede entrar con el usuario o con el correo.

| Tipo | Usuarios | Detalle |
|---|---|---|
| Administrador | `admin`, `kmendez` | |
| Empresa | `laceiba`, `cafeataco`, `olasdeltunco`, `flordeizote` | aprobadas, con ofertas y ventas |
| Empresa | `donchepe`, `miradaclara`, `volcanfit` | pendientes de aprobación |
| Empresa | `sanrafael` | rechazada (muestra el motivo al entrar) |
| Cliente | `maria.lopez`, `carlos.rivas`, `ana.martinez` | con compras hechas |

Para probar la compra sirve un número de tarjeta de 13 a 19 dígitos (por ejemplo `4242 4242 4242 4242`),
un vencimiento futuro (`12/30`) y un CVV de 3 dígitos. No se hace ningún cargo.

## Recuperación de contraseña

Los correos no se envían: se guardan en `storage/correos/` y la pantalla muestra el enlace para seguir con la prueba.

## Estructura

```
cuponera-sv/
├── index.php          punto de entrada
├── .htaccess          manda todas las rutas a index.php
├── config/            conexión a MySQL y ajustes generales
├── routes/            rutas de cada módulo
├── app/
│   ├── core/          router, conexión, validador, sesión, correo, helpers
│   ├── controllers/   un controlador por sección
│   ├── models/        consultas a la base de datos (PDO)
│   ├── views/         pantallas en PHP y HTML
│   └── lib/           librería para enviar correos (PHPMailer)
├── assets/            CSS, JavaScript e imágenes
├── database/          script SQL y diagrama en formato DBML
├── docs/              guía de trabajo en Git
└── storage/           registros de errores y correos de prueba
```

## Base de datos

El script `database/cuponera_sv.sql` crea 7 tablas: `administradores`, `empresas`, `clientes`, `ofertas`,
`compras`, `cupones` y `recuperacion_contrasena`. Las contraseñas se guardan con `password_hash()` y de la
tarjeta solo se guardan los últimos 4 dígitos.