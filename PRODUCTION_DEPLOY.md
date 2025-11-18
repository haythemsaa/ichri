# 🚀 Production Deployment Guide - ichri.tn

## Pre-Deployment Checklist

### 1. Prerequisites ✅
- [ ] Server provisioned (minimum 4GB RAM, 2 CPUs)
- [ ] Domain name configured (ichri.tn)
- [ ] SSL certificate obtained (Let's Encrypt recommended)
- [ ] Database server setup (MySQL 8.0+)
- [ ] Redis server setup
- [ ] ElasticSearch server setup (optional for MVP)
- [ ] S3 or equivalent object storage
- [ ] SMTP email service
- [ ] SMS service (Twilio) account
- [ ] Payment gateway accounts
- [ ] Monitoring tools account (Sentry, etc.)

### 2. Environment Variables ✅
Copy `.env.example` to `.env` and configure ALL production values:

**Critical Settings:**
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.ichri.tn

DB_CONNECTION=mysql
DB_HOST=<production-db-host>
DB_DATABASE=ichri_production
DB_USERNAME=<secure-username>
DB_PASSWORD=<strong-password>

REDIS_HOST=<redis-host>
REDIS_PASSWORD=<redis-password>

JWT_SECRET=<generate-new-secret>

TWILIO_SID=<your-sid>
TWILIO_AUTH_TOKEN=<your-token>

AWS_ACCESS_KEY_ID=<your-key>
AWS_SECRET_ACCESS_KEY=<your-secret>
AWS_BUCKET=ichri-production

FLOUCI_APP_TOKEN=<production-token>
FLOUCI_SANDBOX=false
```

---

## Deployment Steps

### Step 1: Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y git nginx mysql-client redis-tools curl

# Install Docker & Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### Step 2: Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/haythemsaa/ichri.git
sudo chown -R $USER:$USER ichri
cd ichri
```

### Step 3: Configure Environment

```bash
# Backend
cd backend
cp .env.example .env
nano .env  # Edit with production values
cd ..
```

### Step 4: Build and Start Services

```bash
# Build Docker images
docker-compose -f docker-compose.prod.yml build

# Start services
docker-compose -f docker-compose.prod.yml up -d

# Wait for services to be ready
sleep 30
```

### Step 5: Setup Application

```bash
# Install dependencies
docker-compose exec backend composer install --no-dev --optimize-autoloader

# Generate keys
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan jwt:secret

# Run migrations
docker-compose exec backend php artisan migrate --force

# Seed initial data (admin user, roles, etc.)
docker-compose exec backend php artisan db:seed --class=ProductionSeeder

# Optimize
docker-compose exec backend php artisan config:cache
docker-compose exec backend php artisan route:cache
docker-compose exec backend php artisan view:cache
docker-compose exec backend php artisan optimize
```

### Step 6: Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/ichri.tn
```

**Nginx Configuration:**
```nginx
# API Backend
server {
    listen 80;
    listen [::]:80;
    server_name api.ichri.tn;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.ichri.tn;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/api.ichri.tn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.ichri.tn/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Proxy to Backend
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 90;
    }

    # Logs
    access_log /var/log/nginx/ichri-api-access.log;
    error_log /var/log/nginx/ichri-api-error.log;
}

# Web Dashboard
server {
    listen 80;
    listen [::]:80;
    server_name ichri.tn www.ichri.tn;
    
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ichri.tn www.ichri.tn;

    ssl_certificate /etc/letsencrypt/live/ichri.tn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ichri.tn/privkey.pem;

    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    access_log /var/log/nginx/ichri-web-access.log;
    error_log /var/log/nginx/ichri-web-error.log;
}
```

Enable and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/ichri.tn /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 7: SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d api.ichri.tn -d ichri.tn -d www.ichri.tn
sudo certbot renew --dry-run
```

### Step 8: Setup Cron Jobs

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/ichri && docker-compose exec -T backend php artisan schedule:run >> /dev/null 2>&1

# Add backup script
0 2 * * * /var/www/ichri/scripts/backup.sh
```

### Step 9: Setup Monitoring

```bash
# Install monitoring agent (example: New Relic, Datadog)

