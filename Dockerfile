FROM php:8.4-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache nginx mysql-client libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli

# Configure PHP-FPM to pass Docker environment variables to PHP worker processes
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy custom Nginx configuration
COPY .docker/nginx.conf /etc/nginx/nginx.conf

# Set working directory
WORKDIR /var/www/jobpro

# Copy project files
COPY . /var/www/jobpro

# Ensure private storage directory exists with proper permissions
RUN mkdir -p /var/www/jobpro/app/storage/resumes \
    && chown -R www-data:www-data /var/www/jobpro \
    && chmod -R 755 /var/www/jobpro \
    && chmod 750 /var/www/jobpro/app/storage/resumes

# Expose HTTP port 80
EXPOSE 80

# Start both PHP-FPM and Nginx
CMD php-fpm -D && nginx -g "daemon off;"
