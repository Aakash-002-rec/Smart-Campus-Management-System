#!/bin/bash
set -e

# Default PORT to 80 if not set by hosting platform
PORT="${PORT:-80}"

# Configure Apache listening port dynamically
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf

# Ensure upload directories exist and have proper permissions
mkdir -p /var/www/html/uploads/assignments /var/www/html/uploads/submissions
chown -R www-data:www-data /var/www/html/uploads
chmod -R 775 /var/www/html/uploads

exec apache2-foreground
