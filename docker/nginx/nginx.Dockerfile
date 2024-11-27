FROM nginx:stable-alpine

RUN mv /etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf.orig
COPY conf.d/default.conf /etc/nginx/conf.d/

RUN mkdir -p /var/www/html