# Fix for URL Issues in Local Development

## Problem
Category links and other URLs are pointing to production site (https://crowngallery.lk) instead of local staging (http://127.0.0.1:8000).

## Root Cause
The `.env` file has `APP_URL=https://crowngallery.lk` which causes all URL generation to use the production domain.

## Solution

### Step 1: Update APP_URL in .env file
Open your `.env` file and change:
```
APP_URL=https://crowngallery.lk
```
to:
```
APP_URL=http://127.0.0.1:8000
```

### Step 2: Clear caches
After updating .env, run:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 3: Restart your local server
If you're using `php artisan serve`, restart it.

## What Was Fixed
1. Category carousel links now use relative URLs (ensured with leading slash)
2. All other URLs will respect the APP_URL setting after you update .env

## Note
- Keep `APP_URL=https://crowngallery.lk` for production
- Use `APP_URL=http://127.0.0.1:8000` for local development
- Consider using different .env files for different environments
