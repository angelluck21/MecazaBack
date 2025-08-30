# 🚗 Mecaza - Sistema de Gestión de Viajes

Sistema backend desarrollado en Laravel para la gestión de viajes, carros, reservas y usuarios.

## 📋 Descripción

Mecaza es una API REST que permite gestionar un sistema de transporte con las siguientes funcionalidades principales:

- **Gestión de Usuarios**: Registro, login, listado y administración de usuarios
- **Gestión de Carros**: Administración de vehículos y sus estados
- **Sistema de Reservas**: Creación y gestión de reservas de viajes
- **Precios de Viajes**: Configuración y administración de tarifas
- **Estados de Carros**: Control del estado operativo de los vehículos

## 🛠️ Tecnologías

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Base de Datos**: SQL Server / MySQL / SQLite
- **Autenticación**: Laravel Sanctum
- **Generación de PDFs**: DomPDF
- **HTTP Client**: Guzzle

## 📁 Estructura del Proyecto

```
app/
├── Http/Controllers/     # Controladores de la API
├── Mail/                # Clases de correo electrónico
├── Models/              # Modelos Eloquent
└── Providers/           # Proveedores de servicios

database/
├── migrations/          # Migraciones de base de datos
└── seeders/            # Datos de prueba

routes/
└── api.php             # Rutas de la API
```

## 🚀 Instalación

### Requisitos Previos
- PHP 8.2 o superior
- Composer
- Base de datos (SQL Server, MySQL o SQLite)
- XAMPP (recomendado para desarrollo local)

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd MecazaBack-2
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar variables de entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar base de datos**
   - Editar `.env` con las credenciales de tu base de datos
   - Para SQL Server, usar la configuración en `config/database.php`

5. **Ejecutar migraciones**
   ```bash
   php artisan migrate
   ```

6. **Iniciar el servidor**
   ```bash
   php artisan serve
   ```

## 🔌 Endpoints de la API

### Autenticación
- `POST /api/registro` - Registro de usuarios
- `POST /api/login` - Login de usuarios
- `GET /api/user` - Información del usuario autenticado

### Usuarios
- `GET /api/listarusuario` - Listar usuarios
- `GET /api/listarusuariotodo` - Listar todos los usuarios
- `PUT /api/actualizarusuario/{user}` - Actualizar usuario
- `DELETE /api/eliminarusuario/{user}` - Eliminar usuario

### Carros
- `POST /api/agregarcarros` - Agregar carro
- `GET /api/listarcarro` - Listar carros
- `PUT /api/actualizarcarro/{carro}` - Actualizar carro
- `DELETE /api/eliminarcarro/{carro}` - Eliminar carro
- `PUT /api/actualizarestadocarro/{carro}` - Actualizar estado del carro

### Estados de Carros
- `POST /api/agregarestados` - Agregar estado
- `GET /api/listarestados` - Listar estados
- `PUT /api/actualizarestados/{estado}` - Actualizar estado
- `DELETE /api/eliminarestados/{estado}` - Eliminar estado

### Precios de Viajes
- `POST /api/agregarprecio` - Agregar precio
- `GET /api/listarprecio` - Listar precios
- `PUT /api/actualizarprecio/{precio}` - Actualizar precio
- `DELETE /api/eliminarprecio/{precio}` - Eliminar precio

### Reservas
- `POST /api/agregarreserva` - Crear reserva
- `GET /api/listarreserva` - Listar reservas
- `PUT /api/actualizarreserva/{reserva}` - Actualizar reserva
- `DELETE /api/eliminarreserva/{reserva}` - Eliminar reserva

### Prueba
- `GET /api/test` - Verificar funcionamiento de la API

## 🗄️ Base de Datos

El proyecto soporta múltiples motores de base de datos:

- **SQL Server** (configurado por defecto)
- **MySQL/MariaDB**
- **SQLite**

### Configuración de SQL Server
```php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'host' => env('DB_HOST', 'ANGELLUCK34\\SQLEXPRESS01'),
    'port' => env('DB_PORT', '1433'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'ANGELLUCK34'),
    'password' => env('DB_PASSWORD', '11'),
    'charset' => env('DB_CHARSET', 'utf8'),
    'encrypt' => env('DB_ENCRYPT', 'yes'),
    'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
]
```

## 📧 Sistema de Correos

El proyecto incluye funcionalidades de envío de correos electrónicos:
- Confirmación de reservas
- Notificaciones a conductores

## 🔒 Autenticación

- **Laravel Sanctum** para autenticación API
- Tokens de acceso para endpoints protegidos
- Middleware de autenticación en rutas sensibles

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Ejecutar tests con coverage
php artisan test --coverage
```

## 📦 Comandos Útiles

```bash
# Desarrollo
composer run dev

# Limpiar cache
php artisan config:clear
php artisan cache:clear

# Ver logs en tiempo real
php artisan pail
```

## 🐳 Docker (Opcional)

El proyecto incluye configuración Docker para despliegue:

```bash
docker-compose up -d
```

## 📝 Notas de Desarrollo

- **XAMPP**: Recomendado para entorno de desarrollo local
- **Base de datos**: Configurar según tu entorno (SQL Server por defecto)
- **Variables de entorno**: Asegurarse de configurar correctamente `.env`

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Para soporte técnico o preguntas sobre el proyecto, contacta al equipo de desarrollo.

---

**Mecaza** - Sistema de Gestión de Viajes 🚗✨
