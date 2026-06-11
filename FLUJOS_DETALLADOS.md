# 🔄 FLUJOS DETALLADOS - TALLER PHP 2026

## 1️⃣ FLUJO COMPLETO: BUSCAR → RESERVAR → PAGAR

### Fase 1: BÚSQUEDA (Frontend)

```
🎬 Cliente abre app
  ↓
GET /api/servicios?query=diseño&ciudad=montevideo
  ← [ServicioController::index()]
  ← SELECT servicios WHERE nombre LIKE '%diseño%'
  
Retorna:
{
  servicios: [
    {
      idServicio: 1,
      nombre: "Diseño Gráfico",
      descripcion: "Logo + marca",
      duracion: 60,        [minutos]
      precio: 2000,        [pesos UY]
      profesional: {
        idProfesional: 5,
        nombre: "Juan",
        fotoPerfil: "https://res.cloudinary.com/...",
        calificacion: 4.8,
        totalResenas: 45
      }
    }
  ]
}

🎨 Frontend muestra lista
```

### Fase 2: SELECCIONAR HORARIO (Frontend + Backend)

```
Usuario hace click en servicio
  ↓
GET /api/disponibilidad?idProfesional=5&fecha=2026-03-15&idServicio=1
  ← [DisponibilidadController::getDisponibilidad()]
  ← [DisponibilidadService::getDisponibilidad()]

💥 ALGORITMO CORE:
1. Buscar Ciclo actual
2. Buscar Agenda (profesional en ciclo)
3. Obtener ReglaDisponibilidad (ej: Lun-Vie 09:00-18:00)
4. Verificar ExcepcionDisponibilidad (bloqueos puntuales)
5. Obtener Reservas existentes de ese profesional
6. Generar SLOTS en memoria:
   - Duracion servicio (60 min) + bufferMinutos (15 min) = 75 min
   - Desde 09:00 hasta 18:00
   - Saltar 12:00-13:00 (almuerzo) si está en regla
   - Excluir si ya existe reserva en ese horario
   - Resultado: [09:00, 09:30, 10:00, 10:30, ...]

Retorna:
{
  fecha: "2026-03-15",
  slots: [
    { inicio: "09:00", fin: "10:00", disponible: true },
    { inicio: "09:30", fin: "10:30", disponible: true },
    { inicio: "10:00", fin: "11:00", disponible: false },  [Reserva anterior]
    { inicio: "10:30", fin: "11:30", disponible: true },
    ...
  ]
}

🎨 Frontend muestra calendario + horas
```

### Fase 3: CONFIRMAR RESERVA (Frontend → Backend)

```
Usuario selecciona: 15 de marzo, 14:00
  ↓
POST /api/reservas
{
  idProfesional: 5,
  idServicio: 1,
  idCliente: 12,
  fecha: "2026-03-15",
  horaInicio: "14:00",
  horaFin: "15:00"
}
  ← [ReservaController::store()]
  ← [ReservaSlotService::reservar()]

✅ Backend valida:
  1. Fecha válida (no pasada)
  2. Horario disponible (re-verificar)
  3. Profesional existe y está activo
  4. Servicio existe y pertenece a profesional
  5. Cliente está autenticado

✅ Backend crea:
  1. Reserva.idReserva = "RES-12345"
     - estado: "pendiente"
     - idCliente: 12
     - idProfesional: 5
     - idServicio: 1
     - fecha: "2026-03-15"
     - monto: 2000
     
  2. Horario.idHorario = "HOR-54321"
     - horaInicio: "14:00"
     - horaFin: "15:00"
     - estado: "bloqueado"  [Ya no disponible]
     
  3. Pago.idPago = "PAG-99999"
     - estado: "pendiente"
     - monto: 2000
     - metodoPago: null  [Se decide en paso 3]

✅ Backend emite:
  → Event ReservaCreada {idReserva, cliente, profesional}
     └─ Broadcastea a: agenda-profesional.5
        [Profesional ve: "Nueva reserva pendiente"]

✅ Backend cola Job:
  → EnviarEmailNotificacion (pendiente)
     - A profesional: "Nueva reserva de Juan"
     - 🟡 NUNCA se ejecuta (queue worker no activo)

Retorna:
{
  idReserva: "RES-12345",
  estado: "pendiente",
  profesional: {...},
  servicio: {...},
  monto: 2000
}
```

