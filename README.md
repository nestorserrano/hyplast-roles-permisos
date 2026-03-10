# Hyplast Roles y Permisos - Sistema de Autorización

## Descripción
Sistema completo de roles, permisos y autorización para controlar el acceso a funcionalidades del sistema.

## Características Principales
- 👥 Gestión de roles
- 🔐 Permisos granulares
- 🎭 Asignación dinámica
- 🔍 Auditoría de permisos
- 📊 Dashboard de roles
- ⚡ Cache de permisos
- 🌐 Multi-empresa

## Modelos Principales
- **Role**: Roles del sistema
- **Permission**: Permisos individuales
- **User**: Usuarios (relación)

## API Endpoints
```
GET    /api/roles                  # Listar roles
POST   /api/roles                  # Crear rol
GET    /api/roles/{id}/permissions # Ver permisos de rol
PUT    /api/roles/{id}/permissions # Actualizar permisos
GET    /api/permissions            # Listar permisos
```

## Permisos del Sistema
Organizados por módulos:
- CRM (leads, clientes, conversaciones)
- Proyectos (tareas, áreas)
- Aprobaciones (solicitudes, requisiciones)
- Máquinas (operación, mantenimiento)
- Inventario (consultas, movimientos)
- Configuración (usuarios, sistema)

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x

## Instalación
```bash
composer install
php artisan migrate
php artisan db:seed --class=RolesPermissionsSeeder
```

## Licencia
Propietario - Hyplast © 2026
