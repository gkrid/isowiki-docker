FROM php:7.4-apache
MAINTAINER Grupa Konsultingowa RID <it@rid.pl>

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions gd xdebug

# Install cron
RUN apt-get update && apt-get install -y --no-install-recommends tzdata cron && \
    rm -r /var/lib/apt/lists /var/cache/apt/archives
RUN cp /usr/share/zoneinfo/Europe/Warsaw /etc/localtime && echo "Europe/Warsaw" > /etc/timezone

# Enable mod rewrite
RUN a2enmod rewrite

# Copy cron file to the cron.d directory
COPY cron /etc/cron.d/cron

# Give execution rights on the cron job and apply cron job
RUN chmod 0644 /etc/cron.d/cron && crontab /etc/cron.d/cron

# COPY CMD
COPY isowiki-cmd /usr/local/bin
RUN chmod +x /usr/local/bin/isowiki-cmd

# Copy files
COPY dokuwiki /opt/isowiki

CMD ["isowiki-cmd"]