### Fase 4: PAGO (Frontend)

```
🎬 Frontend: Paso 3 - Pago

[Usuario elige método]
  ├─ Mercado Pago
  ├─ Tarjeta
  └─ Efectivo/Redpagos

[Si elige MERCADO PAGO]
  ↓
POST /api/mercadopago/preferences
{
  idReserva: "RES-12345"
}
  ← [MercadoPagoController::crearPreferencia()]
  ← [MercadoPagoService::createPreferenciaReserva()]

Backend llama MP API:
```
$mp->checkout->preferences->create([
  "items" => [{
    "title" => "Diseño Gráfico",
    "unit_price" => 2000,
    "quantity" => 1
  }],
  "payer" => {
    "email" => "cliente@email.com"
  },
  "back_urls" => {
    "success" => "https://app/reservas?status=approved",
    "failure" => "https://app/reservas?status=cancelled",
    "pending" => "https://app/reservas?status=pending"
  }
]);
```

Retorna:
```json
{
  "id": "123456789",
  "initPoint": "https://checkout.mercadopago.com/pay/v2/checkout/123456789"
}
```

Frontend redirige usuario a initPoint
  ↓
Usuario completa pago en Mercado Pago
  ↓
MP redirige a: success URL o failure URL
```

### Fase 5: WEBHOOK MERCADO PAGO (Backend)

```
🌐 Mercado Pago → POST /api/webhooks/mercadopago
{
  "id": "notification_123",
  "type": "payment",
  "data": {
    "id": "payment_999999"
  },
  "user_id": "mp_user_123"
}

⚠️ PROBLEMA: Sin validación de firma x-signature
   → Alguien puede enviar JSON fake
   → Backend actualiza estado sin verificar

Backend procesa:
  ← [MercadoPagoController::webhook()]
  ← [MercadoPagoService::procesarWebhook()]

1. Obtiene payment_id: 999999
2. Llama MP API: GET /payments/999999

MP responde:
{
  "id": 999999,
  "status": "approved",
  "status_detail": "accredited",
  "transaction_amount": 2000,
  "payer": { "email": "cliente@email.com" },
  "external_reference": "RES-12345"  [Nuestra Reserva ID]
}

3. Si status == "approved":
   
   a) Actualiza Pago:
      Pago.estado = "aprobado"
      Pago.referenciaExterna = "999999"
   
   b) Actualiza Reserva:
      Reserva.estado = "confirmada"
   
   c) Emite evento:
      Event ReservaActualizada {idReserva, estado: confirmada}
      Broadcast: agenda-profesional.5
   
   d) Cola Job:
      EnviarEmailNotificacion
      - A cliente: "Reserva confirmada"
      - A profesional: "Reserva confirmada"

✅ Pago COMPLETADO
   (Pero clientes NO se enterenan - falta email + Reverb listener)
```

---

## 2️⃣ FLUJO: VIDEOLLAMADA

### Estado Actual: Backend ✅ | Frontend ❌

```
Hora de la videollamada llegó...

Cliente hace click: "Iniciar videollamada"
  ↓
GET /api/reservas/{id}/video-token
  ← [ReservaController::obtenerVideoToken()]
  ← [LiveKitService::generarTokenParaReserva()]

Backend:
1. Verifica que reserva.estado == "enCurso"
2. Obtiene video_sesion O crea una:
   {
     idVideoSesion: "VS-111",
     idReserva: "RES-12345",
     dateInicio: "2026-03-15 14:00:00",
     dateFin: null
   }
3. Genera JWT token usando LiveKit API key:
   token = JWT.sign({
     sub: cliente.email,
     room: "reserva-12345",
     metadata: "Cliente"
   }, secretKey)

Retorna:
{
  token: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  url: "wss://tallerphp-8zn46ybo.livekit.cloud",
  roomName: "reserva-12345"
}

🔴 Frontend - NECESITA IMPLEMENTAR:
```typescript
// videollamada.component.ts (ACTUALMENTE VACÍO)

import { LivekitRoom } from '@livekit/components-angular';

export class VideollamadaComponent {
  token: string;
  url: string;
  roomName: string;

  ngOnInit() {
    this.reservaService.obtenerVideoToken(idReserva).subscribe(response => {
      this.token = response.token;
      this.url = response.url;
      this.roomName = response.roomName;
    });
  }
}
```

Template:
```html
<lk-room
  [token]="token"
  [url]="url"
  [roomName]="roomName"
  [layoutMode]="'grid'">
