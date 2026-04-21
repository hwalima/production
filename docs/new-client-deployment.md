# New Client Deployment Guide
**MyMine — Adding a New Production Server**

Use this guide every time you onboard a new client. Follow the steps in order.

---

## Prerequisites

- Client has a domain (e.g. `mining.clientname.com`)
- Client has hosting — cPanel **or** DirectAdmin
- You have the cPanel/DirectAdmin login credentials
- You have access to the GitHub repo (`hwalima/production`)

---

## Part 1 — Server-Side Setup (do this in the hosting control panel terminal)

### 1.1 Open the terminal

- **cPanel**: Home → Advanced → Terminal
- **DirectAdmin**: Left sidebar → "Terminal" (under Extra Features or similar)

### 1.2 Check PHP and Git are available

```bash
php -v && git --version && echo "=READY="
```

You need PHP 8.2+ and Git. If `=READY=` prints, continue.

### 1.3 Generate a deploy key for GitHub

```bash
ssh-keygen -t ed25519 -C "clientname.domain.com" -f ~/.ssh/clientname_deploy -N ""
cat ~/.ssh/clientname_deploy.pub
```

Copy the full output (one line starting with `ssh-ed25519 AAAA...`).

### 1.4 Add the deploy key to GitHub

1. Go to **github.com → hwalima/production → Settings → Deploy keys → Add deploy key**
2. Title: `clientname.domain.com`
3. Paste the key
4. Leave "Allow write access" **unchecked**
5. Click **Add key**

### 1.5 Configure SSH to use the deploy key

```bash
echo -e "Host github.com\n  IdentityFile ~/.ssh/clientname_deploy\n  StrictHostKeyChecking no" >> ~/.ssh/config
chmod 600 ~/.ssh/config
```

### 1.6 Create the database

In the hosting control panel (MySQL Databases section):
- Create database: e.g. `username_clientname`
- Create user: e.g. `username_clientname`
- Set a strong password
- Grant user full permissions on the database

> **DirectAdmin note:** The database and username are always prefixed with the hosting account username automatically (e.g. `hwalimad_clientname`).

### 1.7 Clone the repository

**cPanel** (web root is `public_html`):
```bash
git clone git@github.com:hwalima/production.git ~/public_html/clientname
```

**DirectAdmin** (web root is `domains/clientname.domain.com/public_html`):
```bash
git clone git@github.com:hwalima/production.git ~/domains/clientname.domain.com/laravel
```

### 1.8 Wire up the web root (DirectAdmin only)

DirectAdmin serves `public_html` as the web root, but Laravel's entry point is in `public/`. Symlink them:

```bash
rm -rf ~/domains/clientname.domain.com/public_html
ln -s ~/domains/clientname.domain.com/laravel/public ~/domains/clientname.domain.com/public_html
```

> **cPanel**: The clone goes directly into `public_html/clientname` and the domain's document root should point to `.../clientname/public`. Set this in cPanel → Addon Domains.

### 1.9 Configure the .env file

```bash
cd ~/domains/clientname.domain.com/laravel   # DirectAdmin
# or
cd ~/public_html/clientname                  # cPanel

cp .env.example .env

sed -i "s|APP_NAME=.*|APP_NAME=\"Client Company Name\"|" .env
sed -i "s|APP_URL=.*|APP_URL=https://clientname.domain.com|" .env
sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|DB_HOST=.*|DB_HOST=localhost|" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=username_clientname|" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=username_clientname|" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=THE_DB_PASSWORD|" .env
sed -i "s|DEPLOY_SECRET=.*|DEPLOY_SECRET=clientname-deploy-2026|" .env

# Verify
grep -E "APP_NAME|APP_URL|APP_ENV|APP_DEBUG|DB_|DEPLOY_SECRET" .env
```

> Choose a unique `DEPLOY_SECRET` per client — you will use it in GitHub in Part 2.

---

## Part 2 — Install & Launch

### 2.1 Download Composer (if not available)

```bash
which composer || (
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    rm composer-setup.php
    mkdir -p ~/bin
    mv composer.phar ~/bin/composer
    chmod +x ~/bin/composer
    export PATH="$HOME/bin:$PATH"
)
echo "Composer: $(composer --version)"
```

### 2.2 Run the full install

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan package:discover --ansi
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan db:seed --class=DemoUserSeeder
php artisan db:seed --class=KnowledgeBaseSeeder
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 755 storage bootstrap/cache
```

### 2.3 Promote the default user to super_admin

```bash
php -r "
require 'vendor/autoload.php';
\$app = require 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\Illuminate\Support\Facades\DB::table('users')
    ->where('email','admin@mymine.com')
    ->update(['role' => 'super_admin', 'password' => \Illuminate\Support\Facades\Hash::make('Admin@2026!')]);
