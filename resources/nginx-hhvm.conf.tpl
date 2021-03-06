user  www-data www-data;
worker_processes 2;
daemon off;

events {
    worker_connections  1024;
}


http {
    include       mime.types;
    default_type  application/octet-stream;

    error_log     /var/log/nginx/error.log warn;
    access_log    /var/log/nginx/access.log combined;

    sendfile        on;

    keepalive_timeout   30;
    types_hash_max_size 2048;

    server {
        listen __PORT__;

        server_name ~^www\.(?<domain>.+)$;
        return 301 $scheme://$domain$request_uri;
    }

    server {
        listen __PORT__ default;
        root   /var/www/public/;

        location / {
            index  index.html index.htm index.php;
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ .php$ {
            include        fastcgi_params;

            fastcgi_keep_conn   on;
            fastcgi_pass        unix:/var/run/hhvm/sock;
            fastcgi_param       SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }

        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   html;
        }

        ## Enable GZip
        gzip on;
        gzip_comp_level 8;
        gzip_disable msie6;
        gzip_min_length 1000;
        gzip_vary on;
        gzip_types text/css text/xml application/x-javascript;
    }
}
