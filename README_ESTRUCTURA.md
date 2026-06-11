# 📋 ESTRUCTURA PROYECTO - TALLER PHP 2026

## 🎯 OVERVIEW RÁPIDO

**Sistema**: Reservas de servicios profesionales (freelancers)  
**Tech Stack**: Laravel 13.8 + Angular 21 + PostgreSQL  
**Líneas de código**: ~20,000  
**Estado**: 75% completado  
**Tiempo trabajo restante**: 50-60 horas hasta producción  

---

## 📁 ESTRUCTURA CARPETAS

```
taller-php-2026-backend/
├── app/
│   ├── Console/
│   │   └── Commands/              [Comandos Artisan - FALTA: Kernel.php]
│   ├── Events/
│   │   ├── ReservaActualizada.php [Broadcasting evento]
│   │   ├── PagoActualizado.php
│   │   ├── ReservaCreada.php
│   │   └── ... (5 más)
│   ├── Exceptions/
│   │   └── ApiException.php       [Manejo global de errores]
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AdminController.php          [Dashboard métricas]
│   │   │   ├── AuthController.php           [Login, logout, OAuth]
│   │   │   ├── MercadoPagoController.php    [Webhook MP - VULNERABLE]
│   │   │   ├── ReservaController.php        [CRUD reservas]
│   │   │   ├── ProfesionalController.php    [Profesionales]
│   │   │   ├── ClienteController.php        [Clientes]
│   │   │   ├── DisponibilidadController.php [Slots de horarios]
│   │   │   └── ... (20+ más)
│   │   ├── Middleware/
│   │   │   ├── EnsureEmailVerified.php
│   │   │   ├── ValidarRol.php
│   │   │   └── ...
│   │   └── Requests/
│   │       └── [FormRequests validación]
│   ├── Jobs/
│   │   ├── EnviarEmailNotificacion.php      [Job: email async]
│   │   ├── ProcesarPago.php
│   │   └── ... (5 más)
│   ├── Models/
│   │   ├── Usuario.php                      [Base: todas las personas]
│   │   ├── Cliente.php                      [Compra servicios]
│   │   ├── Profesional.php                  [Vende servicios]
│   │   ├── Administrador.php
│   │   ├── Reserva.php                      [MODELO CENTRAL - 21 relaciones]
│   │   ├── Servicio.php                     [Qué vende profesional]
│   │   ├── Horario.php                      [Franja de tiempo reservada]
│   │   ├── Ciclo.php                        [Período: "Verano 2026"]
│   │   ├── Agenda.php                       [Disponibilidad profesional/ciclo]
│   │   ├── ReglaDisponibilidad.php          [Ej: "Lun-Vie 09:00-18:00"]
│   │   ├── ExcepcionDisponibilidad.php      [Bloqueos puntuales]
│   │   ├── Pago.php                         [Transacciones]
│   │   ├── PaqueteComprado.php              [Pack de sesiones]
│   │   ├── Reseña.php                       [Ratings post-servicio]
│   │   ├── VideoSesion.php                  [LiveKit metadata]
│   │   ├── Ubicacion.php                    [Dirección profesional]
│   │   ├── Notificacion.php                 [En DB - no email]
│   │   ├── RangoHorario.php                 [LEGACY - no se usa]
│   │   └── Paquete.php                      [Plantillas de paquetes]
│   ├── Policies/
│   │   ├── ReservaPolicy.php                [Ver/editar reserva propia]
│   │   ├── PagoPolicy.php
│   │   └── ...
│   ├── Services/
│   │   ├── DisponibilidadService.php        [🔑 ALGORITMO SLOTS]
│   │   ├── MercadoPagoService.php           [🔑 PAGOS + WEBHOOK]
│   │   ├── ReservaSlotService.php           [Creación reserva]
│   │   ├── LiveKitService.php               [Tokens videollamada]
│   │   ├── ImageService.php                 [Cloudinary upload]
│   │   ├── AuthService.php
│   │   ├── ReservaService.php
│   │   ├── NotificacionService.py           [DB + EventBroadcasting]
│   │   ├── PagoService.php
│   │   └── ... (12 más)
│   └── Traits/
│       └── [Comportamientos compartidos]
│
├── bootstrap/
│   └── app.php                    [Configuración inicial Laravel]
│
├── config/
│   ├── app.php                    [APP_NAME, timezone, etc]
│   ├── broadcasting.php           [🔑 REVERB CONFIG]
│   ├── cache.php
│   ├── database.php               [PostgreSQL connection]
│   ├── queue.php                  [🔑 DATABASE DRIVER]
│   ├── services.php               [🔑 MP, LiveKit, Google OAuth]
│   ├── mercadopago.php            [Keys de Mercado Pago]
│   └── ...
│
├── database/
│   ├── factories/                 [Generadores datos fake]
│   ├── migrations/
│   │   ├── 2026_05_*.php          [31 migrations PostgreSQL]
│   │   ├── create_usuarios_table.php
│   │   ├── create_reservas_table.php
│   │   ├── create_reglas_disponibilidad_table.php
│   │   └── ...
│   └── seeders/
│       └── [Popular BD datos iniciales]
│
├── public/
│   └── index.php                  [Punto entrada Laravel]
│
├── resources/
│   └── views/                     [NO USADO - API only]
│
├── routes/
│   ├── api.php                    [🔑 95 RUTAS API]
│   ├── channels.php               [🔑 REVERB CHANNELS]
│   └── web.php                    [NO USADO]
│
├── storage/
│   ├── app/
│   └── logs/                      [Laravel logs]
│
├── tests/
│   ├── Feature/                   [Tests integración]
│   └── Unit/                      [Tests unitarios]
│
├── .env                           [🔑 CREDENCIALES - NUNCA COMMITEAR]
├── .env.example                   [Template variables]
├── composer.json                  [Dependencias PHP]
├── Dockerfile                     [Container image]
├── docker-compose.yml             [FALTA - solo existe Dockerfile]
└── artisan                        [CLI Laravel]


frontend/ (SEPARADO)
├── src/
│   ├── app/
│   │   ├── pages/
│   │   │   ├── booking-list/              [Paso 1: Elegir profesional]
│   │   │   ├── select-time-date/          [Paso 2: Elegir horario - 60% listo]
│   │   │   ├── pago/                      [Paso 3: Pago manual - INSEGURO]
│   │   │   ├── perfil/                    [Perfil usuario]
│   │   │   ├── videollamada/              [🔴 VACÍO - NECESITA IMPLEMENTAR]
│   │   │   ├── home/                      [Página inicial]
│   │   │   └── ... (10+ más)
│   │   ├── services/
│   │   │   ├── auth.service.ts            [OAuth + Sanctum]
│   │   │   ├── reserva.service.ts         [API reservas]
│   │   │   ├── schedule.service.ts        [Slots API]
│   │   │   ├── pago.service.ts            [Mercado Pago]
│   │   │   ├── livekit.service.ts         [Videollamada]
│   │   │   └── ... (10+ más)
│   │   ├── guards/
│   │   │   ├── booking-guard.ts           [Protege steps 2 y 3]
│   │   │   └── auth.guard.ts              [Protege rutas privadas]
│   │   ├── interceptors/
│   │   │   └── auth.interceptor.ts        [Inyecta token Sanctum]
│   │   └── components/                    [Shared UI components]
│   └── ...
│
└── package.json                   [Angular dependencies]
```

