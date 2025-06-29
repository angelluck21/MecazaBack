# Integración Frontend Mecaza con Backend Laravel

## Configuración del Backend

### URL Base de la API
```
http://localhost:8000/api
```

### Rutas Disponibles

#### 1. Crear Registro
- **URL:** `POST /api/registro/`
- **Descripción:** Crear un nuevo registro de usuario
- **Headers:**
  ```
  Content-Type: application/json
  Accept: application/json
  ```

**Ejemplo de Request:**
```json
{
  "Nombre": "Juan Pérez",
  "Correo": "juan@ejemplo.com",
  "Contraseña": "123456"
}
```

**Ejemplo de Response (Éxito):**
```json
{
  "success": true,
  "mensaje": "Registro exitoso",
  "data": {
    "id": 1,
    "nombre": "Juan Pérez",
    "correo": "juan@ejemplo.com"
  }
}
```

**Ejemplo de Response (Error):**
```json
{
  "success": false,
  "mensaje": "Error de validación",
  "errores": {
    "Correo": ["Este correo ya está registrado"]
  }
}
```

#### 2. Obtener Todos los Registros
- **URL:** `GET /api/registro/`
- **Descripción:** Obtener lista de todos los registros

**Ejemplo de Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "Nombre": "Juan Pérez",
      "Correo": "juan@ejemplo.com",
      "created_at": "2024-01-30T12:00:00.000000Z"
    }
  ]
}
```

#### 3. Obtener Registro Específico
- **URL:** `GET /api/registro/{id}`
- **Descripción:** Obtener un registro específico por ID

#### 4. Test de API
- **URL:** `GET /api/test`
- **Descripción:** Verificar que la API funciona

## Ejemplos de Código Frontend

### JavaScript (Fetch API)

#### Crear Registro
```javascript
async function crearRegistro(nombre, correo, contraseña) {
    try {
        const response = await fetch('http://localhost:8000/api/registro/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                Nombre: nombre,
                Correo: correo,
                Contraseña: contraseña
            })
        });

        const data = await response.json();
        
        if (data.success) {
            console.log('Registro exitoso:', data.mensaje);
            return data.data;
        } else {
            console.error('Error:', data.mensaje);
            if (data.errores) {
                console.error('Errores de validación:', data.errores);
            }
        }
    } catch (error) {
        console.error('Error de conexión:', error);
    }
}

// Uso
crearRegistro('Juan Pérez', 'juan@ejemplo.com', '123456');
```

#### Obtener Registros
```javascript
async function obtenerRegistros() {
    try {
        const response = await fetch('http://localhost:8000/api/registro/', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        
        if (data.success) {
            console.log('Registros:', data.data);
            return data.data;
        } else {
            console.error('Error:', data.mensaje);
        }
    } catch (error) {
        console.error('Error de conexión:', error);
    }
}
```

### React (Hooks)

#### Hook para Registro
```javascript
import { useState } from 'react';

const useRegistro = () => {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const crearRegistro = async (nombre, correo, contraseña) => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('http://localhost:8000/api/registro/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    Nombre: nombre,
                    Correo: correo,
                    Contraseña: contraseña
                })
            });

            const data = await response.json();
            
            if (data.success) {
                setLoading(false);
                return { success: true, data: data.data };
            } else {
                setError(data.mensaje);
                setLoading(false);
                return { success: false, error: data };
            }
        } catch (error) {
            setError('Error de conexión');
            setLoading(false);
            return { success: false, error: error.message };
        }
    };

    return { crearRegistro, loading, error };
};

export default useRegistro;
```

#### Componente de Registro
```javascript
import React, { useState } from 'react';
import useRegistro from './useRegistro';

const RegistroForm = () => {
    const [formData, setFormData] = useState({
        nombre: '',
        correo: '',
        contraseña: ''
    });
    
    const { crearRegistro, loading, error } = useRegistro();

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        const result = await crearRegistro(
            formData.nombre,
            formData.correo,
            formData.contraseña
        );

        if (result.success) {
            alert('Registro exitoso!');
            setFormData({ nombre: '', correo: '', contraseña: '' });
        }
    };

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    return (
        <form onSubmit={handleSubmit}>
            {error && <div className="error">{error}</div>}
            
            <div>
                <label>Nombre:</label>
                <input
                    type="text"
                    name="nombre"
                    value={formData.nombre}
                    onChange={handleChange}
                    required
                />
            </div>
            
            <div>
                <label>Correo:</label>
                <input
                    type="email"
                    name="correo"
                    value={formData.correo}
                    onChange={handleChange}
                    required
                />
            </div>
            
            <div>
                <label>Contraseña:</label>
                <input
                    type="password"
                    name="contraseña"
                    value={formData.contraseña}
                    onChange={handleChange}
                    required
                />
            </div>
            
            <button type="submit" disabled={loading}>
                {loading ? 'Registrando...' : 'Registrar'}
            </button>
        </form>
    );
};

export default RegistroForm;
```

### Axios

#### Configuración
```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    }
});

export default api;
```

#### Uso con Axios
```javascript
import api from './api';

// Crear registro
const crearRegistro = async (datos) => {
    try {
        const response = await api.post('/registro/', datos);
        return response.data;
    } catch (error) {
        throw error.response?.data || error.message;
    }
};

// Obtener registros
const obtenerRegistros = async () => {
    try {
        const response = await api.get('/registro/');
        return response.data;
    } catch (error) {
        throw error.response?.data || error.message;
    }
};
```

## Configuración CORS

El backend ya está configurado para aceptar peticiones desde cualquier origen. Si necesitas restringir los orígenes, modifica el archivo `config/cors.php`.

## Iniciar el Servidor

```bash
# En el directorio del backend Laravel
php artisan serve
```

El servidor estará disponible en: `http://localhost:8000`

## Pruebas

### 1. Probar la API
```bash
curl -X GET http://localhost:8000/api/test
```

### 2. Probar el registro
```bash
curl -X POST http://localhost:8000/api/registro/ \
  -H "Content-Type: application/json" \
  -d '{
    "Nombre": "Test User",
    "Correo": "test@ejemplo.com",
    "Contraseña": "123456"
  }'
```

## Notas Importantes

1. **Contraseñas:** Las contraseñas se encriptan automáticamente en el backend
2. **Validación:** El backend valida todos los campos antes de guardar
3. **Errores:** Todos los errores se devuelven en formato JSON
4. **CORS:** Configurado para permitir peticiones desde cualquier origen
5. **Seguridad:** Las contraseñas no se devuelven en las respuestas 