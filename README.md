# APIs AEVO

Sistema de APIs Laravel con autenticación MFA/2FA y passkeys para aplicaciones móvil y web.

## Stack Tecnológico

### Backend
- Laravel 11 + Sanctum
- Fortify (2FA/MFA) + WebAuthn (passkeys)
- Pest + PHPStan + Pint

### Frontend Demo
- Nuxt 3 + Tailwind CSS
- Dashboard básico para testing

### Testing & Documentación
- Apidog para testing APIs
- Swagger/OpenAPI integrado
- Pest para testing automatizado

## Estructura del Proyecto

```
apis-aevo/
├── backend/          # Laravel APIs
├── frontend/         # Nuxt dashboard demo
├── docs/            # Documentación
├── docker-compose.yml
└── README.md
```

## Instalación

### Requisitos
- PHP 8.2+
- Node.js 18+
- Composer
- Docker (opcional)

### Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Frontend (Nuxt)
```bash
cd frontend
npm install
npm run dev
```

## Características

- ✅ Autenticación con MFA/2FA
- ✅ Passkeys (Google/Apple)
- ✅ API Resources consistentes
- ✅ Rate limiting por endpoint
- ✅ Documentación automática
- ✅ Testing robusto
- ✅ Código con mejores prácticas

## Desarrollo

### Testing
```bash
# Backend
cd backend && php artisan test

# Frontend
cd frontend && npm run test
```

### Calidad de Código
```bash
# Análisis estático
./vendor/bin/phpstan analyse

# Formateo
./vendor/bin/pint
```

## Documentación

- API: `/api/documentation`
- Apidog: [Colección de APIs]
- Proyecto: `proyecto-resumen.md`
- Mejores prácticas: `consideraciones.md`