</lk-room>
```

Cuando ambos se conectan:
  - Cada uno crea conexión a LiveKit
  - Intercambian audio/video
  - ✅ Funciona

Cuando termina:
  - Profesional cierra sala
  - Frontend POST /api/reservas/{id}/terminar-video
  - Backend actualiza VideoSesion.dateFin
  - Reserva.estado = "completada"
```

---

## 3️⃣ FLUJO: DISPONIBILIDAD (Profesional edita horarios)

### Estructura de capas

```
📦 CICLO
   └─ Período de tiempo (ej: "Verano 2026")
      Inicio: 2026-03-01
      Fin: 2026-06-30
      
      📦 AGENDA
         └─ Disponibilidad del profesional en ESTE ciclo
            Profesional: Juan
            Ciclo: Verano 2026
            Activa: true
            
            📦 REGLA DISPONIBILIDAD (Horarios recurrentes)
               └─ Ej: "Lunes a Viernes, 09:00-18:00"
                  Dia Semana: 1 (Lunes)
                  Hora Inicio: 09:00
                  Hora Fin: 18:00
                  Pausa Almuerzo: 12:00-13:00 (pausaMinutos: 60)
                  Buffer: 15 min después de cada servicio
                  Activa: true
            
            📦 EXCEPCIÓN DISPONIBILIDAD (Bloqueos puntuales)
               └─ Ej: "15 de marzo no trabajo"
                  Fecha: 2026-03-15
                  Hora Inicio: 00:00
                  Hora Fin: 23:59
                  Motivo: "Vacaciones"
```

### Flujo de edición

```
Profesional abre: "Editar disponibilidad"
  ↓
GET /api/agendas/{idAgenda}
  ← Retorna: Ciclo actual + Reglas + Excepciones
  ↓
Profesional ve formulario:
  - Ciclo: [Seleccionar]
  - Reglas: [Tabla editable]
  - Excepciones: [Tabla editable]
  ↓
Profesional modifica:
  - Agranda horario Lunes: 09:00-20:00
  - Agrega excepción: 15 de marzo vacaciones
  ↓
PATCH /api/agendas/{idAgenda}
{
  "reglas": [
    { idRegla: 1, diaSemana: 1, horaInicio: "09:00", horaFin: "20:00", ... }
  ],
  "excepciones": [
    { idExcepcion: null, fecha: "2026-03-15", horaInicio: "00:00", ... }
  ]
}
  ← [AgendaController::update()]
  ← Actualiza BD
  ← Emite Event: AgendaActualizada
  
✅ Próximas búsquedas usarán nuevos horarios
```

---

## 4️⃣ FLUJO: NOTIFICACIONES EN TIEMPO REAL (REVERB)

### Arquitectura actual

```
BACKEND - EMITE EVENTOS:
  ↓
app/Events/ReservaActualizada.php
  implementa ShouldBroadcast
  {
    public function broadcastOn() {
      return new PrivateChannel('agenda-profesional.' . $this->profesional->id);
    }
  }
  
  → Reverb recibe evento
  → Pushea a todos los suscriptores del canal

FRONTEND - NECESITA ESCUCHAR: 🟡 NO IMPLEMENTADO

Agregar a app.config.ts:
```typescript
import Echo from 'laravel-echo';
import io from 'socket.io-client';

window.io = io;
window.Echo = new Echo({
  broadcaster: 'reverb',
  key: environment.reverb.key,
  wsHost: environment.reverb.host,
  wsPort: environment.reverb.port,
  forceTLS: environment.reverb.forceTLS
});
```

En componentes (ej: agenda-profesional):
```typescript
ngOnInit() {
  // Escuchar solo si eres profesional
  if (this.authService.esProfesional) {
    Echo.private(`agenda-profesional.${this.authService.idProfesional}`)
      .listen('ReservaActualizada', (data) => {
        console.log('Nueva reserva:', data);
        this.refresh();  // Recarga tabla
      });
  }
}
```

Eventos disponibles:
- ReservaActualizada: Cambio estado reserva
- ReservaCreada: Nueva reserva
- PagoActualizado: Cambio estado pago
- VideoSesionIniciada: Conectarse a videollamada
- ReservaCompletada: Servicio finalizado
```

