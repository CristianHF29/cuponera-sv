<?php
// Pantallas del sitio: catálogo, compra, cupones del cliente, panel de empresa y reportes

$router->get('/', 'InicioController@inicio');
$router->get('/oferta/{id}', 'InicioController@oferta');

$router->post('/oferta/{id}/comprar', 'CompraController@comprar');
$router->get('/mis-cupones', 'CompraController@historial');
$router->get('/factura/{id}', 'CompraController@factura');

$router->get('/empresa', 'EmpresaController@panel');
$router->get('/empresa/ofertas/nueva', 'EmpresaController@nueva');
$router->post('/empresa/ofertas', 'EmpresaController@guardar');
$router->get('/empresa/ofertas/{id}/editar', 'EmpresaController@editar');
$router->post('/empresa/ofertas/{id}', 'EmpresaController@actualizar');
$router->post('/empresa/ofertas/{id}/estado', 'EmpresaController@cambiarEstado');

$router->get('/admin/reportes', 'ReporteController@index');
