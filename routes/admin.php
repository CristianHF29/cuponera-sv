<?php
// Panel del administrador

$router->get('/admin', 'AdminController@inicio');
$router->get('/admin/solicitudes', 'AdminController@solicitudes');
$router->post('/admin/solicitudes/{id}/aprobar', 'AdminController@aprobar');
$router->post('/admin/solicitudes/{id}/rechazar', 'AdminController@rechazar');
$router->get('/admin/administradores', 'AdminController@administradores');
$router->post('/admin/administradores', 'AdminController@guardarAdministrador');
