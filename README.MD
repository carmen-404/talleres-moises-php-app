<p align="center">
  <img src="img/logo/logo.webp" alt="Logo de Talleres Moises" width="420">
</p>

<h1 align="center">Talleres Moises · Aplicación PHP</h1>

<p align="center">
  <em>Mecánica · Reparación | Profesionalidad y confianza</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white" alt="HTML5">
  <img src="https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/Bootstrap_5-7952B3?logo=bootstrap&logoColor=white" alt="Bootstrap 5">
  <img src="https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white" alt="MySQL">
</p>

---

## Sobre el proyecto

Proyecto de la asignatura **Desarrollo de Interfaces (2º DAM)**, segunda parte.
Temática: **Taller Mecánico de Vehículos**.

Aplicación web (SPA) para controlar el flujo de reparaciones de los clientes del taller: gestión de clientes, coches, reservas y servicios, con PHP y MySQL en el servidor y JavaScript (AJAX) en el cliente.

La primera parte (web corporativa estática) está en el repositorio [Talleres-Moises](https://github.com/L3V1XX/Talleres-Moises).

## Enlaces

- 💻 **Repositorio GitHub:** [talleres-moises-php-app](https://github.com/carmen-404/talleres-moises-php-app)
- 🌐 **Alojamiento web:** <!-- TODO: pegar aquí la URL del hosting --> https://PENDIENTE

## Base de datos

| Dato | Valor |
|------|-------|
| Motor | MySQL 8.0.16+ / MariaDB 10.2.1+ |
| Nombre de la base de datos | `talleres_moises` |
| Usuario | `PENDIENTE` |
| Password | `PENDIENTE` |
| Host | `localhost` |

> Los scripts SQL están en la carpeta `sql/`:
> - `backup.sql`: crea la base de datos `talleres_moises`, las tablas, relaciones y datos.
> - `backup_sin_create_database.sql`: igual, pero sin `CREATE DATABASE` (para hostings donde la base de datos ya viene creada).

### Entidades (modelo relacional)

`CODIGO_POSTAL` → `CLIENTE` → `COCHE` → `RESERVA` → `SERVICIO`

Los nombres de tabla están en MAYÚSCULAS (en Linux, MySQL distingue mayúsculas y minúsculas).

## Reparto de responsabilidades

| Alumno | Entidad responsable |
|--------|---------------------|
| Carmen | `Reserva` |
| Moises | `Servicios` |
| Jose Manuel | `Coche` |
| Javier | `Cliente` |

## Tecnologías

- HTML5, CSS3 y JavaScript (AJAX)
- [Bootstrap 5.3.3](https://getbootstrap.com/) y [Bootstrap Icons](https://icons.getbootstrap.com/) 1.11.3
- PHP y MySQL
- Tipografías locales en WOFF: Oswald (títulos), Roboto (cuerpo), Montserrat (navegación y botones)

## Estructura del proyecto

```
talleres-moises-php-app/
├── sql/
│   ├── backup.sql
│   └── backup_sin_create_database.sql
├── cabecera.html
└── README.MD
```



## Requisitos

- PHP 8.0 o superior
- MySQL 8.0.16+ o MariaDB 10.2.1+
- Servidor web Apache
