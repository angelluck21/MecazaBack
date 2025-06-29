# Configuración de SQL Server para Laravel

## Requisitos Previos

### 1. Instalar SQL Server
- Descargar e instalar SQL Server (Express, Developer, o Enterprise)
- Habilitar autenticación mixta (Windows + SQL Server)
- Crear un usuario SA con contraseña

### 2. Instalar Drivers de SQL Server para PHP
Para Windows, necesitas instalar los drivers de Microsoft SQL Server para PHP:

1. Descargar Microsoft Drivers for PHP for SQL Server desde:
   https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server

2. Instalar la versión correspondiente a tu versión de PHP

3. Habilitar las extensiones en php.ini:
   ```ini
   extension=php_sqlsrv.dll
   extension=php_pdo_sqlsrv.dll
   ```

## Configuración en Laravel

### 1. Crear archivo .env
Copia el contenido del archivo `env-sqlsrv-example.txt` a tu archivo `.env` y modifica:

```env
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=tu_base_de_datos
DB_USERNAME=sa
DB_PASSWORD=tu_contraseña
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=false
```

### 2. Crear la base de datos
En SQL Server Management Studio o Azure Data Studio:
```sql
CREATE DATABASE laravel;
```

### 3. Probar la conexión
Ejecuta el script de prueba:
```bash
php test-sqlsrv-connection.php
```

### 4. Ejecutar migraciones
```bash
php artisan migrate
```

## Configuración Adicional

### Para desarrollo local
Si estás usando SQL Server Express localmente:
- Host: `localhost` o `127.0.0.1`
- Puerto: `1433`
- Usuario: `sa`
- Contraseña: la que configuraste durante la instalación

### Para SQL Server en Azure
```env
DB_HOST=tu-servidor.database.windows.net
DB_PORT=1433
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario@tu-servidor
DB_PASSWORD=tu_contraseña
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=false
```

### Para SQL Server con autenticación de Windows
```env
DB_CONNECTION=sqlsrv
DB_HOST=localhost
DB_PORT=1433
DB_DATABASE=laravel
DB_USERNAME=
DB_PASSWORD=
DB_TRUSTED_CONNECTION=true
```

## Solución de Problemas

### Error: "SQLSTATE[HY000] [2002] Connection refused"
- Verificar que SQL Server esté ejecutándose
- Verificar que el puerto 1433 esté abierto
- Verificar la configuración del firewall

### Error: "SQLSTATE[HY000] [18456] Login failed"
- Verificar credenciales de usuario y contraseña
- Verificar que el usuario tenga permisos en la base de datos

### Error: "Driver not found"
- Verificar que las extensiones php_sqlsrv.dll y php_pdo_sqlsrv.dll estén habilitadas
- Reiniciar el servidor web después de instalar los drivers

### Error de certificado SSL
Si tienes problemas con certificados SSL, puedes deshabilitar la verificación:
```env
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=true
```

## Comandos Útiles

### Verificar configuración de base de datos
```bash
php artisan config:show database
```

### Limpiar caché de configuración
```bash
php artisan config:clear
php artisan cache:clear
```

### Verificar conexión desde Laravel
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

## Notas Importantes

1. **Caracteres especiales**: SQL Server puede tener problemas con caracteres especiales en contraseñas. Usa contraseñas simples para pruebas.

2. **Timezone**: SQL Server puede tener problemas con zonas horarias. Considera configurar:
   ```php
   'timezone' => 'UTC',
   ```

3. **Collation**: Para mejor compatibilidad con Laravel, usa:
   ```sql
   CREATE DATABASE laravel COLLATE SQL_Latin1_General_CP1_CI_AS;
   ```

4. **Backup**: Siempre haz backup de tu base de datos antes de ejecutar migraciones en producción. 

## Verificación de Drivers

Para verificar que los drivers están correctamente instalados y habilitados, puedes ejecutar el siguiente comando en tu terminal:
```bash
php -m | findstr sqlsrv
```

Si ves ambos, ¡ya está todo listo!

- Ahora puedes probar la conexión con:
  ```bash
  php test-connection.php
  ```

¿Quieres que verifique de nuevo si los drivers ya cargan correctamente? Si sí, reinicia Apache y dime cuando esté listo para que lo compruebe. 