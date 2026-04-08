ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-fpm AS base

ARG PHP_VERSION=8.5
ARG UID=1000
ARG GID=1000
ARG USER=phalcon
ARG GROUP=phalcon

# hadolint ignore=DL3022
COPY --from=composer/composer:2 --chown=${USER}:${GROUP} --chmod=0770 /usr/bin/composer /usr/bin/composer
# hadolint ignore=DL3022
COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie

# Add user and group
RUN groupadd -g "${GID}" "${GROUP}" \
    && RUN useradd -l -m -u "${UID}" -g "${GID}" "${USER}" \
    && RUN usermod -s /bin/bash "${USER}" \
    && RUN mkdir /app \
    && RUN chown "${USER}":"${GROUP}" /app \
    && RUN chmod 0770 /app \
# Install needed packages \
    && RUN apt update -y \
    && RUN apt install -yq --no-install-recommends apt-utils git locales nano sudo unzip wget zip \
# Configure locales \
    && RUN sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen \
    && RUN dpkg-reconfigure --frontend=noninteractive locales \
    && RUN update-locale LANG=en_US.UTF-8 \
# Install xdebug for code coverage \
    && RUN apt install -yq --no-install-recommends $PHPIZE_DEPS \
    && RUN pie install xdebug/xdebug \
    && RUN apt-get purge -y --auto-remove $PHPIZE_DEPS \
# Copy ini file \
    && RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
# Set correct pid file location and permissions \
    && RUN mkdir -p /run/php \
    && RUN chown "${USER}":"${GROUP}" /run/php \
    && RUN chmod 0770 /run/php \
# Cleanup \
    && RUN apt-get autoremove --purge -y \
    && RUN apt-get autoclean -y \
    && RUN apt-get clean -y \
    && RUN rm -rf /tmp/* /var/tmp/* \
    && RUN find /var/cache/apt/archives /var/lib/apt/lists -not -name lock -type f -delete \
    && RUN find /var/cache -type f -delete \
    && RUN find /var/log -type f -delete \

COPY resources/docker/develop/.bashrc /home/${USER}/.bashrc
COPY resources/docker/develop/extra.ini /usr/local/etc/php/conf.d/

ENV LANG=en_US.UTF-8 \
    LANGUAGE=en_US.UTF-8 \
    LC_ALL=en_US.UTF-8

SHELL [ "/bin/bash", "--rcfile", "/home/${USER}/.bashrc", "-o", "pipefail", "-c" ]

WORKDIR /app

#USER ${USER}