echo 'Done' . PHP_EOL;
"
```

Default login credentials (force-change on first login):
- Email: `admin@mymine.com`
- Password: `Admin@2026!`

---

## Part 3 — Automated Deployment

### 3a — cPanel servers (shell_exec is enabled)

The webhook works directly. Nothing extra to configure. Skip to Part 4.

### 3b — DirectAdmin servers (shell_exec disabled in web PHP)

DirectAdmin disables `shell_exec` in web PHP. The webhook writes a flag file; a cron job does the actual deploy.

#### Set the cron job

In **DirectAdmin → Cron Jobs → Add Cron Job**:

| Field | Value |
|---|---|
| Minute | `*` |
| Hour | `*` |
| Day | `*` |
| Month | `*` |
| Weekday | `*` |
| Command | `bash /home/USERNAME/domains/clientname.domain.com/laravel/deploy-cron.sh >> /home/USERNAME/domains/clientname.domain.com/laravel/storage/logs/deploy.log 2>&1` |

Replace `USERNAME` with the actual DirectAdmin account username (e.g. `hwalimad`).

Make the script executable:
```bash
chmod +x ~/domains/clientname.domain.com/laravel/deploy-cron.sh
```

---

## Part 4 — Add the GitHub Webhook

1. Go to **github.com → hwalima/production → Settings → Webhooks → Add webhook**

| Field | Value |
|---|---|
| Payload URL | `https://clientname.domain.com/deploy.php` |
| Content type | `application/json` |
| Secret | *(same value as `DEPLOY_SECRET` in the client's `.env`)* |
| Which events | Just the push event |

2. Click **Add webhook**
3. GitHub will send a test ping — it should show a green tick ✅

---

## Part 5 — Point the Domain

In the client's DNS panel, add an **A record**:
```
Type: A
Name: @ (or subdomain, e.g. mining)
Value: <server IP address>
TTL: 3600
```

In the hosting control panel, ensure the domain is added as an Addon Domain / Virtual Host with the document root pointing to the `public/` folder.

---

## Part 6 — Verify Everything Works

```bash
# From server terminal — should return 200 or 302
curl -s -o /dev/null -w "HTTP %{http_code}\n" https://clientname.domain.com
```

Then visit the site in a browser and log in with `admin@mymine.com` / `Admin@2026!`.

Go to **Admin → Settings** and update:
- Company name
- Company logo
- Email address
- Phone number
- Gold price default
- SMTP settings (for email alerts)

---

## Part 7 — Confirm Auto-Deploy is Working

Make a test push from your laptop:

```bash
cd C:\MyMine
git commit --allow-empty -m "chore: test deploy for clientname"
git push origin main
```

Then:
- **GitHub → Webhooks** → confirm the new webhook shows ✅
- **cPanel**: deploy happens within seconds
- **DirectAdmin**: check the log within 60 seconds:
  ```bash
  tail -20 ~/domains/clientname.domain.com/laravel/storage/logs/deploy.log
  ```
  Should end with `Deploy complete.`

---

## Summary Checklist

| Step | Task | Done |
|---|---|---|
| 1 | Generate deploy key and add to GitHub | ☐ |
| 2 | Configure SSH on server | ☐ |
| 3 | Create MySQL database | ☐ |
| 4 | Clone repo and wire web root | ☐ |
| 5 | Configure `.env` | ☐ |
| 6 | Run composer install + artisan setup | ☐ |
| 7 | Promote default user to super_admin | ☐ |
| 8 | Add cron job (DirectAdmin only) | ☐ |
| 9 | Add GitHub webhook | ☐ |
| 10 | Point DNS | ☐ |
| 11 | Test auto-deploy with empty commit | ☐ |
| 12 | Configure company settings in app | ☐ |

---

## Quick Reference

| Item | Value |
|---|---|
| Default login | `admin@mymine.com` / `Admin@2026!` |
| Webhook URL | `https://DOMAIN/deploy.php` |
| Deploy log (DirectAdmin) | `~/domains/DOMAIN/laravel/storage/logs/deploy.log` |
| Deploy log (cPanel) | `~/public_html/SITEDIR/storage/logs/deploy.log` |
| Cron script | `deploy-cron.sh` in Laravel root |
| GitHub repo | `github.com/hwalima/production` |
