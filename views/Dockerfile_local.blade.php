FROM x3tech/nginx-hhvm:3.7.0
MAINTAINER {!! $maintainer !!}

WORKDIR /var/www

ADD {!! str_replace(base_path() . '/', '', LARAVEL_SHIPPER_ROOT) !!}/resources/nginx-hhvm.conf.tpl /etc/nginx/nginx.conf.tpl

# Set user to 1000 so we can map it to logged in user
RUN useradd -d /var/www -u {!! $uid !!} www && \
    sed -i 's/www-data/www/g' /etc/nginx/nginx.conf.tpl && \
    sed -i 's/www-data/www/g' /etc/service/hhvm/run && \
    chown -R www:www \
        /var/run/hhvm \
        /var/log/hhvm \
        /var/log/nginx \
        /var/www
