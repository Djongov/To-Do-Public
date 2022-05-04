FROM php:8.1-apache
ENV DB_PASS=:~QB;sh5a%K
COPY . /var/www/html/
ARG phpIniPath=/usr/local/etc/php/php.ini
#RUN cp /usr/local/etc/php/php.ini-production $phpIniPath
RUN cp /var/www/html/php.inifile /usr/local/etc/php/php.ini
# Update Php Settings
RUN sed -E -i -e 's/max_execution_time = 30/max_execution_time = 120/' $phpIniPath \
    && sed -E -i -e 's/memory_limit = 128M/memory_limit = 256M/' $phpIniPath \
    && sed -E -i -e 's/post_max_size = 8M/post_max_size = 64M/' $phpIniPath \
    && sed -E -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' $phpIniPath \
    && sed -E -i -e 's/expose_php = On/expose_php = Off/' $phpIniPath \
    && sed -E -i -e 's/session.name = PHPSESSID/session.name = SSID/' $phpIniPath \
    && sed -E -i -e 's/session.cookie_httponly =/session.cookie_httponly = true/' $phpIniPath \
    && sed -E -i -e 's/session.cookie_samesite =/session.cookie_samesite = "Strict"/' $phpIniPath \
    && sed -E -i -e 's/;session.cookie_secure =/session.cookie_secure =/' $phpIniPath
RUN docker-php-ext-install \
    mysqli
RUN apt-get update
RUN ["apt-get", "install", "-y", "vim"]
RUN a2enmod rewrite
RUN a2enmod headers
RUN service apache2 restart

#CMD ["apache2", "-D", "FOREGROUND"]