# ─────────────────────────────────────────────
#  QRLockApp — Dockerfile
#  PHP 8.2 + Apache serving backend & frontend
# ─────────────────────────────────────────────
FROM php:8.2-apache

# Fix: Disable conflicting MPMs, enable only prefork (required for mod_php)
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork

# Install mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy frontend
COPY index.html .

# Copy backend PHP files into /backend sub-path
COPY backend/ ./backend/

# Copy Apache virtual host config
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