---

## 🔄 FLUJOS PRINCIPALES

### 1️⃣ FLUJO RESERVA (Cliente)

```
🎬 START: Frontend búsqueda
  ↓
[Paso 1] Select Profesional
  → GET /api/servicios?query=...
  → GET /api/profesionales/{id}
  ↓
[Paso 2] Select Horario + Fecha
  → GET /api/disponibilidad?profId=X&fecha=Y&serviceId=Z
  ↓ DisponibilidadService.getDisponibilidad() genera SLOTS en memoria
  ↓
[Paso 3] Pago
  → POST /api/reservas (crea Reserva + Horario)
  → POST /api/mercadopago/preferences (genera link MP)
  → Cliente redirígido a checkout MP
  ↓
[Webhook] Mercado Pago
  → POST /api/webhooks/mercadopago
  ↓ ⚠️ SIN VALIDACIÓN DE FIRMA
  ↓
[Backend] MercadoPagoService.procesarReserva()
  → Actualiza estado Reserva: pendiente → confirmada
  → Emite evento: ReservaActualizada
  → 🟡 Cola job: EnviarEmailNotificacion (pero MAIL_MAILER=log)
  ↓
[Frontend] 🟡 NO ESCUCHA EVENTO (falta Laravel Echo)
  ↓
✅ Reserva confirmada (pero usuario NO se entera)
```

