FROM php:8.2-apache

RUN a2enmod rewrite

# Define a pasta pública como a raiz do servidor
ENV APACHE_DOCUMENT_ROOT /var/www/html/src/public

# Ajusta as configurações do Apache para apontar para a nova pasta
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/src/public/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Instala extensões PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html

# Garante que o PHP tenha permissão para ler/escrever nos arquivos
RUN chown -R www-data:www-data /var/www/html