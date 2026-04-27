# 🍰 Yvonne's Cake Shop — Web Application

A full-stack Laravel-based e-commerce platform for **Yvonne's Cake Shop**, featuring online ordering, paluwagan (installment) subscriptions, GCash payment integration via PayMongo, and a comprehensive admin management dashboard.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Installation & Setup](#-installation--setup)
- [Environment Configuration](#-environment-configuration)
- [Running the Application](#-running-the-application)
- [PayMongo Integration](#-paymongo-integration)
- [Ngrok Setup (Webhook Tunneling)](#-ngrok-setup-webhook-tunneling)
- [Common Commands](#-common-commands)

---

## ✨ Features

### 👤 Customer Side

- 🔐 User registration & authentication
- 🛒 Product catalog with cart system
- 📍 Multiple delivery addresses with map integration
- 💳 GCash payment (via PayMongo) & Cash on Delivery
- 📦 Order tracking & history
- ⭐ Order rating & reviews
- 🤝 Paluwagan (installment) subscription system
- 📅 Scheduled monthly payments
- 🧾 PDF receipt generation

### 🛠️ Admin Side

- 📊 Dashboard with sales analytics
- 📦 Product & inventory management
- 📋 Order management with status tracking
- 👥 User management (customer & admin roles)
- 💰 Sales reporting
- 🤝 Paluwagan package & subscription management
- 📈 Real-time payment monitoring

---

## 🚀 Tech Stack

| Layer                 | Technology                          |
| --------------------- | ----------------------------------- |
| **Backend**           | Laravel 11 (PHP 8.2+)               |
| **Frontend**          | Blade, TailwindCSS, Alpine.js, Vite |
| **Database**          | MySQL / MariaDB                     |
| **Payment**           | PayMongo API (GCash)                |
| **Webhook Tunneling** | Ngrok                               |
| **PDF**               | DomPDF                              |

---

## 📦 Prerequisites

Make sure to install all of these:

- **PHP** ≥ 11
- **Composer** (latest)
- **Node.js** ≥ 18.x & **npm**
- **PHPMyAdmin** (XAMPP)
- **Git**
- **Ngrok** ([Download here](https://ngrok.com/download))
- **PayMongo Account** ([Sign up](https://dashboard.paymongo.com/signup))

---

## ⚙️ Installation & Setup

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/Keith3123/yvonneForChecking.git
code .
```

### 2️⃣ Install PHP Dependencies

```bash
composer install
```

### 3️⃣ Install Node Dependencies

```bash
npm install
```

### 4️⃣ Setup Environment File

```bash
cp .env.example .env
php artisan key:generate
```

### 5️⃣ Setup Database

1. Open phpMyAdmin or your MySQL client
2. Create a new database (e.g., `yvonne_cake_shop`)
3. Import the SQL file from `database/sql/yvonne_cake_shop.sql`

### 6️⃣ Create Storage Symlink

```bash
php artisan storage:link
```

---

## 🔑 Environment Configuration

I-edit imong `.env` file:

```env
APP_NAME="Yvonne's Cake Shop"
APP_ENV=local
APP_KEY=base64:your_generated_key
APP_DEBUG=true
APP_URL=https://lustfully-payment-croak.ngrok-free.dev
```

> ⚠️ **Important**: Every time mag-restart ang ngrok (free tier), magbag-o ang URL. Update gyud sa `.env` ug sa PayMongo Dashboard.

---

## ▶️ Running the Application

You'll need **3 terminals** running simultaneously:

### 🖥️ Terminal 1 — Vite Dev Server (Frontend)

```bash
npm run dev
```

> Compiles your Tailwind CSS, JS, and hot-reloads sa browser.

### 🖥️ Terminal 2 — Laravel Server (Backend)

```bash
php artisan serve
```

> Server runs at: `http://localhost:8000`

### 🖥️ Terminal 3 — Ngrok Tunnel (For Webhooks)

```bash
ngrok http 8000
```

> Provides a public URL para ma-reach sa PayMongo ang imong webhook.

---

## 💳 PayMongo Integration

### Step 1: Get API Keys

1. Login sa [PayMongo Dashboard](https://dashboard.paymongo.com)
2. Adto sa **Developers → API Keys**
3. Copy ang **Test Mode** keys ug i-paste sa `.env`:
    - `PAYMONGO_PUBLIC_KEY`
    - `PAYMONGO_SECRET_KEY`

### Step 2: Setup Webhook

1. Adto sa **Developers → Webhooks**
2. Click **Create Webhook**
3. Fill in:
    ```
    Endpoint URL:  https://your-ngrok-url.ngrok-free.dev/paymongo/webhook
    Events:        checkout_session.payment.paid
    ```
4. Copy ang **Webhook Secret** ug i-paste sa `.env` as `PAYMONGO_WEBHOOK_SECRET`

### Step 3: Test Payment Flow

| Stage | Action                              |
| ----- | ----------------------------------- |
| 1     | Customer adds items to cart         |
| 2     | Selects GCash payment at checkout   |
| 3     | Redirected to PayMongo checkout     |
| 4     | Pays via GCash sandbox              |
| 5     | PayMongo fires webhook → updates DB |
| 6     | Customer redirected to success page |

### Test GCash Credentials (Sandbox)

| Field  | Value         |
| ------ | ------------- |
| Mobile | `09175551234` |
| MPIN   | `1234`        |
| OTP    | `123456`      |

---

## 🌐 Ngrok Setup (Webhook Tunneling)

Ngrok kay tool nga ma-expose nimo ang imong **localhost** sa internet — gikinahanglan kay ang PayMongo dili maka-reach sa `localhost:8000` directly.

### Step 1: Download Ngrok

[https://ngrok.com/download](https://ngrok.com/download)

Extract ang `ngrok.exe` sa imong project root or any folder sa `PATH`.

### Step 2: Sign Up & Get Authtoken

1. Create account sa [ngrok.com](https://ngrok.com)
2. Adto sa **Your Authtoken** page
3. Copy ang authtoken

### Step 3: Configure Authtoken

```bash
ngrok config add-authtoken <YOUR_AUTHTOKEN>
```

### Step 4: Start Tunnel

```bash
ngrok.exe http 8000
```

You'll see something like:

```
Forwarding   https://lustfully-payment-croak.ngrok-free.dev → http://localhost:8000
```

### Step 5: Update PayMongo Webhook

Every time magbag-o ang URL (free tier), i-update sa:

- `.env` → `APP_WEBHOOK_URL`
- PayMongo Dashboard → Webhooks → Edit endpoint URL

### 🔍 Ngrok Inspector

Open `http://127.0.0.1:4040` sa imong browser para makita tanan incoming requests (debugging tool).

---

## 🛠️ Common Commands

### Laravel

```bash
# Start dev server
php artisan serve

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Show all routes
php artisan route:list

# Watch logs (real-time)
php artisan pail

# Run migrations
php artisan migrate

# Create symlink for storage
php artisan storage:link
```

### Frontend

```bash
# Development (with hot reload)
npm run dev

# Production build
npm run build
```

### Ngrok

```bash
# Add authtoken (one-time setup)
ngrok config add-authtoken <YOUR_AUTHTOKEN>

# Start tunnel
ngrok.exe http 8000

# Access inspector
# http://127.0.0.1:4040
```

### Git

```bash
git pull origin main
git add .
git commit -m "your message"
git push origin main
```

---

## 📝 Notes

- **Always run all 3 terminals** (Vite, Laravel, Ngrok) when developing payment features.
- **Update webhook URL** sa PayMongo Dashboard every time mag-restart ang ngrok.
- **Test in PayMongo Sandbox** lang muna before going live.
- **Check ngrok inspector** (`http://127.0.0.1:4040`) for webhook debugging.
- **Monitor logs** with `php artisan pail` for real-time error tracking.

---

## 👨‍💻 Author

Developed for **Yvonne's Cakes & Pastries**

- **Full-Stack Programmer** - njcs11 - Jaspher Lloyd Tadlan
- **Front-End Programmer / Database Analyst** - Keith3123 - Nicole Berou
- **Front-End Programmer** - suiswei - Jan Brian Maturan

---

## 📄 License

This project is private and proprietary. All rights reserved.

---

<div align="center">

**🍰 Made with ❤️ and lots of cake 🍰**

</div>
