<?php

echo "=== Prueba de conexión a SQL Server ===\n\n";

$serverName = 'ANGELLUCK34\\SQLEXPRESS01';
$databaseName = 'laravel';
$username = 'ANGELLUCK34';

echo "Servidor: $serverName\n";
echo "Base de datos: $databaseName\n";
echo "Usuario: $username (Autenticación de Windows)\n\n";

echo "¿Quieres usar autenticación de Windows (sin contraseña)? (s/n): ";
$handle = fopen("php://stdin", "r");
$useWindowsAuth = trim(strtolower(fgets($handle)));
fclose($handle);

if ($useWindowsAuth === 's') {
    // Autenticación de Windows
    $connectionString = "sqlsrv:Server=$serverName;Database=$databaseName;Trusted_Connection=yes";
    $username = '';
    $password = '';
    echo "Usando autenticación de Windows...\n";
} else {
    // Autenticación SQL Server
    echo "Por favor, ingresa la contraseña del usuario '$username': ";
    $handle = fopen("php://stdin", "r");
    $password = trim(fgets($handle));
    fclose($handle);
    $connectionString = "sqlsrv:Server=$serverName;Database=$databaseName";
}

echo "\nIntentando conectar a: $connectionString\n";

try {
    $pdo = new PDO($connectionString, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 60,
    ]);
    
    echo "✅ Conexión exitosa al servidor!\n";
    
    // Verificar versión
    $stmt = $pdo->query("SELECT @@VERSION as version");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Versión de SQL Server: " . substr($result['version'], 0, 100) . "...\n";
    
    // Listar bases de datos disponibles
    echo "\nBases de datos disponibles:\n";
    $stmt = $pdo->query("SELECT name FROM sys.databases WHERE database_id > 4 ORDER BY name");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($databases as $db) {
        echo "- $db\n";
    }
    
    // Verificar si la base de datos laravel existe
    if (in_array($databaseName, $databases)) {
        echo "\n✅ La base de datos '$databaseName' existe\n";
    } else {
        echo "\n⚠️  La base de datos '$databaseName' no existe\n";
        echo "¿Quieres crearla? (s/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 's') {
            $pdo->exec("CREATE DATABASE [$databaseName]");
            echo "✅ Base de datos '$databaseName' creada exitosamente\n";
        }
    }
    
    // Si todo está bien, actualizar el archivo .env
    echo "\n¿Quieres actualizar el archivo .env con esta configuración? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) === 's') {
        $envContent = file_get_contents('.env');
        
        if ($useWindowsAuth === 's') {
            // Configuración para autenticación de Windows
            $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME=", $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD=", $envContent);
            $envContent = preg_replace('/DB_TRUSTED_CONNECTION=.*/', "DB_TRUSTED_CONNECTION=true", $envContent);
        } else {
            // Configuración para autenticación SQL Server
            $envContent = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME=$username", $envContent);
            $envContent = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD=$password", $envContent);
            $envContent = preg_replace('/DB_TRUSTED_CONNECTION=.*/', "DB_TRUSTED_CONNECTION=false", $envContent);
        }
        
        file_put_contents('.env', $envContent);
        echo "✅ Archivo .env actualizado\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
    echo "Solución de problemas:\n";
    echo "1. Verifica que SQL Server esté ejecutándose\n";
    echo "2. Verifica que el usuario tenga permisos en SQL Server\n";
    echo "3. Verifica que tengas los drivers de SQL Server instalados\n";
    echo "4. Intenta conectar desde SQL Server Management Studio\n";
    echo "5. Verifica que el servicio SQL Server esté activo\n";
    
    // Verificar si los drivers están instalados
    echo "\nVerificando drivers de SQL Server:\n";
    if (extension_loaded('sqlsrv')) {
        echo "✅ Driver sqlsrv está instalado\n";
    } else {
        echo "❌ Driver sqlsrv NO está instalado\n";
    }
    
    if (extension_loaded('pdo_sqlsrv')) {
        echo "✅ Driver pdo_sqlsrv está instalado\n";
    } else {
        echo "❌ Driver pdo_sqlsrv NO está instalado\n";
    }
} 