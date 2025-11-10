# Documentación APIs AEVO

## Índice

- [Arquitectura](./arquitectura.md)
- [Autenticación](./autenticacion.md)
- [APIs Reference](./api-reference.md)
- [Testing](./testing.md)
- [Deployment](./deployment.md)

## Enlaces Rápidos

- **API Docs**: http://localhost:8000/api/documentation
- **Frontend Demo**: http://localhost:3000
- **Apidog Collection**: [Pendiente configurar]

## Flujo de Desarrollo

1. **Backend**: Laravel APIs con Sanctum
2. **Testing**: Apidog + Pest
3. **Frontend**: Nuxt demo para pruebas
4. **Deploy**: Docker containers

## Convenciones

### Commits
- `feat:` Nueva funcionalidad
- `fix:` Corrección de bugs
- `docs:` Documentación
- `test:` Tests
- `refactor:` Refactoring

### APIs
- Usar API Resources
- HTTP status codes correctos
- Rate limiting por endpoint
- Documentación automática