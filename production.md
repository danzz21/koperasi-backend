# 🚀 Production Deployment Guide — Koperasi K-Samara

Panduan lengkap untuk deploy sistem Koperasi Simpan Pinjam ke lingkungan production.

---

## 📋 Persyaratan Server

| Komponen | Versi Minimum | Rekomendasi |
|---|---|---|
| PHP | 8.3 | 8.3 LTS |
| Laravel | 13.x | 13.x |
| MySQL | 8.0 | 8.0+ |
| Node.js | 18.x | 20 LTS |
| Web Server | Nginx | Nginx 1.24+ |
| OS | Ubuntu 22.04 | Ubuntu 24.04 LTS |
| RAM | 1 GB | 2 GB+ |
| Storage | 10 GB | 20 GB+ |

---

## ⚙️ Konfigurasi `.env` Production

```env
APP_NAME="Koperasi K-Samara"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_VIA_ARTISAN
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koperasi_db
DB_USERNAME=koperasi_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Midtrans PRODUCTION
MIDTRANS_ENABLED=true
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY=Mid-server-PRODUCTION_KEY
MIDTRANS_CLIENT_KEY=Mid-client-PRODUCTION_KEY
MIDTRANS_SNAP_URL=https://app.midtrans.com/snap/v1/transactions
MIDTRANS_SNAP_JS=https://app.midtrans.com/snap/snap.js
MIDTRANS_API_URL=https://api.midtrans.com/v2

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=MAIL_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Koperasi K-Samara"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

---

## 🛠️ Langkah Deploy Backend (Laravel)

### 1. Clone & Install Dependencies

```bash
git clone https://github.com/yourorg/koperasi-backend.git /var/www/koperasi-backend
cd /var/www/koperasi-backend

composer install --optimize-autoloader --no-dev
```

### 2. Konfigurasi Environment

```bash
cp .env.example .env
nano .env   # Isi semua variabel sesuai section di atas
php artisan key:generate
```

### 3. Database & Migrasi

```bash
php artisan migrate --force
php artisan db:seed --class=SystemConfigSeeder   # opsional
```

### 4. Optimasi & Cache

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
php artisan filament:cache-components
```

### 5. Set Permission

```bash
chown -R www-data:www-data /var/www/koperasi-backend
chmod -R 775 storage bootstrap/cache
```

### 6. Queue Worker (Supervisor)

Buat file `/etc/supervisor/conf.d/koperasi-worker.conf`:

```ini
[program:koperasi-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/koperasi-backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/koperasi-worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start koperasi-worker:*
```

### 7. Scheduler (Crontab)

```bash
crontab -e -u www-data
# Tambahkan:
* * * * * cd /var/www/koperasi-backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🌐 Konfigurasi Nginx

### Backend API

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;

    root /var/www/koperasi-backend/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.yourdomain.com/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 20M;
}
```

### Frontend PWA (React)

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    root /var/www/koperasi-frontend/dist;
    index index.html;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache assets statis
    location ~* \.(js|css|png|jpg|svg|ico|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## 🔧 Deploy Frontend (React + Vite)

```bash
cd /var/www/koperasi-frontend

# Install deps
npm ci

# Set env production
echo "VITE_BACKEND_URL=https://api.yourdomain.com" > .env.production

# Build
npm run build

# Copy dist ke web root
cp -r dist/* /var/www/koperasi-frontend-public/
```

---

## 🔒 SSL Certificate (Let's Encrypt)

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d yourdomain.com -d api.yourdomain.com
# Auto-renew sudah otomatis via cron
```

---

## 🔄 Update / Rolling Deploy

```bash
cd /var/www/koperasi-backend

git pull origin main

composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

supervisorctl restart koperasi-worker:*
```

---

## 📊 Monitoring & Log

| Log File | Isi |
|---|---|
| `storage/logs/laravel.log` | Error aplikasi |
| `/var/log/nginx/error.log` | Error web server |
| `/var/log/koperasi-worker.log` | Queue worker |
| `/var/log/mysql/error.log` | Database errors |

```bash
# Pantau log real-time
tail -f storage/logs/laravel.log

# Pantau queue
php artisan queue:monitor
```

---

## 🧪 Health Check

Setelah deploy, verifikasi:

```bash
# API health
curl https://api.yourdomain.com/api/ping

# Route cache check
php artisan route:list --name=filament.admin

# Database connection
php artisan migrate:status
```

---

## ⚠️ Checklist Pre-Launch

- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_ENV=production`
- [ ] Midtrans key sudah ganti ke **production** (bukan sandbox)
- [ ] SSL aktif & auto-renew
- [ ] Queue worker berjalan via supervisor
- [ ] Scheduler cron terpasang
- [ ] Backup database otomatis dikonfigurasi
- [ ] File `storage/` dapat ditulis oleh `www-data`
- [ ] Notifikasi Midtrans webhook URL sudah didaftarkan di dashboard Midtrans
- [ ] CORS di `config/cors.php` sudah sesuai domain production
