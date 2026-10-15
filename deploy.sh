#!/bin/bash
set -e

# StackCP runs this script from the repository root
cd "$(dirname "$0")" || exit 1

# Install PHP dependencies (use php83 as required by Serverbyt)
php83 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction

# Run Laravel optimizations
php83 artisan migrate --force
php83 artisan config:cache
php83 artisan route:cache
php83 artisan view:cache
php83 artisan queue:restart