### 2️⃣ FLUJO PAGO (Estados)

```
Pago.pendiente
  ↓ [Usuario hace click checkout MP]
  ↓
Pago.aprobado (webhook MP)
  ↓ [Automático: MercadoPagoService]
  ↓
Reserva.pendiente → Reserva.confirmada
  ↓
✅ Ambos confirmados
```

**Estados posibles**:
- ✅ pendiente → aprobado → confirmada (normal)
- ❌ pendiente → rechazado (usuario ve error)
- ⚠️ pendiente → SIN CAMBIO (timeout/bug)

### 3️⃣ FLUJO VIDEOLLAMADA (Vacío)

```
✅ Backend listo:
  GET /api/reservas/{id}/video-token
  → LiveKitService.generarTokenParaReserva()
  → Retorna JWT + URL sala

🔴 Frontend VACÍO:
  videollamada.component.ts: solo template
  Necesita: @livekit/components-angular + <lk-room>
```

### 4️⃣ FLUJO ADMINISTRADOR

```
GET /api/admin/dashboard
→ AdminController.estadisticas()
→ Retorna:
  - Ingresos totales
  - Reservas por estado
  - Usuarios activos
  - Profesionales
```

---

## 🗄️ BASE DE DATOS (21 MODELOS)

### NÚCLEO DE USUARIOS
```
usuarios (idUsuario)
  ├─ clientes (idCliente)
  ├─ profesionales (idProfesional)
  └─ administradores (idAdmin)
```

### RESERVAS & PAGOS
```
reservas (idReserva)
  ├─ pagos (1:1)
  ├─ horarios (N)
  ├─ video_sesiones (1:N)
  └─ reseñas (1:N post-completada)

pagos (idPago)
  ├─ estado: pendiente, aprobado, rechazado, cancelado, reembolsado
  └─ metodoPago: mercado_pago, tarjeta, efectivo
```

### DISPONIBILIDAD & AGENDAS
```
ciclos (idCiclo)           [Período: "Verano 2026"]
  └─ agendas (idAgenda)    [Profesional en ese ciclo]
      └─ reglas_disponibilidad (idRegla)  [Lun-Vie 09:00-18:00]
          └─ excepciones_disponibilidad    [Bloqueos puntuales]

horarios (idHorario)       [Franja reservada]
  ├─ idReserva
  ├─ horaInicio, horaFin
  └─ estado: disponible, bloqueado, reservado
```

### SERVICIOS & PAQUETES
```
servicios (idServicio)
  ├─ profesional
  └─ duracion, precio
      └─ paquetes (idPaquete)  [Ofertas: 5 sesiones = -20%]

paquetes_comprados (idPaqueteComprado)
  ├─ cliente
  ├─ paquete
  └─ estados: pendiente, activo, agotado, cancelado
```

### OTROS
```
ubicaciones (idUbicacion)  [Dirección profesional]
reseñas (idReseña)         [Rating post-servicio]
notificaciones (idNotif)   [En DB, no email - FALTA SMTP]
video_sesiones (idVideoSesion)  [Metadata LiveKit]
```

**Total**: 21 tablas + 9 migraciones de relaciones

---

## 🔐 AUTENTICACIÓN & AUTORIZACIÓN

### Sanctum (API Token Auth)
```
POST /api/login
→ Genera token
→ Guarda en headers: Authorization: Bearer <token>

POST /api/logout
→ Revoca token
```

