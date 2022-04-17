FROM php:8.1-apache
ENV DB_PASS=19MySQL86$$
COPY . /var/www/html/
ARG phpIniPath=/usr/local/etc/php/php.ini
RUN cp /usr/local/etc/php/php.ini-production $phpIniPath
# Update Php Settings
RUN sed -E -i -e 's/max_execution_time = 30/max_execution_time = 120/' $phpIniPath \
    && sed -E -i -e 's/memory_limit = 128M/memory_limit = 256M/' $phpIniPath \
    && sed -E -i -e 's/post_max_size = 8M/post_max_size = 64M/' $phpIniPath \
    && sed -E -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' $phpIniPath \
    && sed -E -i -e 's/expose_php = On/expose_php = Off/' $phpIniPath
RUN docker-php-ext-install \
    mysqli
RUN apt-get update
RUN a2enmod rewrite
RUN a2enmod headers
RUN service apache2 restart

#CMD ["apache2", "-D", "FOREGROUND"]