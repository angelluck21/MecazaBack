# Configuración de Mailtrap para Mecaza

## ¿Qué es Mailtrap?
Mailtrap es un servicio que captura emails de desarrollo y testing para que no se envíen a destinatarios reales. Es perfecto para probar el sistema de emails durante el desarrollo.

## Pasos para configurar Mailtrap:

### 1. Crear cuenta en Mailtrap
- Ve a [mailtrap.io](https://mailtrap.io)
- Crea una cuenta gratuita
- Inicia sesión

### 2. Crear un Inbox
- En el dashboard, crea un nuevo "Inbox"
- Dale un nombre como "Mecaza Development"

### 3. Obtener credenciales SMTP
- En tu inbox, ve a "Settings" → "SMTP Settings"
- Selecciona "Laravel" como framework
- Copia las credenciales que aparecen

### 4. Configurar el archivo .env
Crea o edita tu archivo `.env` en la raíz del proyecto con estas configuraciones:

```env
# Configuración de Mailtrap
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_username_de_mailtrap
MAIL_PASSWORD=tu_password_de_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@mecaza.com"
MAIL_FROM_NAME="Mecaza"
```

**Reemplaza:**
- `tu_username_de_mailtrap` con tu username real de Mailtrap
- `tu_password_de_mailtrap` con tu password real de Mailtrap

### 5. Generar APP_KEY (si no existe)
Si tu aplicación no tiene una APP_KEY, ejecuta:
```bash
php artisan key:generate
```

### 6. Probar la configuración
Puedes probar si los emails funcionan creando una reserva en tu aplicación. Los emails aparecerán en tu inbox de Mailtrap.

## Estructura de Emails Implementada

### Email al Conductor
- **Archivo:** `app/Mail/NuevaReservaConductor.php`
- **Vista:** `resources/views/emails/nueva-reserva-conductor.blade.php`
- **Contenido:** Notifica al conductor sobre una nueva reserva

### Email al Pasajero
- **Archivo:** `app/Mail/ConfirmacionReservaPasajero.php`
- **Vista:** `resources/views/emails/confirmacion-reserva-pasajero.blade.php`
- **Contenido:** Confirma la reserva al pasajero

## Campos del Modelo Carros Necesarios
Asegúrate de que tu modelo `Carros` tenga estos campos:
- `conductor` - Nombre del conductor
- `email` - Email del conductor
- `telefono` - Teléfono del conductor
- `placa` - Placa del vehículo
- `destino` - Destino del viaje
- `fecha` - Fecha del viaje
- `horasalida` - Hora de salida

## Campos del Usuario Necesarios
Asegúrate de que tu modelo `User` tenga:
- `email` - Email del usuario/pasajero

## Solución de Problemas

### Error: "Class 'Mail' not found"
- Verifica que tienes `use Illuminate\Support\Facades\Mail;` en tu controlador

### Error: "View not found"
- Verifica que las vistas están en `resources/views/emails/`

### Emails no se envían
- Verifica las credenciales de Mailtrap en `.env`
- Verifica que `APP_KEY` esté configurado
- Revisa los logs en `storage/logs/laravel.log`

### Emails aparecen en Mailtrap pero no en producción
- Cambia la configuración de `MAIL_HOST` a tu servidor SMTP real
- Actualiza `MAIL_USERNAME` y `MAIL_PASSWORD` con credenciales reales

## Para Producción
Cuando estés listo para producción, cambia la configuración de email a un servicio real como:
- Gmail SMTP
- SendGrid
- Amazon SES
- Mailgun

## Comandos Útiles
```bash
# Limpiar cache de configuración
php artisan config:clear

# Limpiar cache de vistas
php artisan view:clear

# Ver configuración actual
php artisan config:show
```