### OAuth Google
```
GET /api/auth/redirect
→ Redirige a Google
→ Google devuelve code
→ Backend intercambia por usuario
→ Crea token Sanctum
```

### Roles & Permisos
```
Usuario → hasOne Cliente
       → hasOne Profesional
       → hasOne Administrador

// Ejemplo: Usuario es AMBOS Cliente Y Profesional
```

### Policies (Autorización)
```
ReservaPolicy:
  - view/update/delete solo si:
    - Eres cliente de la reserva
    - O eres profesional de la reserva
    - O eres admin

PagoPolicy:
  - Ver pago solo si:
    - Eres cliente de reserva
    - O eres profesional de reserva
```

---

## ⚙️ INTEGRACIONES EXTERNAS

### 1️⃣ MERCADO PAGO (Pagos)
```
Configuración:
  - config/mercadopago.php
  - .env: MP_ACCESS_TOKEN, MP_PUBLIC_KEY

Flujo:
  1. crear preferencia: MercadoPagoService::createPreferenciaReserva()
  2. Retorna: { initPoint: "https://checkout.mercadopago.com/..." }
  3. Cliente redirígido
  4. Compra
  5. Webhook: POST /api/webhooks/mercadopago
  6. Procesa: MercadoPagoService::procesarWebhook()

⚠️ VULNERABILIDAD:
  - Sin validación firma x-signature
  - Alguien puede falsificar webhook
  - Cambiar estado pago a aprobado sin pagar
  - Solución: Validar header x-signature contra MP keys
```

### 2️⃣ LIVEKIT (Videollamadas)
```
Configuración:
  - config/services.php
  - .env: LIVEKIT_URL, LIVEKIT_API_KEY, LIVEKIT_API_SECRET

Backend:
  - LiveKitService::generarTokenParaReserva() ✅
  - Genera JWT válido
  - Crea VideoSesion en BD

Frontend:
  - 🟡 VACÍO: videollamada.component.ts
  - Necesita: npm install @livekit/components-angular
  - Usar: <lk-room token="..." url="...">
```

### 3️⃣ CLOUDINARY (Imágenes)
```
Configuración:
  - config/services.php
  - .env: CLOUDINARY_URL

Uso:
  - ImageService::uploadProfileImage()
  - Usuarios suben foto → Cloudinary
  - Guarda URL en usuarios.fotoPerfil
```

### 4️⃣ REVERB (WebSockets - TIEMPO REAL)
```
Configuración:
  - config/broadcasting.php ✅
  - routes/channels.php ✅
  - .env: REVERB_HOST, REVERB_PORT=8081

Backend - Emite eventos:
  - ReservaActualizada ($reserva) [Cuando confirmada]
  - ReservaCreada ($reserva)
  - PagoActualizado ($pago)
  - VideoSesionIniciada ($videoSesion)
  - [5 eventos más]

Frontend - 🟡 NO ESCUCHA:
  - Falta: npm install laravel-echo socket.io-client
  - Falta: Suscribirse a canales
  - Falta: Listeners en componentes
  - Impacto: Usuario NO ve cambios en tiempo real
```

### 5️⃣ GOOGLE OAUTH (Login Social)
```
Configuración:
  - config/services.php
  - .env: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET

Flujo:
  GET /api/auth/google
  → Redirect a Google
  → Google callback a frontend
  → Frontend hace POST /api/auth/google-callback?code=...
  → Backend intercambia code por user_info
  → Crea/actualiza usuario
  → Retorna token Sanctum + user data
```

---

## 📦 DEPENDENCIAS CLAVE

### Backend (composer.json)
```
laravel/framework 13.8           [Framework]
laravel/sanctum 4.3              [API Auth]
laravel/reverb 1.10              [WebSockets]
laravel/socialite 5.27           [OAuth Google]
mercadopago/sdk 3.10             [Pagos]
cloudinary/cloudinary_php 2.13   [Imágenes]
livKit/server-sdk 0.10.2         [LiveKit]
```

