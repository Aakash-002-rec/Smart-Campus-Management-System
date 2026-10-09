FROM php:8.2-apache

# Install required PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers

# Configure PHP production runtime settings and upload limits
RUN { \
    echo 'upload_max_filesize = 50M'; \
    echo 'post_max_size = 55M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    echo 'session.cookie_httponly = 1'; \
    echo 'session.use_only_cookies = 1'; \
    echo 'display_errors = Off'; \
    echo 'log_errors = On'; \
} > /usr/local/etc/php/conf.d/smart-campus.ini

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . /var/www/html/

# Copy entrypoint script and normalize line endings (LF)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

# Ensure upload directories exist and permissions are set
RUN mkdir -p /var/www/html/uploads/assignments /var/www/html/uploads/submissions && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 775 /var/www/html/uploads

# Expose default HTTP port
EXPOSE 80

# Run entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
