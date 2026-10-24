<?php
/*
 * Lo que comparten administradores, empresas y clientes:
 * buscar la cuenta para iniciar sesión y cambiar la contraseña.
 * Cada modelo hijo solo indica su tabla.
 */
abstract class ModeloUsuario extends Modelo
{
    protected string $tabla;

    public function buscarPorId(int $id): ?array
    {
        return $this->fila("SELECT * FROM {$this->tabla} WHERE id = ?", [$id]);
    }

    // Se puede entrar con el usuario o con el correo
    public function buscarPorUsuarioOCorreo(string $dato): ?array
    {
        return $this->fila(
            "SELECT * FROM {$this->tabla} WHERE usuario = ? OR correo = ? LIMIT 1",
            [$dato, mb_strtolower($dato)]
        );
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        return $this->fila("SELECT * FROM {$this->tabla} WHERE correo = ? LIMIT 1", [$correo]);
    }

    public function cambiarClave(int $id, string $claveNueva): void
    {
        $this->ejecutar(
            "UPDATE {$this->tabla} SET contrasena = ? WHERE id = ?",
            [password_hash($claveNueva, PASSWORD_DEFAULT), $id]
        );
    }
}
