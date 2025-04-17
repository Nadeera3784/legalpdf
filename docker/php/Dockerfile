FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libfontconfig1 \
    libxrender1 \
    libzip-dev \
    wkhtmltopdf \
    supervisor \
    vim

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Configure PHP
RUN echo "memory_limit = 2G" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "max_execution_time = 3600" > /usr/local/etc/php/conf.d/max-execution-time.ini
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/upload-max-filesize.ini
RUN echo "post_max_size = 100M" > /usr/local/etc/php/conf.d/post-max-size.ini

# Create directories for supervisor
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /etc/supervisor/conf.d

# Copy supervisor configuration
COPY supervisor/laravel-horizon.conf /etc/supervisor/conf.d/laravel-horizon.conf
COPY supervisor/php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf

# Create supervisord.conf that includes all configs
RUN echo "[supervisord]" > /etc/supervisord.conf \
    && echo "nodaemon=true" >> /etc/supervisord.conf \
    && echo "user=root" >> /etc/supervisord.conf \
    && echo "logfile=/var/log/supervisord.log" >> /etc/supervisord.conf \
    && echo "pidfile=/var/run/supervisord.pid" >> /etc/supervisord.conf \
    && echo "" >> /etc/supervisord.conf \
    && echo "[include]" >> /etc/supervisord.conf \
    && echo "files = /etc/supervisor/conf.d/*.conf" >> /etc/supervisord.conf

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000
EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"] 