---

## 5️⃣ FLUJO: ROLES Y AUTORIZACIÓN

### Relación Usuario ↔ Roles

```
1 Usuario puede ser:
  - Solo Cliente
  - Solo Profesional
  - Solo Admin
  - Cliente + Profesional (multiplayer)

Ejemplo:
  Usuario "Juan" {
    idUsuario: 5,
    email: "juan@email.com"
  }
  
  Cliente {
    idCliente: 10,
    idUsuario: 5,
    saldo: 0,
    suscripcion: "gratis"
  }
  
  Profesional {
    idProfesional: 8,
    idUsuario: 5,
    descripcion: "Diseñador",
    calificacion: 4.8
  }
  
  // Juan es AMBOS
```

### Middleware de protección

```
// En routes/api.php:

Route::middleware('auth:sanctum')->group(function() {
  
  // Solo clientes pueden:
  Route::post('/reservas', [ReservaController::class, 'store'])
    ->middleware('rol:cliente');
  
  // Solo profesionales pueden:
  Route::patch('/agendas/{id}', [AgendaController::class, 'update'])
    ->middleware('rol:profesional');
  
  // Solo admins pueden:
  Route::get('/admin/dashboard', [AdminController::class, 'estadisticas'])
    ->middleware('rol:admin');
    
  // Cualquiera autenticado:
  Route::get('/perfil', [PerfilController::class, 'show']);
});
```

### Policy: Autorización a nivel recurso

```
// app/Policies/ReservaPolicy.php

public function view(Usuario $user, Reserva $reserva) {
  return 
    $user->cliente?->id === $reserva->cliente->id ||
    $user->profesional?->id === $reserva->profesional->id ||
    $user->admin !== null;
}

public function update(Usuario $user, Reserva $reserva) {
  // Solo profesional O cliente (no ambos)
  return 
    ($user->profesional?->id === $reserva->profesional->id &&
     $reserva->estado === 'pendiente') ||
    $user->admin !== null;
}

// En controlador:
$this->authorize('view', $reserva);  // Lanza 403 si falla
```

---

## 6️⃣ FLUJO: JOBS Y QUEUE

### Estado actual

```
❌ PROBLEMA: Queue worker NO está corriendo

Jobs guardados en BD pero NUNCA se ejecutan

Tabla: jobs
  ├─ id
  ├─ queue: "default"
  ├─ payload: {JSON serializado del job}
  ├─ exceptions: null
  ├─ failed_at: null
```

### Cómo funcionaría si estuviera activo

```
1. Backend despacha Job:

   dispatch(new EnviarEmailNotificacion(
     $usuario,
     'Reserva confirmada',
     $reserva
   ));

2. Job guardado en jobs table:
   {
     id: 1,
     queue: 'default',
     payload: {...EnviarEmailNotificacion...},
     available_at: 1710521832  [timestamp ahora]
   }

3. Queue worker ejecuta:

   php artisan queue:listen --tries=1 --timeout=0
   
   → Busca jobs en BD
   → Obtiene payload
   → Deserializa: new EnviarEmailNotificacion(...)
   → Ejecuta: handle()
   → Si OK: Borra job
   → Si error: Incrementa attempts, reintenta

4. Email enviado (si MAIL_MAILER != 'log')
```

### Jobs existentes

```
app/Jobs/
├── EnviarEmailNotificacion.php    [Email async]
├── ProcesarPago.php               [Post-pago]
├── ActualizarCalificacion.php     [Stats profesional]
├── GenerarReporte.php             [Admin]
├── LimpiarReservas.php            [Cleanup old]
└── ... (5 más)
```

---

## 7️⃣ FLUJO: SCHEDULER (FALTA)

### Qué necesita

```
❌ app/Console/Kernel.php NO EXISTE

Debería contener:
```php
protected function schedule(Schedule $schedule) {
  // Cancelar reservas vencidas diariamente
  $schedule->command('reservas:cancelar-vencidas')
    ->daily()
    ->at('03:00');
  
  // Generar reportes semanalmente
  $schedule->command('reportes:generar')
    ->weekly()
    ->mondays()
    ->at('08:00');
}
```

Para ejecutar en local:
```bash
php artisan schedule:run
```

En producción:
```bash
# Agregar a crontab:
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

