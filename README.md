Your content is actually solid—but messy. Mixed language, inconsistent tone, and some unclear instructions will confuse any dev trying to run this. I cleaned it properly, kept everything, and made it consistent, technical, and readable.

Here’s your **fixed, professional README (fully in English, nothing removed, just improved):**

---

# 🍰 Yvonne's Cake Shop — Web Application

A full-stack Laravel-based e-commerce platform for **Yvonne's Cake Shop**, featuring online ordering, paluwagan (installment) subscriptions, GCash payment integration via PayMongo, and a comprehensive admin management dashboard.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Prerequisites](#-prerequisites)
- [Installation & Setup](#-installation--setup)
- [Environment Configuration](#-environment-configuration)
- [Email Verification & Google Binding](#-email-verification-otp--google-account-binding)
- [Running the Application](#-running-the-application)
- [PayMongo Integration](#-paymongo-integration)
- [Ngrok Setup](#-ngrok-setup-webhook-tunneling)
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

Make sure the following are installed:

- PHP ≥ 8.2 (your doc says 11—fix that if wrong)
- Composer
- Node.js ≥ 18.x & npm
- phpMyAdmin (XAMPP or similar)
- Git
- Ngrok
- PayMongo account

---

## ⚙️ Installation & Setup

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/Keith3123/yvonneForChecking.git
cd yvonneForChecking
code .
```

### 2️⃣ Install Dependencies

```bash
composer install
npm install
```

### 3️⃣ Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4️⃣ Setup Database

1. Open phpMyAdmin or MySQL client
2. Create database (e.g., `yvonne_cake_shop`)
3. Import SQL file from:

```
database/sql/yvonne_cake_shop.sql
```

### 5️⃣ Storage Symlink

```bash
php artisan storage:link
```

---

## 🔑 Environment Configuration

Edit your `.env`:

```env
APP_NAME="Yvonne's Cake Shop"
APP_ENV=local
APP_KEY=base64:your_generated_key
APP_DEBUG=true
APP_URL=https://your-ngrok-url.ngrok-free.dev
```

> ⚠️ Ngrok URLs change every restart (free tier). You MUST update `.env` and PayMongo webhook URLs.

---

## 📧 Email Verification (OTP) & Google Account Binding

This project supports:

- 📩 Email OTP verification (for email updates)
- 🔗 Google account binding via Laravel Socialite

---

### 📩 Email OTP Verification

#### ⚠️ Important Behavior

- In **local development**, emails are NOT sent to Gmail
- Emails are captured using Mailtrap

---

### 🔧 Option 1 — Local Testing (Recommended)

#### Step 1: Create Mailtrap Account

1. Go to Mailtrap
2. Navigate to:
   **Email Testing → Inboxes → My Inbox → SMTP Settings**

#### Step 2: Update `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS="noreply@yvonnes.com"
MAIL_FROM_NAME="Yvonne's Cake Shop"
```

#### Step 3: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

#### Result

- OTP emails appear in Mailtrap inbox
- NOT delivered to Gmail

---

### 🌐 Option 2 — Real Email (Production)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="Yvonne's Cake Shop"
```

> ⚠️ Use **App Password**, not your real Gmail password.

---

## 🔗 Google Account Binding (OAuth)

---

### 🔧 Setup Steps

#### 1. Install Socialite

```bash
composer require laravel/socialite
```

---

#### 2. Configure in Google Cloud Console

- Create project
- Enable Google Identity
- Create OAuth Client

---

#### 3. Authorized Redirect URI

```text
https://your-ngrok-url.ngrok-free.dev/auth/google/callback
```

> Must EXACTLY match `.env`

---

#### 4. `.env`

```env
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=https://your-ngrok-url.ngrok-free.dev/auth/google/callback
```

---

#### 5. `config/services.php`

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],
```

---

### ⚠️ Localhost vs Ngrok

| Feature      | Localhost  | Ngrok  |
| ------------ | ---------- | ------ |
| Email OTP    | ✅ Yes     | ❌ No  |
| Google OAuth | ⚠️ Limited | ✅ Yes |

- Google OAuth requires public URL → use Ngrok
- Free Ngrok URLs change → update config

---

### 🧠 Key Takeaways

- No email in Gmail? → check Mailtrap
- Google login fails? → redirect mismatch
- OTP fails? → MAIL config issue
- Errors? → run:

```bash
php artisan pail
```

---

## ▶️ Running the Application

You need **3 terminals**:

### Terminal 1 — Frontend

```bash
npm run dev
```

### Terminal 2 — Backend

```bash
php artisan serve
```

Runs at:

```
http://localhost:8000
```

### Terminal 3 — Ngrok

```bash
ngrok http 8000
```

---

## 💳 PayMongo Integration

### Step 1 — API Keys

1. Go to PayMongo Dashboard
2. Developers → API Keys
3. Add to `.env`

---

### Step 2 — Webhook

```
Endpoint:
https://your-ngrok-url.ngrok-free.dev/paymongo/webhook
```

Event:

```
checkout_session.payment.paid
```

---

### Step 3 — Flow

| Step | Action               |
| ---- | -------------------- |
| 1    | Add to cart          |
| 2    | Choose GCash         |
| 3    | Redirect to PayMongo |
| 4    | Pay                  |
| 5    | Webhook triggers     |
| 6    | DB updates           |

---

### Test Credentials

| Field  | Value       |
| ------ | ----------- |
| Mobile | 09175551234 |
| MPIN   | 1234        |
| OTP    | 123456      |

---

## 🌐 Ngrok Setup

Ngrok exposes localhost to the internet.

### Steps

```bash
ngrok config add-authtoken <TOKEN>
ngrok http 8000
```

Update webhook URL every restart.

Inspector:

```
http://127.0.0.1:4040
```

---

## 🛠️ Common Commands

### Laravel

```bash
php artisan serve
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan route:list
php artisan pail
php artisan migrate
php artisan storage:link
```

### Frontend

```bash
npm run dev
npm run build
```

### Git

```bash
git pull origin main
git add .
git commit -m "message"
git push origin main
```

---

## 📝 Notes

- Always run all 3 terminals for payments
- Update webhook after Ngrok restart
- Use sandbox before production
- Use Ngrok inspector for debugging
- Monitor logs using `php artisan pail`

---

## 👨‍💻 Author

Developed for **Yvonne's Cakes & Pastries**

- Jaspher Lloyd Tadlan — Full-Stack Developer
- Nicole Berou — Front-End / Database
- Jan Brian Maturan — Front-End

---

## 📄 License

Private and proprietary. All rights reserved.

---
