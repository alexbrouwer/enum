FROM php:7.3-cli-alpine

###########################################################################
# non-root user:
###########################################################################

ARG APP_USER=app
ARG APP_GROUP=app
ARG APP_USER_ID=1000
ARG APP_GROUP_ID=1000
RUN addgroup -g ${APP_GROUP_ID} -S ${APP_GROUP} \
	&& adduser -u ${APP_USER_ID} -S ${APP_USER} -G ${APP_GROUP}

###########################################################################
# Set Timezone
###########################################################################

ARG TZ=UTC
ENV TZ ${TZ}

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

###########################################################################
# PHP extras
###########################################################################

# install php extensions
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/

RUN install-php-extensions xdebug zip

# configure php extensions
RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
	&& echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
	&& echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

###########################################################################
# Composer
###########################################################################

# install composer
RUN apk update \
	&& apk add --no-cache git curl \
	&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/*

# Make sure app directory has correct ownership
ARG APP_CODE_PATH="/app"
RUN mkdir ${APP_CODE_PATH} \
	&& chown ${APP_USER}:${APP_GROUP} ${APP_CODE_PATH} \
	&& chmod 775 ${APP_CODE_PATH}

WORKDIR ${APP_CODE_PATH}

# execute below as non-root user
USER ${APP_USER}

# make sure PATH contains all composer scripts
ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"

# speedup composer installs
RUN composer global require hirak/prestissimo
