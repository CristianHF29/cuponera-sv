<?php
/*
 * Tokens para recuperar la contraseña. Solo guardamos el hash (sha256) del token;
 * el token real va únicamente en el enlace que recibe la persona.
 */
class RecuperacionContrasena extends Modelo
{
    public function crear(string $tipo, int $usuarioId, string $tokenHash, int $minutos): void
    {
        // si pidió varios enlaces, solo el último sirve
        $this->ejecutar(
            'UPDATE recuperacion_contrasena SET usado = 1 WHERE tipo_usuario = ? AND usuario_id = ? AND usado = 0',
            [$tipo, $usuarioId]
        );
        $this->ejecutar(
            'INSERT INTO recuperacion_contrasena (tipo_usuario, usuario_id, token_hash, expira_en)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))',
            [$tipo, $usuarioId, $tokenHash, $minutos]
        );
    }

    // Devuelve la solicitud solo si no se ha usado y no ha vencido
    public function buscarVigente(string $tokenHash): ?array
    {
        return $this->fila(
            'SELECT * FROM recuperacion_contrasena WHERE token_hash = ? AND usado = 0 AND expira_en > NOW() LIMIT 1',
            [$tokenHash]
        );
    }

    public function marcarUsado(int $id): void
    {
        $this->ejecutar('UPDATE recuperacion_contrasena SET usado = 1, usado_en = NOW() WHERE id = ?', [$id]);
    }
}