### Frontend (package.json)
```
@angular/core 21.2               [Framework]
@angular/common 21.2
typescript 5.7
tailwindcss 3                    [CSS utility]
axios                            [HTTP client]

🟡 FALTA INSTALAR:
laravel-echo 1.15                [WebSockets client]
socket.io-client 4               [Socket transport]
@livekit/components-angular      [Videollamada]
```

---

## 🛠️ CÓMO CORRER LOCALMENTE

### 1. Setup Backend
```bash
# Variables de entorno
cp .env.example .env
# Editar .env con credenciales TEST

# Instalar dependencias
composer install

# BD: PostgreSQL debe estar corriendo
# Update: psql -U postgres -c "ALTER USER postgres PASSWORD 'your_pass';"

# Migraciones
php artisan migrate

# Generar key
php artisan key:generate

# Sanctum tokens
php artisan migrate --path=database/migrations

# Correr en terminal 1
php artisan serve                # API en http://localhost:8000
php artisan queue:listen         # Cola de jobs (terminal 2)
php artisan schedule:run         # Scheduler (terminal 3 o cron)
php artisan reverb:start         # WebSockets (terminal 4)
```

### 2. Setup Frontend
```bash
cd frontend
npm install
ng serve                         # http://localhost:4200
```

### 3. PostgreSQL
```bash
# Docker
docker run -d \
  --name postgres-taller \
  -e POSTGRES_PASSWORD=secret \
  -e POSTGRES_DB=taller_php \
  -p 5432:5432 \
  postgres:17

# O instalar local
# https://www.postgresql.org/download/
```

### 4. LiveKit (dev)
```bash
# Usar servidor público
# config/services.php: LIVEKIT_URL = "wss://tallerphp-8zn46ybo.livekit.cloud"
# Ya configurado en .env.example
```

### 5. Mercado Pago (dev)
```bash
# Usar credenciales TEST de .env
# No paga dinero real
# Se ve en Dashboard MP
```

---

## 🚨 CRÍTICO - QUÉ FALTA

### 🔴 BLOQUEA DEMO (6-8 horas)

1. **Scheduler no existe**
   - Archivo falta: `app/Console/Kernel.php`
   - Sin él: No se cancelen reservas vencidas
   - Solución: Crear archivo + registrar comando
   - Tiempo: 1 hora

2. **Emails no funcionan**
   - Problema: `MAIL_MAILER=log` (solo registra en storage/logs)
   - Sin emails: Usuarios no se enterenan de reservas
   - Solución: Cambiar a SMTP real (SendGrid, Mailtrap)
   - Tiempo: 1-2 horas

3. **Queue worker no activo**
   - Problema: `php artisan queue:listen` NO ejecutándose
   - Efecto: Jobs en BD nunca se procesan
   - Solución: Terminal con `queue:listen` o supervisord
   - Tiempo: 30 minutos

4. **WebSocket frontend falta**
   - Falta: `npm install laravel-echo socket.io-client`
   - Falta: Listeners en componentes (ej: agenda-profesional.{id})
   - Efecto: Usuario no ve cambios en tiempo real
   - Tiempo: 1.5 horas

5. **LiveKit frontend vacío**
   - Archivo: `videollamada.component.ts` es solo template
   - Falta: `npm install @livekit/components-angular`
   - Falta: Componente implementado con <lk-room>
   - Tiempo: 1.5 horas

6. **Webhook MP vulnerable**
   - Problema: Sin validación de firma x-signature
   - Efecto: Alguien puede falsificar pago
   - Solución: Validar header contra MP keys
   - Tiempo: 1 hora

### 🟡 BLOQUEA PRODUCCIÓN (12-15 horas)

7. **docker-compose.yml inexistente**
   - Solo existe Dockerfile
   - Falta: PostgreSQL, Reverb, Queue Worker, Scheduler, Nginx
   - Necesario para deploy

8. **Credenciales expuestas**
   - .env con MP keys TEST visible
   - Necesario: Usar secrets en CI/CD

9. **Tests faltantes**
   - Cero tests
   - Necesarios: E2E de flujos críticos

10. **Rate limiting**
    - Sin protección DDoS
    - Endpoints públicos abiertos

