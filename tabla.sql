-- --------------------------------------------------------
-- Creación de la base de datos (si no existe)
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS spherework;
USE spherework;

-- --------------------------------------------------------
-- Estructura de la tabla `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rol` enum('administrador','cliente') NOT NULL DEFAULT 'cliente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `password_reset_tokens`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `sessions`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `editoriales`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `editoriales`;
CREATE TABLE `editoriales` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `pais` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `autores`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `autores`;
CREATE TABLE `autores` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `pais` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `libros`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `libros`;
CREATE TABLE `libros` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `isbn` varchar(255) NOT NULL,
  `anio_publicacion` int NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `autor_id` bigint UNSIGNED NOT NULL,
  `editorial_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `libros_autor_id_foreign` (`autor_id`),
  KEY `libros_editorial_id_foreign` (`editorial_id`),
  CONSTRAINT `libros_autor_id_foreign` FOREIGN KEY (`autor_id`) REFERENCES `autores` (`id`),
  CONSTRAINT `libros_editorial_id_foreign` FOREIGN KEY (`editorial_id`) REFERENCES `editoriales` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `clientes`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `empleados`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `empleados`;
CREATE TABLE `empleados` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','gestor') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `pedidos`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE `pedidos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT NULL,
  `fecha_pedido` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_cliente_id_foreign` (`cliente_id`),
  CONSTRAINT `pedidos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `detallespedidos`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `detallespedidos`;
CREATE TABLE `detallespedidos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cantidad` int NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `pedido_id` bigint UNSIGNED NOT NULL,
  `libro_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detallespedidos_pedido_id_foreign` (`pedido_id`),
  KEY `detallespedidos_libro_id_foreign` (`libro_id`),
  CONSTRAINT `detallespedidos_libro_id_foreign` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`),
  CONSTRAINT `detallespedidos_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de la tabla `comentarios`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `comentarios`;
CREATE TABLE `comentarios` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `comentario` text NOT NULL,
  `puntuacion` int DEFAULT NULL,
  `libro_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comentarios_libro_id_foreign` (`libro_id`),
  KEY `comentarios_user_id_foreign` (`user_id`),
  CONSTRAINT `comentarios_libro_id_foreign` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comentarios_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Inserción de datos en la tabla `users`
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `rol`) VALUES
(1, 'Administrador', 'admin@spherework.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'abcdefghijklmn', '2023-10-26 12:00:00', '2023-10-26 12:00:00', 'administrador'),
(2, 'Cliente Uno', 'cliente@spherework.com', NULL, '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'opqrstuvwxyz', '2023-10-26 12:00:00', '2023-10-26 12:00:00', 'cliente');

-- --------------------------------------------------------
-- Inserción de datos en la tabla `editoriales`
-- --------------------------------------------------------

INSERT INTO `editoriales` (`id`, `nombre`, `pais`, `created_at`, `updated_at`) VALUES
(1, 'Editorial Planeta', 'España', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(2, 'Penguin Random House', 'Internacional', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(3, 'Anagrama', 'España', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(4, 'Paginas de espuma', 'España', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(5, 'Debolsillo', 'España', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(6, 'Alba', 'España', '2023-10-26 12:00:00', '2023-10-26 12:00:00');

-- --------------------------------------------------------
-- Inserción de datos en la tabla `autores`
-- --------------------------------------------------------

INSERT INTO `autores` (`id`, `nombre`, `pais`, `created_at`, `updated_at`) VALUES
(1, 'Gabriel García Márquez', 'Colombia', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(2, 'Isabel Allende', 'Chile', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(3, 'Haruki Murakami', 'Japón', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(4, 'Jane Austen', 'Reino Unido', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(5, 'Sara Mesa', 'Espana', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(6, 'Cormac McCarthy', 'Estados Unidos', '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(7, 'Alejandra Kamiya', 'Argentina', '2023-10-26 12:00:00', '2023-10-26 12:00:00');

-- --------------------------------------------------------
-- Inserción de datos en la tabla `libros`
-- --------------------------------------------------------

INSERT INTO `libros` (`id`, `titulo`, `isbn`, `anio_publicacion`, `precio`, `autor_id`, `editorial_id`, `created_at`, `updated_at`) VALUES
(1, 'Cien años de soledad', '978-8437604947', 1967, '19.95', 1, 2, '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(2, 'La casa de los espíritus', '978-8401341910', 1982, '18.50', 2, 1, '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(3, 'Tokio Blues (Norwegian Wood)', '978-8483835043', 1987, '21.00', 3, 3, '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(4, 'Mansfield Park', '978-8490650295', 1814, '19.00', 4, 6, '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(5, 'Oposición', '978-8433929686', 2025, '24.00', 5, 3, '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(6, 'La carretera', '978-8483468685', 2007, '12.00', 6, 5, '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(7, 'La paciencia del agua sobre cada piedra', '978-8412664720', 2022, '19.00', 7, 4, '2023-10-26 12:00:00', '2023-10-26 12:00:00'),
(8, 'Cronica de una muerte anunciada', '978-84975924