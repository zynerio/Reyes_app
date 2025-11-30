FROM php:8.2-apache
RUN apt-get update && apt-get install -y libzip-dev zip libsqlite3-dev && docker-php-ext-install pdo_mysql pdo_sqlite && a2enmod rewrite headers
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY . /var/www/html
RUN ln -sf /var/www/html/app/storage/app/public /var/www/html/app/public/storage
RUN composer install --no-dev --optimize-autoloader -d app
RUN touch /var/www/html/app/database/database.sqlite && chown -R www-data:www-data /var/www/html/app/storage /var/www/html/app/bootstrap/cache /var/www/html/app/database
RUN printf '<Directory /var/www/html>\nAllowOverride All\n</Directory>\n<Directory /var/www/html/app/public>\nAllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/allow-override.conf && a2enconf allow-override
RUN sed -i 's/Listen 80/Listen 85/' /etc/apache2/ports.conf && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:85>/' /etc/apache2/sites-available/000-default.conf
EXPOSE 85
CMD ["apache2-foreground"]