# Configure log rotation
sudo nano /etc/logrotate.d/ichri
```

**Log Rotation Config:**
```
/var/log/nginx/ichri-*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    sharedscripts
    postrotate
        [ -f /var/run/nginx.pid ] && kill -USR1 `cat /var/run/nginx.pid`
    endscript
}
```

---

## Post-Deployment Verification

### Health Checks

```bash
# 1. API Health
curl https://api.ichri.tn/api/v1/health

# 2. Database Connection
docker-compose exec backend php artisan db:monitor

# 3. Redis Connection
docker-compose exec backend php artisan redis:ping

# 4. Queue Workers
docker-compose ps | grep queue

# 5. Scheduled Tasks
docker-compose exec backend php artisan schedule:list
```

### Performance Tests

```bash
# Load testing with Apache Bench
ab -n 1000 -c 10 https://api.ichri.tn/api/v1/catalog/products

# Expected: 90%+ requests should be < 200ms
```

### Security Audit

```bash
# SSL Test
sslscan api.ichri.tn

# Security Headers
curl -I https://api.ichri.tn

# Penetration testing (use professional tools)
```

---

## Monitoring & Maintenance

### Daily Tasks
- [ ] Check error logs
- [ ] Monitor API response times
- [ ] Check database performance
- [ ] Review failed jobs queue

### Weekly Tasks
- [ ] Review application metrics
- [ ] Check disk space
- [ ] Verify backups
- [ ] Update dependencies (security patches)

### Monthly Tasks
- [ ] Full security audit
- [ ] Performance optimization
- [ ] Database optimization
- [ ] Review scaling needs

---

## Backup Strategy

### Database Backups

Create `/var/www/ichri/scripts/backup.sh`:
```bash
#!/bin/bash

BACKUP_DIR="/var/backups/ichri"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup MySQL
docker-compose exec -T mysql mysqldump -u root -p$MYSQL_ROOT_PASSWORD ichri_production | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz backend/storage/app

# Upload to S3
aws s3 cp $BACKUP_DIR/db_$DATE.sql.gz s3://ichri-backups/
aws s3 cp $BACKUP_DIR/storage_$DATE.tar.gz s3://ichri-backups/

# Keep only last 7 days locally
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
```

Make executable:
```bash
chmod +x /var/www/ichri/scripts/backup.sh
```

---

## Scaling Strategy

### Horizontal Scaling

**1. Load Balancer Setup:**
- Use Nginx or HAProxy
- Configure multiple backend servers
- Session handling with Redis

**2. Database Scaling:**
- Master-Slave replication
- Read replicas for heavy read operations
- Connection pooling

**3. Cache Layer:**
- Redis cluster for high availability
- CDN for static assets
- Full-page caching for public pages

**4. Queue Workers:**
- Multiple queue workers
- Supervisor for process management
- Horizon for queue monitoring

### Vertical Scaling
- Upgrade server resources as needed
- Optimize database queries
- Add more Redis memory

---

## Troubleshooting

### Application Not Responding
```bash
# Check services
docker-compose ps

# Check logs
docker-compose logs -f backend

# Restart services
docker-compose restart backend
```

### Database Connection Issues
```bash
# Test connection
docker-compose exec backend php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL
docker-compose exec mysql mysql -u root -p -e "SHOW PROCESSLIST;"
```

### High CPU Usage
```bash
# Check processes
docker stats

# Optimize application
docker-compose exec backend php artisan optimize:clear
docker-compose exec backend php artisan config:cache
```

---

## Rollback Procedure

If deployment fails:

```bash
# 1. Stop new version
docker-compose down

# 2. Restore database backup
zcat /var/backups/ichri/db_YYYYMMDD_HHMMSS.sql.gz | docker-compose exec -T mysql mysql -u root -p ichri_production

# 3. Checkout previous version
git checkout <previous-tag>

# 4. Restart services
docker-compose up -d

# 5. Verify
curl https://api.ichri.tn/api/v1/health
```

---

## Support Contacts

- **Technical Lead**: tech@ichri.tn
- **DevOps**: devops@ichri.tn
- **Emergency**: +216 XX XXX XXX

---

**🚀 Production Deployment Complete!**

**Monitor everything for the first 48 hours!**
