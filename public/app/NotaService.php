<?php

declare(strict_types=1);

/**
 * Servicio de persistencia para notas y fuentes.
 */
final class NotaService
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::getInstance()->getConnection();
    }

    public function guardarBorrador(array $datos, int $usuarioId): int
    {
        $sql = 'INSERT INTO notas (usuario_id, titulo, seccion, extension, palabras_clave, instrucciones_extra, estado)
                VALUES (:usuario_id, :titulo, :seccion, :extension, :palabras_clave, :instrucciones_extra, :estado)';

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'usuario_id' => $usuarioId,
            'titulo' => $datos['titular'],
            'seccion' => $datos['seccion'],
            'extension' => $datos['extension'],
            'palabras_clave' => $datos['palabras_clave'] !== '' ? $datos['palabras_clave'] : null,
            'instrucciones_extra' => $datos['instrucciones_extra'] !== '' ? $datos['instrucciones_extra'] : null,
            'estado' => 'borrador',
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function guardarFuentes(int $notaId, array $urls): void
    {
        $sql = 'INSERT INTO fuentes (nota_id, url) VALUES (:nota_id, :url)';
        $statement = $this->connection->prepare($sql);

        foreach ($urls as $url) {
            $statement->execute([
                'nota_id' => $notaId,
                'url' => $url,
            ]);
        }
    }

    public function actualizarContenido(int $notaId, string $contenido): void
    {
        $sql = 'UPDATE notas
                SET contenido_generado = :contenido_generado,
                    estado = :estado,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id';

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'contenido_generado' => $contenido,
            'estado' => 'borrador',
            'id' => $notaId,
        ]);
    }

    public function obtenerNota(int $notaId, int $usuarioId): ?array
    {
        $sql = 'SELECT
                    n.id,
                    n.usuario_id,
                    n.titulo,
                    n.seccion,
                    n.extension,
                    n.palabras_clave,
                    n.instrucciones_extra,
                    n.contenido_generado,
                    n.estado,
                    n.created_at,
                    n.updated_at,
                    f.id AS fuente_id,
                    f.url AS fuente_url,
                    f.contenido_scrapeado,
                    f.error AS fuente_error,
                    f.created_at AS fuente_created_at
                FROM notas n
                LEFT JOIN fuentes f ON f.nota_id = n.id
                WHERE n.id = :nota_id AND n.usuario_id = :usuario_id
                ORDER BY f.id ASC';

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'nota_id' => $notaId,
            'usuario_id' => $usuarioId,
        ]);

        $rows = $statement->fetchAll();

        if ($rows === []) {
            return null;
        }

        $firstRow = $rows[0];
        $nota = [
            'id' => (int) $firstRow['id'],
            'usuario_id' => (int) $firstRow['usuario_id'],
            'titulo' => (string) $firstRow['titulo'],
            'seccion' => (string) $firstRow['seccion'],
            'extension' => (string) $firstRow['extension'],
            'palabras_clave' => $firstRow['palabras_clave'],
            'instrucciones_extra' => $firstRow['instrucciones_extra'],
            'contenido_generado' => $firstRow['contenido_generado'],
            'estado' => (string) $firstRow['estado'],
            'created_at' => $firstRow['created_at'],
            'updated_at' => $firstRow['updated_at'],
            'fuentes' => [],
        ];

        foreach ($rows as $row) {
            if ($row['fuente_id'] === null) {
                continue;
            }

            $nota['fuentes'][] = [
                'id' => (int) $row['fuente_id'],
                'url' => (string) $row['fuente_url'],
                'contenido_scrapeado' => $row['contenido_scrapeado'],
                'error' => $row['fuente_error'],
                'created_at' => $row['fuente_created_at'],
            ];
        }

        return $nota;
    }
}
