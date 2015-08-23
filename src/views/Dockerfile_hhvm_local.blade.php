FROM {!! $hhvm_image !!}
MAINTAINER {!! $maintainer !!}

WORKDIR /var/www

ADD {!! str_replace(base_path() . '/', '', LARAVEL_SHIPPER_ROOT) !!}/resources/nginx-hhvm.conf /etc/nginx/nginx.conf

# Set user to {!! $uid !!} so we can map it to logged in user
RUN useradd -d /var/www -u {!! $uid !!} www && \
    sed -i 's/www-data/www/g' /etc/nginx/nginx.conf && \
    sed -i 's/www-data/www/g' /etc/service/hhvm/run && \
    chown -R www:www \
        /var/run/hhvm \
        /var/log/hhvm \
        /var/log/nginx \
        /var/www
