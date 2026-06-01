# ─────────────────────────────────────────────
#  QRLockApp — Dockerfile
#  PHP 8.2 built-in server (no Apache MPM issues)
# ─────────────────────────────────────────────
FROM php:8.2-cli

# Install mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Set working directory
WORKDIR /var/www/html

# Copy frontend
COPY index.html .

# Copy backend PHP files
COPY backend/ ./backend/

# Expose port 8080
EXPOSE 8080

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html"]