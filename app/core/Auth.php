<?php
/*
 * Sesión de los usuarios.
 * Hay tres tipos de cuenta y cada uno vive en su propia tabla:
 * administradores, empresas y clientes.
 */
class Auth
{
    public const TIPOS = [
        'cliente' => 'Cliente',
        'empresa' => 'Empresa',
        'administrador' => 'Administrador',
    ];

    public static function modelo(string $tipo): ModeloUsuario
    {
        return match ($tipo) {
            'cliente' => new Cliente(),
            'empresa' => new Empresa(),
            'administrador' => new Administrador(),
        };
    }

    public static function iniciar(string $tipo, array $cuenta): void
    {
        // id de sesión nuevo al entrar, para evitar que alguien reutilice uno viejo
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id' => (int) $cuenta['id'],
            'tipo' => $tipo,
            'nombre' => $tipo === 'cliente' ? $cuenta['nombre_completo'] : $cuenta['nombre'],
            'usuario' => $cuenta['usuario'],
        ];
    }

    public static function cerrar(): void
    {
        unset($_SESSION['usuario'], $_SESSION['volver_a']);
        session_regenerate_id(true);
    }

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['usuario']) ? (int) $_SESSION['usuario']['id'] : null;
    }

    public static function tipo(): ?string
    {
        return $_SESSION['usuario']['tipo'] ?? null;
    }

    public static function es(string $tipo): bool
    {
        return self::tipo() === $tipo;
    }

    // Se llama al inicio de cada página protegida
    public static function exigir(string $tipo): void
    {
        if (!self::usuario()) {
            // guardamos a dónde iba, para regresarlo ahí después de iniciar sesión
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                $_SESSION['volver_a'] = ['ruta' => ruta_actual(), 'tipo' => $tipo];
            }
            flash('aviso', 'Inicia sesión para continuar.');
            redirigir('/login?tipo=' . $tipo);
        }
        if (!self::es($tipo)) {
            pagina_de_error(403);
        }
    }

    // Página principal de cada tipo de cuenta
    public static function panel(?string $tipo = null): string
    {
        return match ($tipo ?? self::tipo()) {
            'administrador' => '/admin',
            'empresa' => '/empresa',
            default => '/',
        };
    }

    public static function destinoDespuesDeEntrar(): string
    {
        $destino = $_SESSION['volver_a'] ?? null;
        unset($_SESSION['volver_a']);

        if (is_array($destino) && ($destino['tipo'] ?? '') === self::tipo() && str_starts_with($destino['ruta'] ?? '', '/')) {
            return $destino['ruta'];
        }
        return self::panel();
    }
}
