FROM php:7.2-cli

COPY ./scripts/ /tmp/scripts/
RUN chmod +x -R /tmp/scripts/

# set timezone
ARG TZ=UTC
RUN /tmp/scripts/set_timezone.sh ${TZ}

# add users
ARG HOST_USER=${HOST_USER:-nodummy}
ARG HOST_UID=${HOST_UID:-4000}
RUN /tmp/scripts/create_user.sh ${HOST_USER} ${HOST_USER} ${HOST_UID} ${HOST_UID}

RUN /tmp/scripts/install_php_extensions.sh
RUN /tmp/scripts/install_software.sh

# set up ssh
RUN apt-get update -yqq && apt-get install -yqq openssh-server \
 && mkdir /var/run/sshd \
;

## Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer
USER ${HOST_USER}
RUN composer global require -q hirak/prestissimo
USER root

# add default public key to authorized_keys
USER ${HOST_USER}
COPY ${SERVICE_DIR}/.ssh/insecure_id_rsa.pub /tmp/insecure_id_rsa.pub
RUN mkdir -p ~/.ssh \
 && cat /tmp/insecure_id_rsa.pub >> ~/.ssh/authorized_keys \
 && chown -R ${HOST_USER}: ~/.ssh \
 && chmod 700 ~/.ssh \
 && chmod 600 ~/.ssh/authorized_keys \
;
USER root

# php config
COPY ./config/php/conf.d/*  /usr/local/etc/php/conf.d/

# workdir
ARG APP_CODE_PATH="/opt/project"
WORKDIR "$APP_CODE_PATH"

# entrypoint
RUN mkdir -p /bin/docker-entrypoint/ \
 && cp /tmp/scripts/docker-entrypoint/* /bin/docker-entrypoint/ \
 && chmod +x -R /bin/docker-entrypoint/ \
 && chmod +w /etc/hosts \
;

RUN /tmp/scripts/cleanup.sh

# @see https://docs.docker.com/engine/examples/running_ssh_service/
CMD ["/usr/sbin/sshd", "-D"]
ENTRYPOINT ["/bin/docker-entrypoint/resolve-docker-host-ip.sh"]
