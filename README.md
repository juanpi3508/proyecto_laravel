# 🛒 KoKo Market

Sistema de e-commerce desarrollado en Laravel para supermercado ecuatoriano.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-4169E1?style=flat-square&logo=postgresql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap)

---

## 📋 Requisitos

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x y npm
- **PostgreSQL** >= 15.x
- **Extensiones PHP**: pdo_pgsql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath

---

## ⚡ Instalación Rápida

```bash
# 1. Clonar repositorio
git clone https://github.com/tu-usuario/kokomarket.git
cd kokomarket

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm install

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Compilar assets
npm run build
```

---

## 🔧 Configuración

### Variables de Entorno (.env)

```env
APP_NAME="KoKo Market"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Base de Datos PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kokomarket
DB_USERNAME=usuario
DB_PASSWORD=tu_password

# Sesión
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Base de Datos

El sistema se conecta a una base de datos **PostgreSQL existente**. La estructura de tablas incluye:

| Tabla | Descripción |
|-------|-------------|
| `productos` | Catálogo de productos |
| `categorias` | Categorías de productos |
| `clientes` | Datos de clientes |
| `usuarios` | Usuarios del sistema |
| `facturas` | Facturas generadas |
| `proxfac` | Detalle de facturas |
| `ciudades` | Catálogo de ciudades |

#### Funciones PostgreSQL Requeridas

```sql
-- Generar código de factura
CREATE OR REPLACE FUNCTION GenerarCodigoFactura() 
RETURNS VARCHAR AS $$ ... $$;

-- Aprobar factura
CREATE OR REPLACE FUNCTION fn_aprobar_factura_json(p_id_factura VARCHAR) 
RETURNS JSON AS $$ ... $$;
```

---

## 🚀 Ejecución

### Desarrollo Local

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Vite (hot reload de assets)
npm run dev
```

Acceder a: `http://localhost:8000`

### Producción

```bash
# Optimizar
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compilar assets
npm run build

# Servir con Nginx/Apache o usar:
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🌐 Despliegue con Cloudflare Tunnel

### Instalar cloudflared

```bash
# Debian/Ubuntu
curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -o cloudflared
chmod +x cloudflared && sudo mv cloudflared /usr/local/bin/
```

### Configurar Tunnel

```bash
# Autenticar
cloudflared tunnel login

# Crear tunnel
cloudflared tunnel create kokomarket

# Configurar DNS
cloudflared tunnel route dns kokomarket tudominio.com
```

### Archivo de Configuración

`~/.cloudflared/config.yml`:
```yaml
tunnel: <TUNNEL_ID>
credentials-file: ~/.cloudflared/<TUNNEL_ID>.json

ingress:
  - hostname: tudominio.com
    service: http://localhost:8000
  - service: http_status:404
```

### Ejecutar

```bash
# Como servicio
cloudflared service install

# O manualmente
cloudflared tunnel run kokomarket
```

---

## 📁 Estructura Principal

```
kokomarket/
├── app/
│   ├── Constants/       # Constantes de columnas BD
│   ├── Http/
│   │   ├── Controllers/ # Controladores
│   │   └── Requests/    # Validación de formularios
│   ├── Models/          # Modelos Eloquent
│   └── Services/        # Lógica de negocio
├── config/              # Configuración
├── public/              # Archivos públicos
├── resources/views/     # Vistas Blade
└── routes/web.php       # Rutas
```

---

## 🔐 Características de Seguridad

- Contraseñas encriptadas con **bcrypt**
- Protección **CSRF** en formularios
- IDs de productos encriptados en URLs
- Bloqueo de usuario después de 5 intentos fallidos
- Validación de cédula/RUC ecuatoriano

---

## 📖 Documentación

Para documentación técnica detallada del sistema, ver:

📄 [DOCUMENTACION_SISTEMA.md](./DOCUMENTACION_SISTEMA.md)

---

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Con cobertura
php artisan test --coverage
```

---

## 📝 Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Ver rutas
php artisan route:list

# Verificar conexión BD
php artisan db:show
```