O en Docker:
```yaml
scheduler:
  image: php:8.3-cli
  volumes: [. : /app]
  working_dir: /app
  command: |
    sh -c 'while true; do
      php artisan schedule:run
      sleep 60
    done'
  depends_on: [app]
```
```

---

## 🔗 INTEGRACIONES EXTERNAS

### Mercado Pago (Pagos)

```
Cliente compra → Frontend genera preferencia → MP checkout
  ↓
Cliente paga en MP
  ↓
MP webhook → Backend recibe notification
  ↓ [SIN VALIDACIÓN - CRÍTICO]
Backend procesa sin verificar firma
  ↓
Pago actualizado en BD
```

**Riesgos**:
- Sin validación x-signature: Cualquiera puede falsificar
- Sin verificación con API MP: Podría diferir

**Solución**:
```php
// MercadoPagoController::webhook()

$signature = request()->header('x-signature');
$request_id = request()->header('x-request-id');

// Validar firma
if (!$this->mercadopagoService->verificarFirma($signature, $request_id)) {
  return response()->json(['error' => 'Invalid signature'], 401);
}

// Luego procesar
```

### LiveKit (Videollamadas)

```
Backend: Genera JWT token ✅
Frontend: Conecta a sala 🟡 (falta implementar)
  ↓
Ambos en sala
  ↓
Audio/video fluye
```

### Cloudinary (Imágenes)

```
Frontend carga foto
  ↓
POST /api/usuarios/{id}/foto
  ↓
ImageService::uploadToCloudinary()
  ↓
Guarda URL en usuarios.fotoPerfil
```

### Google OAuth

```
GET /api/auth/redirect
  ↓
Redirect a Google
  ↓
Google redirige a frontend con code
  ↓
Frontend → POST /api/auth/google-callback?code=...
  ↓
Backend intercambia code por access_token
  ↓
Obtiene user_info
  ↓
Crea Usuario si no existe
  ↓
Retorna token Sanctum
```

---

## 📊 MATRIZ DE DEPENDENCIAS

```
Reserva
  ├─ Depende de: Cliente, Profesional, Servicio, Horario, Ciclo
  ├─ Es dependida por: Pago, VideoSesion, Reseña
  └─ Afecta: Agenda disponibilidad, Notificaciones

Pago
  ├─ Depende de: Reserva O PaqueteComprado
  ├─ Es dependida por: (ninguno)
  └─ Afecta: Estado reserva, Email notificación

Ciclo
  ├─ Depende de: (ninguno)
  ├─ Es dependida por: Agenda, ReglaDisponibilidad
  └─ Afecta: Disponibilidad búsqueda

ReglaDisponibilidad
  ├─ Depende de: Agenda, Ciclo
  ├─ Es dependida por: DisponibilidadService
  └─ Afecta: Slots generados

ExcepcionDisponibilidad
  ├─ Depende de: Agenda
  ├─ Es dependida por: DisponibilidadService
  └─ Afecta: Slots generados
```

---

## ⚡ CASOS DE USO CRÍTICOS

### Caso 1: Cliente modifica otra reserva
```
Cliente A hace PATCH /api/reservas/RES-999
  → Policy::update() verifica:
    - Es dueño de reserva?
    - NO
  → 403 Unauthorized
  ✅ PROTEGIDO
```

### Caso 2: Profesional ve ingresos ajenos
```
Profesional A GET /api/ingresos?profesional=5
  → Middleware validar: ¿eres profesional 5?
  → NO (eres profesional 8)
  → 403 Unauthorized
  ✅ PROTEGIDO
```

### Caso 3: Usuario se ve datos de otro usuario
```
Usuario A GET /api/usuarios/999/perfil
  → Policy::view() verifica:
    - Es tu perfil?
    - NO
    - Eres admin?
    - NO
  → 403 Unauthorized
  ✅ PROTEGIDO
```

### Caso 4: Webhook MP falsificado
```
Atacante POST /api/webhooks/mercadopago
  { "status": "approved", "idReserva": "RES-123" }
  
  ⚠️ SIN VALIDACIÓN:
    → Backend marca pago como aprobado
    → Cliente no pagó
    → Profesional espera
    
  ✅ CON VALIDACIÓN:
    → Verifica x-signature header
    → NO válida
    → 401 Unauthorized
    → PROTEGIDO
```

---

**Última actualización**: 2026  
**Auditor**: Copilot AI
