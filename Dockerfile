FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/app/public

# Serve only /public; the application code, config and storage live outside the web root.
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
 && a2enmod rewrite

COPY . /var/www/app
RUN mkdir -p /var/www/app/storage && chown -R www-data:www-data /var/www/app/storage

