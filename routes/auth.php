<?php
// Inicio de sesión y recuperación de contraseña (los tres tipos de cuenta)

$router->get('/login', 'AuthController@formulario');
$router->post('/login', 'AuthController@entrar');
$router->post('/salir', 'AuthController@salir');

$router->get('/recuperar', 'RecuperarController@formulario');
$router->post('/recuperar', 'RecuperarController@enviar');
$router->get('/recuperar/enviado', 'RecuperarController@enviado');
$router->get('/restablecer', 'RecuperarController@formularioNueva');
$router->post('/restablecer', 'RecuperarController@guardarNueva');
