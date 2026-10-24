<?php
// Registro de clientes y empresas

$router->get('/registro', 'RegistroController@elegir');
$router->get('/registro/cliente', 'RegistroController@cliente');
$router->post('/registro/cliente', 'RegistroController@guardarCliente');
$router->get('/registro/empresa', 'RegistroController@empresa');
$router->post('/registro/empresa', 'RegistroController@guardarEmpresa');
$router->get('/registro/empresa/enviada', 'RegistroController@solicitudEnviada');
