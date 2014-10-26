FROM x3tech/nginx-hhvm
MAINTAINER {{ $maintainer }}

# Set user to 1000 so we can map it to logged in user
RUN useradd -d /var/www -u {{ $uid }} www && \
    sed -i 's/www-data/www/g' /etc/nginx/nginx.conf && \
    sed -i 's/www-data/www/g' /etc/service/hhvm/run && \
    chown -R www:www \
        /var/run/hhvm \
        /var/log/hhvm \
        /var/log/nginx \
        /var/www
