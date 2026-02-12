# 🚀 Easypanel Deployment Guide

## 📋 Ön Hazırlık

1. GitHub'a projeyi push'layın
2. Easypanel hesabınıza giriş yapın
3. Yeni bir proje oluşturun

## 🎯 Easypanel'de Deployment

### Adım 1: Yeni Servis Oluştur

1. Easypanel Dashboard → **New Service** → **From Source**
2. **GitHub** seçin ve repo'nuzu bağlayın
3. Service type: **Docker Compose**
4. Compose file: `docker-compose.easypanel.yml`

### Adım 2: Environment Variables

Easypanel UI'da şu environment variable'ları ekleyin:

#### 🔴 Zorunlu (Mutlaka Ayarlayın)

```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_URL=https://your-domain.com
DB_PASSWORD=güçlü-database-şifresi-buraya
JWT_SECRET=çok-uzun-ve-random-bir-string-buraya
```

**APP_KEY Oluşturma:**
```bash
# Local'de çalıştırın
php artisan key:generate --show
# veya
echo "base64:$(openssl rand -base64 32)"
```

**JWT_SECRET Oluşturma:**
```bash
openssl rand -base64 64
```

#### 🟡 Önerilen (Mail için gerekli)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME=FFO Backend
```

#### 🟢 Opsiyonel (Varsayılan değerler var)

```env
APP_NAME=FFO Backend
APP_ENV=production
APP_DEBUG=false
DB_DATABASE=ffo
DB_USERNAME=ffo_user
REDIS_PASSWORD=redis-şifresi-opsiyonel
```

### Adım 3: Domain Ayarları

1. Easypanel'de **Domains** sekmesine gidin
2. Domain ekleyin: `your-domain.com`
3. **Enable SSL** (Let's Encrypt otomatik)
4. DNS ayarlarını yapın:
   ```
   A Record: your-domain.com → Easypanel IP
   ```

### Adım 4: Deploy!

1. **Deploy** butonuna tıklayın
2. İlk deploy 3-5 dakika sürebilir (composer install vs.)
3. Logs'u takip edin

## ✅ Deployment Kontrolü

### Health Check
```bash
curl https://your-domain.com/health
# Dönüş: healthy
```

### API Test
```bash
curl https://your-domain.com/api
# Dönüş: {"status":"ok"}
```

## 🔄 Güncelleme (Update)

Easypanel'de 3 yöntem var:

### Yöntem 1: Otomatik (Önerilen)
1. GitHub'a push yapın
2. Easypanel otomatik deploy eder (webhook varsa)

### Yöntem 2: Manuel
1. Easypanel Dashboard → **Deployments**
2. **Deploy Latest** butonuna tıklayın

### Yöntem 3: CLI
```bash
# Easypanel CLI ile (eğer yüklüyse)
easypanel deploy your-service-name
```

## 📊 Monitoring

### Logs Görüntüleme

Easypanel Dashboard'da:
1. **Logs** sekmesi → Service seçin
2. Real-time logs görürsünüz

Veya CLI ile:
```bash
easypanel logs your-service-name --follow
```

### Resource Kullanımı

Dashboard'da **Metrics** bölümünden:
- CPU kullanımı
- Memory kullanımı
- Network trafiği
- Disk kullanımı

## 🛠️ Yararlı Komutlar

### Easypanel CLI Komutları

```bash
# Service restart
easypanel restart your-service-name

# Logs
easypanel logs your-service-name

# Shell'e bağlan
easypanel shell your-service-name app

# Backup
easypanel backup create your-service-name
```

### Container İçinde Komut Çalıştırma

Easypanel'de **Terminal** sekmesinden:

```bash
# Migration
php artisan migrate --force

# Cache temizle
php artisan cache:clear

# User oluştur
php artisan tinker
```

## 🔐 Güvenlik

### Firewall (Easypanel otomatik yönetir)
- ✅ Sadece 80/443 portları açık
- ✅ Internal network izole
- ✅ DDoS koruması

### SSL/HTTPS
- ✅ Let's Encrypt otomatik
- ✅ Auto-renewal
- ✅ HTTPS redirect otomatik

### Database
- ✅ Internal network'te
- ✅ External erişim kapalı
- ✅ Encrypted backup

## 💾 Backup

### Otomatik Backup (Easypanel Pro)
1. **Backups** sekmesi
2. **Enable Auto Backup**
3. Backup frequency: Daily/Weekly
4. Retention: 30 days

### Manuel Backup

**Database:**
```bash
# Easypanel terminal'den
pg_dump -U ffo_user ffo > backup.sql
```

**Volumes:**
Easypanel Dashboard → **Volumes** → **Create Snapshot**

## 🚨 Sorun Giderme

### Deployment Başarısız

1. **Logs'u kontrol edin:**
   - Easypanel Dashboard → Logs
   - Kırmızı error mesajlarına bakın

2. **Environment variables kontrol:**
   - APP_KEY, DB_PASSWORD, JWT_SECRET set mi?
   - Boşluk veya özel karakter var mı?

3. **Docker Compose syntax:**
   ```bash
   # Local'de test edin
   docker-compose -f docker-compose.easypanel.yml config
   ```

### Container Çalışmıyor

1. **Health check:**
   ```bash
   curl https://your-domain.com/health
   ```

2. **Service restart:**
   - Dashboard → **Restart**

3. **Rebuild:**
   - Dashboard → **Rebuild**

### Database Bağlantı Hatası

1. **Environment kontrol:**
   - DB_PASSWORD doğru mu?
   - DB_DATABASE ve DB_USERNAME doğru mu?

2. **Database durumu:**
   - Dashboard → Services → db → Logs

3. **Migration:**
   ```bash
   # Terminal'den
   php artisan migrate --force
   ```

### 502 Bad Gateway

1. **App container durumu:**
   - Dashboard → Services → app → Status

2. **PHP-FPM çalışıyor mu:**
   ```bash
   # Terminal'den
   ps aux | grep php-fpm
   ```

3. **Nginx config:**
   - Logs'da syntax error var mı?

## 📈 Performance Optimization

### Redis Cache Aktif mi?

```bash
# Terminal'den kontrol
redis-cli ping
# Dönüş: PONG
```

### OPcache Status

```bash
# Terminal'den
php -i | grep opcache
```

### Application Cache

```bash
# Optimize edin
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🎯 Easypanel Avantajları

✅ **Otomatik SSL**: Let's Encrypt ücretsiz
✅ **Auto-scaling**: Traffic'e göre
✅ **Zero-downtime deploy**: Rolling update
✅ **Built-in monitoring**: Metrics + Alerts
✅ **One-click backup**: Volume snapshots
✅ **Git integration**: Auto-deploy on push
✅ **Environment management**: UI'dan kolay
✅ **Container logs**: Real-time izleme

## 🔗 Faydalı Linkler

- [Easypanel Docs](https://easypanel.io/docs)
- [Docker Compose Reference](https://docs.docker.com/compose/)
- [PostgreSQL in Docker](https://hub.docker.com/_/postgres)

## 📞 Destek

Easypanel Support:
- Discord: https://discord.gg/easypanel
- Email: support@easypanel.io
- Docs: https://easypanel.io/docs

---

**İlk deployment'tan sonra:**
1. ✅ Health check yapın: `curl https://your-domain.com/health`
2. ✅ API test edin: `curl https://your-domain.com/api`
3. ✅ SSL çalışıyor mu: `https://` ile erişin
4. ✅ Logs'u kontrol edin
5. ✅ Backup ayarlarını aktifleştirin

🎉 **Başarılı bir deployment!**
