# 🚀 FFO Backend - Production Deployment Guide

## 📋 Ön Gereksinimler

Sunucunuzda şunlar yüklü olmalı:
- Docker (20.10+)
- Docker Compose (2.0+)
- Git

## 🎯 İlk Kurulum (Sunucuda)

### 1. Projeyi Clone'layın

```bash
git clone <your-repo-url> ffo-backend
cd ffo-backend
```

### 2. Environment Ayarları

```bash
# .env.production dosyasını .env olarak kopyalayın
cp .env.production .env

# Gerekli değerleri düzenleyin
nano .env
```

**Mutlaka değiştirilmesi gerekenler:**
```env
APP_URL=https://your-domain.com
DB_PASSWORD=güçlü-bir-şifre
REDIS_PASSWORD=güçlü-bir-şifre  # opsiyonel
JWT_SECRET=uzun-random-string
MAIL_* (mail ayarları)
SESSION_DOMAIN=.your-domain.com
```

### 3. Deploy Script'i Çalıştırın

```bash
chmod +x deploy.sh
./deploy.sh
```

Script otomatik olarak:
- ✅ Gereksinimleri kontrol eder
- ✅ Dizinleri oluşturur
- ✅ Docker image'larını build eder
- ✅ Container'ları başlatır
- ✅ Migration'ları çalıştırır
- ✅ Cache'leri optimize eder

## 🔄 Güncelleme (Update/Redeploy)

```bash
# Kodu güncelleyin
git pull origin main

# Container'ları yeniden build edin
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Migration'ları çalıştırın
docker-compose exec app php artisan migrate --force

# Cache'leri yenileyin
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

Veya tek komutla:
```bash
./deploy.sh
```

## 🌐 Domain Yapılandırması

### Nginx Reverse Proxy (Önerilen)

Sunucunuzda ana nginx yoksa, direkt domain'i Docker nginx'e yönlendirin.

Varsa, reverse proxy ayarı:

```nginx
# /etc/nginx/sites-available/ffo-backend
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### SSL/HTTPS (Önerilen)

**Certbot ile Let's Encrypt:**

```bash
# Certbot yükleyin
sudo apt-get install certbot

# Sertifika alın
sudo certbot certonly --standalone -d your-domain.com

# Sertifikaları Docker'a kopyalayın
sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem docker/nginx/ssl/

# nginx.conf'a SSL ekleyin (örnek aşağıda)
```

**SSL için nginx.conf güncellemesi:**

`docker/nginx/default.conf` dosyasına ekleyin:

```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # ... diğer ayarlar aynı
}

# HTTP'den HTTPS'e yönlendirme
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

## 🛠️ Yararlı Komutlar

### Container Yönetimi

```bash
# Container'ları başlat
docker-compose up -d

# Container'ları durdur
docker-compose down

# Container'ları yeniden başlat
docker-compose restart

# Belirli bir service'i restart et
docker-compose restart app

# Logları izle
docker-compose logs -f
docker-compose logs -f app
docker-compose logs -f webserver

# Container durumunu kontrol et
docker-compose ps
```

### Laravel Komutları

```bash
# Migration çalıştır
docker-compose exec app php artisan migrate --force

# Cache temizle
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Cache oluştur (production için önemli)
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Queue worker başlat (eğer kullanıyorsanız)
docker-compose exec app php artisan queue:work --daemon

# Tinker (Laravel console)
docker-compose exec app php artisan tinker

# Container içine gir
docker-compose exec app bash
```

### Database Yönetimi

```bash
# PostgreSQL'e bağlan
docker-compose exec db psql -U ffo_user -d ffo

# Database backup
docker-compose exec db pg_dump -U ffo_user ffo > backup.sql

# Database restore
docker-compose exec -T db psql -U ffo_user -d ffo < backup.sql

# Database'i sıfırla (DİKKAT!)
docker-compose down -v
docker-compose up -d
```

### Monitoring

```bash
# Resource kullanımı
docker stats

# Container sağlık durumu
docker-compose ps

# Health check
curl http://localhost/health
# veya
curl https://your-domain.com/health
```

## 🔐 Güvenlik Önerileri

1. **Firewall Ayarları:**
   ```bash
   # Sadece gerekli portları açın
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw allow 22/tcp  # SSH
   sudo ufw enable
   ```

2. **SSH Key ile Giriş:**
   - Password authentication'ı kapatın
   - Sadece SSH key ile erişim verin

3. **Database Güvenliği:**
   - Güçlü şifreler kullanın
   - External port'u kapatabilirsiniz (5432)
   - Sadece localhost'tan erişim

4. **Regular Updates:**
   ```bash
   # Sistem güncellemeleri
   sudo apt update && sudo apt upgrade -y
   
   # Docker image'ları güncelle
   docker-compose pull
   docker-compose up -d
   ```

5. **Backup Strategy:**
   - Günlük database backup
   - `.env` dosyasını güvenli yerde saklayın
   - Storage klasörünü yedekleyin

## 📊 Monitoring & Logging

### Log Dosyaları

```bash
# Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log

# Nginx logs
docker-compose exec webserver tail -f /var/log/nginx/error.log
docker-compose exec webserver tail -f /var/log/nginx/access.log
```

### Health Check

API health endpoint:
```bash
curl https://your-domain.com/health
```

## 🚨 Sorun Giderme

### Container çalışmıyor

```bash
# Logları kontrol edin
docker-compose logs app

# Container'ı yeniden başlatın
docker-compose restart app

# Tamamen yeniden build
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database bağlantı hatası

```bash
# Database container durumunu kontrol et
docker-compose ps db

# Database loglarını incele
docker-compose logs db

# .env dosyasındaki DB ayarlarını kontrol et
cat .env | grep DB_
```

### Permission hataları

```bash
# Storage ve cache klasörlerine yetki ver
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Composer dependencies

```bash
# Dependencies'i güncelle
docker-compose exec app composer install --optimize-autoloader --no-dev
```

## 📝 Environment Variables Reference

| Variable | Description | Example |
|----------|-------------|---------|
| APP_URL | Domain URL | https://api.example.com |
| DB_PASSWORD | Database password | StrongPassword123! |
| JWT_SECRET | JWT encryption key | Random64CharString |
| REDIS_PASSWORD | Redis password (optional) | RedisPass123 |
| MAIL_HOST | SMTP server | smtp.gmail.com |
| SESSION_DOMAIN | Cookie domain | .example.com |

## 🆘 Destek

Sorun yaşıyorsanız:
1. Logları kontrol edin: `docker-compose logs -f`
2. Container durumunu kontrol edin: `docker-compose ps`
3. Health check yapın: `curl http://localhost/health`

## 📚 Ek Kaynaklar

- [Docker Documentation](https://docs.docker.com/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
