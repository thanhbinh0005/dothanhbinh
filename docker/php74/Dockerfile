# Reproduce the owner's XAMPP PHP version; keep the normal PHP 8.3 stack intact.
FROM wordpress:php8.3-apache@sha256:d42f63cb89ad0eab272c4b393164e63830d0df270009907919f7dcce52b1a20b AS wordpress_core
FROM php:7.4.30-apache@sha256:2113ef93843d2b906c1dbb79e5e68632ff6ba62cc62a277e9f09b739ce57fbaf
RUN docker-php-ext-install -j2 mysqli \
    && a2enmod rewrite \
    && printf '<Directory /var/www/html>\nAllowOverride All\nRequire all granted\n</Directory>\n' > /etc/apache2/conf-available/wordpress.conf \
    && a2enconf wordpress
COPY --from=wordpress_core /usr/src/wordpress/ /usr/src/wordpress/
COPY --from=wordpress_core /usr/local/bin/docker-entrypoint.sh /usr/local/bin/wordpress-entrypoint.sh
ENTRYPOINT ["wordpress-entrypoint.sh"]
CMD ["apache2-foreground"]
