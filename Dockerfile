# -------------------------------------------------------
# BuildNess - Dockerfile
# Imagen base: PHP 8.1 con Apache
# -------------------------------------------------------
FROM php:8.1-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable mysqli

# Habilitar mod_rewrite de Apache (por si se necesita en el futuro)
RUN a2enmod rewrite

# Copiar el código fuente al directorio raíz de Apache
COPY . /var/www/html/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Exponer el puerto 80
EXPOSE 80
