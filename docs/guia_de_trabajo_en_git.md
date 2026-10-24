# Cómo trabajamos con Git y GitHub

## Cómo subimos la Fase 1

Para la Fase 1 integramos las partes de los cinco integrantes en una sola copia del proyecto, la probamos
completa y la subimos al repositorio en un solo push.

## Crear el repositorio

1. En GitHub: **New repository**, nombre `cuponera-sv`, sin README (el proyecto ya trae uno).
2. **Settings > Collaborators > Add people**: agregar a cada integrante y al docente **@AcostaGTR**.
3. En la computadora:

```bash
git clone https://github.com/USUARIO/cuponera-sv.git
cd cuponera-sv
# copiar aquí el contenido del proyecto
git add .
git commit -m "Proyecto La Cuponera SV: Fase 1"
git branch -M main
git push -u origin main
```

## Reglas del equipo

- Antes de empezar a trabajar: `git pull`.
- Commits pequeños y con mensajes que digan qué cambió, por ejemplo:
  - `Validación de edad en el registro de clientes`
  - `Corrige el mensaje de error del NIT`
- No subir `config/config.local.php` (tiene las claves de cada quien; ya está en `.gitignore`).
- Si dos personas tocan el mismo archivo y hay conflicto, se resuelve juntos antes de unir.
- Cada falla que se encuentre se registra como tarjeta en el tablero.