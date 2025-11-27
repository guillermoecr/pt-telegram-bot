# 🤖 Proyecto: API de Chatbot de Telegram (Laravel 11)

¡Hola! Este es el código que desarrollé para la prueba técnica, implementando una API que integra mi bot de Telegram con un panel de administración en Laravel.

El objetivo principal fue crear una arquitectura limpia y testeable (TDD) capaz de recibir mensajes, guardarlos y enviar respuestas automáticas, dejando la puerta abierta para integrar IA avanzada fácilmente.

## 🚀 Características Clave y Logros

* **Piping de Telegram Completo:** Recibo mensajes por **Webhook** y los guardo inmediatamente.
* **Respuesta Automática Inteligente (Plus IA):** El bot responde automáticamente a cada mensaje, y el sistema está desacoplado para ser conectado a un LLM (como Gemini o GPT) solo cambiando una línea de código.
* **Panel Administrativo (CRM Básico):** Un panel protegido por *login* donde puedo ver todas las conversaciones históricas y enviar mensajes manuales a los contactos.
* **Código de Calidad:** Arquitectura basada en Capas y Patrones de Diseño (**Service/Contract/Inyección de Dependencias**).
* **Testing TDD:** Feature Tests para la seguridad, persistencia de datos y funcionalidad de respuesta del Webhook, que demuestran la fiabilidad del sistema.

## ⚙️ Configuración del Entorno (¡Para que funcione en tu máquina!)

### Prerequisitos

Necesitas **PHP 8.2+**, **Composer**, **npm** y una base de datos **MySQL** accesible.

1.  **Clonar y Configurar Dependencias:**

    ```bash
    # Clona el repo
    git clone [https://github.com/guillermoecr/pt-telegram-bot.git](https://github.com/guillermoecr/pt-telegram-bot.git) telegram-bot-api 
    cd telegram-bot-api
    
    # Instala PHP y JS
    composer install
    npm install
    npm run dev
    ```

2.  **Archivos de Entorno (`.env`):**
    * Copia `.env.example` a `.env` y genera la clave de la aplicación: `php artisan key:generate`.
    * Configura tu conexión MySQL.
    * **Telegram:** Obtén tu `BOT_TOKEN` de BotFather y crea tu propio *secret*.

    ```dotenv
    # Sección Telegram
    TELEGRAM_BOT_TOKEN="TU_TOKEN_REAL_DE_BOTFATHER"
    TELEGRAM_WEBHOOK_SECRET="clave-unica-secreta-ejemplo-de-seguridad" 
    ```

3.  **Base de Datos y Usuario Admin (CRÍTICO):**
    * Crea la base de datos vacía.
    * Ejecuta la migración y la siembra de datos con un solo comando. Usamos el `migrate:fresh` para asegurar una base limpia.

    ```bash
    # Este comando borra tablas, migra y crea el usuario admin (admin@example.com / password).
    php artisan migrate:fresh --seed
    ```

4.  **Activación del Webhook (Paso Final y Crítico):**
    * **Problema:** Telegram solo puede enviar mensajes a URLs públicas (`HTTPS`). Necesitas un túnel (Ngrok, Expose) para obtener una URL pública (`https://ejemplo.io`).
    * **Solución:** Una vez que tengas tu URL pública, usa este comando para registrar el Webhook en la API de Telegram. **Asegúrate de incluir el `/api/telegram/webhook/TU_SECRET` al final de tu URL.**

    ```bash
    php artisan tinker
    >>> $service = app(\App\Services\TelegramService::class);
    >>> $service->setWebhook('TU_URL_PUBLICA_CON_SECRET');
    ```
    *(Ejemplo de URL a registrar: `https://tunel.ngrok.io/api/telegram/webhook/clave-unica-secreta-ejemplo-de-seguridad`)*

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
# Correr la suite completa (incluye seguridad, persistencia y respuesta automática)
php artisan test