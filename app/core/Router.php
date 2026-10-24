<?php
/*
 * Router hecho a mano.
 * Cada archivo de la carpeta routes/ registra sus rutas con get() y post().
 * Los parámetros entre llaves, como {id}, solo aceptan números.
 *
 *   $router->get('/oferta/{id}', 'InicioController@oferta');
 */
class Router
{
    private array $rutas = [];

    public function get(string $patron, string $accion): void
    {
        $this->agregar('GET', $patron, $accion);
    }

    public function post(string $patron, string $accion): void
    {
        $this->agregar('POST', $patron, $accion);
    }

    private function agregar(string $metodo, string $patron, string $accion): void
    {
        $expresion = '#^' . preg_replace('#\{[a-z_]+\}#', '(\d+)', $patron) . '$#';
        $this->rutas[] = ['metodo' => $metodo, 'expresion' => $expresion, 'accion' => $accion];
    }

    public function despachar(string $metodo, string $ruta): void
    {
        if ($metodo === 'HEAD') {
            $metodo = 'GET';
        }

        foreach ($this->rutas as $r) {
            if ($r['metodo'] === $metodo && preg_match($r['expresion'], $ruta, $partes)) {
                array_shift($partes); // la primera coincidencia es la ruta completa
                [$clase, $funcion] = explode('@', $r['accion']);
                (new $clase())->$funcion(...$partes);
                return;
            }
        }

        pagina_de_error(404);
    }
}