11. **HTTPS/CORS**
    - Sin configurar
    - Necesario en producción

12. **Logs/Monitoring**
    - Sin Sentry/NewRelic
    - Necesario para debug en prod

---

## 📍 DÓNDE ESTÁ CADA COSA

### Usuario quiere hacer X → Va a Y

| Funcionalidad | Archivo Backend | Archivo Frontend | Estado |
|--|--|--|--|
| Login | AuthController | auth.service.ts | ✅ OK |
| OAuth Google | AuthController.googleCallback() | auth.service.ts | ✅ OK |
| Reservar servicio | ReservaSlotService::reservar() | booking-list, select-time-date, pago | 🟡 Falta LiveKit |
| Ver disponibilidad | DisponibilidadController::getDisponibilidad() | select-time-date | ✅ OK |
| Pagar con MP | MercadoPagoController::webhook() | pago.component | 🟡 Formulario manual inseguro |
| Videollamada | LiveKitService::generarTokenParaReserva() | videollamada.component | ❌ VACÍO |
| Ver mis reservas | ReservaController::miasReservas() | reservas-list | ✅ OK |
| Crear servicio (prof) | ServicioController::store() | servicios-admin | 🟡 No testeado |
| Ver ingresos (admin) | AdminController::estadisticas() | dashboard-admin | ✅ OK |
| Reseñar servicio | ReseñaController::store() | reseña (routing falta) | 🟡 Falta ruta |
| Cancelar reserva | ReservaController::cancelar() | reservas-list | ✅ OK |
| Cambiar disponibilidad (prof) | AgendaController::update() | agenda-admin | 🟡 Falta validar |
| Recibir notificación tiempo real | ReservaActualizada event | 🟡 Falta escuchar | ❌ No implementado |

---

## 🎯 PRÓXIMAS ACCIONES (EN ORDEN)

### INMEDIATO (Hoy - 2 horas)
1. [ ] Crear `app/Console/Kernel.php` con scheduler
2. [ ] Cambiar `MAIL_MAILER=log` a SMTP real
3. [ ] Verificar webhook MP (agregar validación)

### ESTA SEMANA (4-6 horas)
4. [ ] `npm install laravel-echo socket.io-client` en frontend
5. [ ] Implementar listeners en componentes (ej: agenda)
6. [ ] Implementar `videollamada.component.ts` con LiveKit
7. [ ] Testear flujo completo: reserva → pago → videollamada

### PRÓXIMA SEMANA (8-12 horas)
8. [ ] Crear docker-compose.yml completo
9. [ ] Setup HTTPS + CORS + Rate limiting
10. [ ] Obtener credenciales MP PRODUCCIÓN
11. [ ] Tests E2E principales
12. [ ] Deploy a servidor

---

## 📚 DOCUMENTACIÓN IMPORTANTE

- **Migraciones**: `database/migrations/` - 31 archivos, consultar específico
- **Modelos**: `app/Models/` - 21 archivos, ver relaciones
- **Controladores**: `app/Http/Controllers/Api/` - 27 archivos
- **Servicios**: `app/Services/` - 21 archivos (lógica)
- **Rutas**: `routes/api.php` - 95 endpoints

---

## 🚀 PRODUCCIÓN

### Variables críticas .env.production
```
APP_ENV=production
APP_DEBUG=false
MAIL_MAILER=smtp              [Cambiar de log]
MAIL_FROM_ADDRESS=noreply@tallerphp.com
MERCADOPAGO_ACCESS_TOKEN=     [Credencial PRODUCCIÓN]
POSTGRES_HOST=                [RDS o servidor]
LIVEKIT_URL=                  [Servidor LiveKit]
```

### Servicios necesarios
- PostgreSQL (RDS)
- Redis (opcional, cache)
- Nginx (reverse proxy)
- Supervisor (queue worker + scheduler)
- Sentry (error tracking)
- SendGrid/Mailtrap (emails)

---

**Última actualización**: 2026  
**Auditor**: Copilot AI  
**Estado general**: 75% → 95% (después arreglos críticos)
