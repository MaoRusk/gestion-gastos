# Dockerfile para PHP en Render.com
FROM php:8.1-cli

# Instalar dependencias del sistema necesarias para PostgreSQL y MySQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copiar archivos de la aplicación
COPY . /var/www/html/

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Configurar permisos
RUN chmod -R 755 /var/www/html

# Exponer puerto (Render usará la variable $PORT)
EXPOSE 8080

# Usar el servidor PHP built-in (más simple para Render con puertos dinámicos)
CMD php -S 0.0.0.0:${PORT:-8080} -t .
