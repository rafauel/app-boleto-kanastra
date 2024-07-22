FROM php:8.3-fpm

# set your user name, ex: user=carlos
ARG user=rafael
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    redis-tools \
    sqlite3 \
    libsqlite3-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd sockets pdo_sqlite

# Install and enable redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy existing application directory contents
COPY . .

# Ensure storage and cache directories exist and have correct permissions
RUN mkdir -p storage/framework/{cache,sessions,views} && \
    chmod -R 775 storage && \
    chmod -R 775 bootstrap/cache && \
    chown -R www-data:www-data storage && \
    chown -R www-data:www-data bootstrap/cache

# Switch to non-root user
USER $user

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
