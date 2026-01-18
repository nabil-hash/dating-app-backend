# Dockerfile pour Laravel sur Railway
FROM php:8.2-apache

# Mettre à jour et installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo pdo_mysql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Activer mod_rewrite pour Apache (nécessaire pour Laravel)
RUN a2enmod rewrite

# Copier les fichiers de l'application
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Configurer les permissions des dossiers Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Générer la clé d'application (si .env existe)
RUN if [ -f .env ]; then php artisan key:generate; fi

# Port exposé (Railway utilise le PORT de l'environnement)
EXPOSE 8080

# Commande de démarrage
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
