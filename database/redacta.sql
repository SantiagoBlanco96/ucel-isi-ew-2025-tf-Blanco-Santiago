-- =============================================================================
-- Redacta — Script de inicialización de base de datos
-- UCEL - ISI - Entornos Web - Trabajo final: Blanco, Santiago
-- =============================================================================
-- Este script puede ejecutarse múltiples veces sin errores.
-- Las tablas se eliminan en orden inverso al de creación para respetar las FK.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS redacta
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE redacta;

-- -----------------------------------------------------------------------------
-- Eliminación de tablas en orden inverso (respeta restricciones de FK)
-- -----------------------------------------------------------------------------

DROP TABLE IF EXISTS fuentes;
DROP TABLE IF EXISTS notas;
DROP TABLE IF EXISTS usuarios;

-- -----------------------------------------------------------------------------
-- Tabla: usuarios
-- Almacena los usuarios del sistema (periodistas y editores)
-- -----------------------------------------------------------------------------

CREATE TABLE usuarios (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(100)     NOT NULL,
    email         VARCHAR(150)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    rol           ENUM('periodista', 'editor') NOT NULL DEFAULT 'periodista',
    activo        TINYINT(1)       NOT NULL DEFAULT 1,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabla: notas
-- Almacena las notas periodísticas generadas por los usuarios
-- -----------------------------------------------------------------------------

CREATE TABLE notas (
    id                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    usuario_id           INT UNSIGNED  NOT NULL,
    titulo               VARCHAR(255)  NOT NULL,
    seccion              ENUM('politica', 'economia', 'deportes', 'cultura', 'tecnologia', 'sociedad') NOT NULL,
    extension            ENUM('corta', 'media', 'larga') NOT NULL,
    palabras_clave       VARCHAR(500)  NULL,
    instrucciones_extra  TEXT          NULL,
    contenido_generado   LONGTEXT      NULL,
    estado               ENUM('borrador', 'publicado') NOT NULL DEFAULT 'borrador',
    created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_notas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Tabla: fuentes
-- Almacena las URLs fuente y el contenido scrapeado asociado a cada nota
-- -----------------------------------------------------------------------------

CREATE TABLE fuentes (
    id                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nota_id              INT UNSIGNED  NOT NULL,
    url                  VARCHAR(2048) NOT NULL,
    contenido_scrapeado  LONGTEXT      NULL,
    error                VARCHAR(500)  NULL,
    created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_fuentes_nota
        FOREIGN KEY (nota_id) REFERENCES notas (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Datos iniciales: usuario administrador
-- Contraseña: redacta2025 (hash bcrypt generado con PASSWORD_BCRYPT)
-- -----------------------------------------------------------------------------

INSERT INTO usuarios (nombre, email, password_hash, rol, activo)
VALUES (
    'Admin',
    'admin@redacta.com',
    '$2y$10$bCNBhg085rqtqS.Z8EqlMuE6/cwzeKVFUXlRTAp3SmbFCzF.oP3jW',
    'editor',
    1
);

