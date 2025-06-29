<?php

// Script para configurar automáticamente la conexión a SQL Server
// Ejecutar con: php setup-sqlsrv.php

echo "=== Configuración de SQL Server para Laravel ===\n\n";

// Configuración específica para tu servidor
$serverName = 'ANGELLUCK34\\SQLEXPRESS01';
$databaseName = 'laravel';
$username = 'ANGELLUCK34';
$password = '1'; // Cambiar por tu contraseña

echo "Servidor: $serverName\n";
echo "Base de datos: $databaseName\n";
echo "Usuario: $username\n\n";

// Crear contenido del archivo .env
$envContent = "APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:tu_clave_generada_aqui
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Configuración de SQL Server
DB_CONNECTION=sqlsrv
DB_HOST=$serverName
DB_PORT=1433
DB_DATABASE=$databaseName
DB_USERNAME=$username
DB_PASSWORD=$password
DB_ENCRYPT=yes
DB_TRUST_SERVER_CERTIFICATE=false

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=\"hello@example.com\"
MAIL_FROM_NAME=\"\${APP_NAME}\"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME=\"\${APP_NAME}\"
VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"
VITE_PUSHER_HOST=\"\${PUSHER_HOST}\"
VITE_PUSHER_PORT=\"\${PUSHER_PORT}\"
VITE_PUSHER_SCHEME=\"\${PUSHER_SCHEME}\"
VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"";

// Escribir el archivo .env
if (file_put_contents('.env', $envContent)) {
    echo "✅ Archivo .env creado exitosamente\n";
} else {
    echo "❌ Error al crear el archivo .env\n";
    exit(1);
}

// Probar la conexión
echo "\n=== Probando conexión a SQL Server ===\n";

try {
    $connectionString = "sqlsrv:Server=$serverName;Database=$databaseName";
    
    echo "Intentando conectar a: $connectionString\n";
    
    $pdo = new PDO($connectionString, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 60,
    ]);
    
    echo "✅ Conexión exitosa!\n";
    
    // Verificar versión
    $stmt = $pdo->query("SELECT @@VERSION as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Versión: " . substr($result['version'], 0, 100) . "...\n";
    
    // Verificar si la base de datos existe
    $stmt = $pdo->query("SELECT name FROM sys.databases WHERE name = '$databaseName'");
    $dbExists = $stmt->fetch();
    
    if ($dbExists) {
        echo "✅ La base de datos '$databaseName' existe\n";
    } else {
        echo "⚠️  La base de datos '$databaseName' no existe\n";
        echo "Creando base de datos...\n";
        
        // Conectar a master para crear la base de datos
        $masterConnection = "sqlsrv:Server=$serverName;Database=master";
        $pdoMaster = new PDO($masterConnection, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $pdoMaster->exec("CREATE DATABASE [$databaseName]");
        echo "✅ Base de datos '$databaseName' creada exitosamente\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
    echo "Solución de problemas:\n";
    echo "1. Verifica que SQL Server esté ejecutándose\n";
    echo "2. Verifica que el usuario 'sa' tenga la contraseña correcta\n";
    echo "3. Verifica que tengas los drivers de SQL Server instalados\n";
    echo "4. Intenta conectar desde SQL Server Management Studio\n";
    exit(1);
}

echo "\n=== Configuración completada ===\n";
echo "Ahora puedes ejecutar:\n";
echo "php artisan migrate\n";
echo "php artisan serve\n"; 