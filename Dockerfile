FROM php:7.2-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer

ARG HOST_UID=${HOST_UID:-4000}
ARG HOST_USER=${HOST_USER:-nodummy}
RUN [ "${HOST_USER}" == "root" ] || (adduser -D -u ${HOST_UID} ${HOST_USER})

RUN apk update \
    && apk add --no-cache \
        unzip

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del --purge $PHPIZE_DEPS

USER ${HOST_USER}

RUN composer global require -q hirak/prestissimo

WORKDIR /opt/project
