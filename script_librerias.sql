-- Creación de la base de datos
CREATE DATABASE SphereWork;
USE SphereWork;

-- Tabla de Editoriales
CREATE TABLE Editoriales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    pais VARCHAR(100) NOT NULL
);

-- Tabla de Autores
CREATE TABLE Autores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    pais VARCHAR(100) NOT NULL
);

-- Tabla de Libros
CREATE TABLE Libros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    anio_publicacion INT NOT NULL,
    editorial_id INT,
    autor_id INT,
    FOREIGN KEY (editorial_id) REFERENCES Editoriales(id),
    FOREIGN KEY (autor_id) REFERENCES Autores(id)
);

-- Tabla de Clientes
CREATE TABLE Clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Tabla de Empleados
CREATE TABLE Empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'gestor') NOT NULL
);

-- Tabla de Pedidos
CREATE TABLE Pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'enviado', 'entregado') NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES Clientes(id)
);

-- Tabla de Detalles de Pedido
CREATE TABLE DetallesPedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    libro_id INT,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES Pedidos(id),
    FOREIGN KEY (libro_id) REFERENCES Libros(id)
);

-- Tabla de Comentarios y Puntuaciones
CREATE TABLE Comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    libro_id INT,
    comentario TEXT,
    puntuacion INT CHECK (puntuacion BETWEEN 1 AND 5),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES Clientes(id),
    FOREIGN KEY (libro_id) REFERENCES Libros(id)
);
