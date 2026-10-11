# Rush Fitness Gym

Laravel-based gym membership, coaching, payment review, QR attendance, scheduling, notifications, and private chat portal.

## Local development

Requirements: PHP 8.3+, Composer, Node.js, and MySQL/MariaDB.

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

The development seeder creates demonstration accounts with known passwords. It is deliberately blocked when `APP_ENV=production`.

For live mobile-camera testing, run `composer run mobile` and open the temporary HTTPS URL shown in the terminal. The tunnel is for development only and exposes the local login page while it is running.

## Production deployment

1. Provision a permanent HTTPS domain, PHP 8.3+, MySQL/MariaDB, and persistent object or server storage.
2. Copy `.env.production.example` to `.env` and replace every placeholder. Never commit `.env`.
3. Configure a real SMTP provider so sign-in and password-reset codes are delivered.
4. Build and prepare the application:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate
php artisan migrate --force
php artisan app:create-admin admin@example.com --name="Rush Fitness Admin"
php artisan app:deployment-check
php artisan optimize
```

Do not run `php artisan migrate:fresh` or `php artisan db:seed` in production.

Serve the `public` directory through Nginx or Apache. Do not use `php artisan serve` in production.

### Required background process

Configure the host scheduler to run Laravel every minute:

```cron
* * * * * cd /var/www/rush-fitness-gym && php artisan schedule:run >> /dev/null 2>&1
```

This updates expired subscriptions and sends three-day expiration alerts. If queued jobs are introduced later, also run `php artisan queue:work` under Supervisor or the hosting provider's worker service.

### Storage and backups

- Ephemeral/container hosting: configure `FILESYSTEM_DISK=s3` and the AWS-compatible credentials.
- Persistent VPS storage: `FILESYSTEM_DISK=local` is supported, but `storage/app/private` must be included in backups.
- Back up the production database and private uploads daily. Test restoration before launch and periodically afterward.
- Keep database and object-storage retention/versioning enabled where supported.

### Release procedure

```bash
php artisan down --retry=30
git pull --ff-only
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan app:deployment-check
php artisan up
```

Test login, password recovery, payment receipt review, chat/media, QR download, QR attendance scanning, and scheduler output after each release. Deploy to staging before production whenever database migrations are included.

## Verification

```powershell
php artisan test
composer audit --locked
npm audit
npm run build
vendor\bin\pint --test
```
