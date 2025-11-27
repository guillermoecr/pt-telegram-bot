# 🤖 Proyecto: API de Chatbot de Telegram (Laravel 11)

¡Hola! Este es el código que desarrollé para la pt, implementando una API que integra mi bot de Telegram con un panel de administracion en Laravel.

El objetivo principal fue crear una arquitectura limpia y testeable (TDD) capaz de recibir mensajes, guardarlos y enviar respuestas automaticas, dejando la puerta abierta para integrar IA avanzada facilmente.

## 🚀 Características Clave y Logros

* **Piping de Telegram Completo:** Recibo mensajes por **Webhook** y los guardo inmediatamente.
* **Respuesta Automática Inteligente (Plus IA):** El bot responde automáticamente a cada mensaje, y el sistema está desacoplado para ser conectado a un LLM (como Gemini o GPT) solo cambiando una línea de código.
* **Panel Administrativo (CRM Básico):** Un panel protegido por *login* donde puedo ver todas las conversaciones históricas y enviar mensajes manuales a los contactos.
* **Código de Calidad:** Arquitectura basada en Capas y Patrones de Diseño (Service/Contract/Inyección de Dependencias).
* **Testing TDD:** Feature Tests para la seguridad, persistencia de datos y funcionalidad de respuesta del Webhook, que demuestran la fiabilidad del sistema.

## ⚙️ Configuración del Entorno (¡Para que funcione en tu máquina!)

### Prerequisitos

Necesitas **PHP 8.2+**, **Composer**, **npm** y una base de datos **MySQL** accesible.

1.  **Clonar y Configurar Dependencias:**

    ```bash
    git clone [https://aws.amazon.com/es/what-is/repo/](https://aws.amazon.com/es/what-is/repo/) telegram-bot-api
    cd telegram-bot-api
    composer install
    npm install
    npm run dev
    ```

2.  **Archivos de Entorno (`.env`):**
    * Copia `.env.example` a `.env`.
    * **Base de Datos:** Configura tu conexión MySQL (`DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
    * **Telegram:** Debes obtener tu `BOT_TOKEN` de BotFather y crear tu propio *secret* para seguridad.

    ```dotenv
    # Sección Telegram
    TELEGRAM_BOT_TOKEN="TU_TOKEN_REAL_DE_BOTFATHER"
    TELEGRAM_WEBHOOK_SECRET="clave-unica-secreta-ejemplo-de-seguridad" 
    ```

3.  **Base de Datos y Usuario Admin:**
    * Crea una base de datos vacía.
    * Ejecuta las migraciones (incluyendo nuestras tablas de `chats`, `conversations` y `messages`).
    * Crea el usuario que usaremos para acceder al panel:

    ```bash
    php artisan migrate
    php artisan tinker
    \App\Models\User::factory()->create(['email' => 'admin@example.com', 'password' => bcrypt('password')]);
    exit;
    ```
    *Credenciales de acceso: **admin@example.com** / **password***

4.  **Habilitar Rutas API (CRÍTICO en Laravel 11):**
    Asegúrate de que `bootstrap/app.php` tenga la línea `api:` habilitada para que el Webhook funcione (esto se solucionó en la fase de desarrollo).

## 🧭 Arquitectura y Diseño Técnico

El proyecto sigue una arquitectura organizada para facilitar el mantenimiento y la escalabilidad:

| Capa | Responsabilidad | Detalles Técnicos Clave |
| :--- | :--- | :--- |
| **Modelos/DB** | Persistencia | Uso de **Eloquent ORM** con **Relaciones 1:N** bien definidas (`hasMany`/`belongsTo`). Uso de **`$fillable`** para protección contra *Mass Assignment*. |
| **Rutas** | Enrutamiento | Separación de tráfico (`api.php` para Webhook) y lógica protegida (`web.php` para Panel). |
| **Controladores**| Lógica de Capa Media | Uso de **Inyección de Dependencias** (para `TelegramService`) y **Single Action Controller** (`__invoke`) para el Webhook. |
| **Servicios** | Lógica de Negocio | **`TelegramService`** (Outbound/Inbound/Persistencia). **`ReplyServiceInterface`** (Contrato para la respuesta). |
| **Arquitectura**| Abstracción | Implementación del **Principio de Inversión de Dependencias (DIP)**: el sistema depende de la interfaz `ReplyServiceInterface`, permitiendo cambiar el `RandomReplyService` por una implementación de IA (ej., **`GeminiAIService`**) sin modificar el controlador ni el flujo principal del Webhook. **¡Este es mi Plus de escalabilidad!** |

## 🧪 Cómo Correr los Tests (TDD)

Todos los tests de seguridad y funcionalidad del Webhook deben pasar:

```bash
# Correr los tests principales del Webhook
php artisan test --filter TelegramWebhookTest