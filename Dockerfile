FROM php:7.4-apache
MAINTAINER Grupa Konsultingowa RID <it@rid.pl>

ENV DOKUWIKI_VERSION 2020-07-29.000

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && sync && install-php-extensions gd xdebug

# Install cron
RUN apt-get update && apt-get install -y --no-install-recommends tzdata cron
RUN cp /usr/share/zoneinfo/Europe/Warsaw /etc/localtime && echo "Europe/Warsaw" > /etc/timezone

# Enable mod rewrite
RUN a2enmod rewrite

# Copy files
COPY core_engines/dokuwiki-${DOKUWIKI_VERSION} /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Copy cron file to the cron.d directory
COPY cron /etc/cron.d/cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/cron

# Apply cron job
RUN crontab /etc/cron.d/cron

# COPY CMD
COPY isowiki-cmd /usr/local/bin
RUN chmod +x /usr/local/bin/isowiki-cmd

CMD ["isowiki-cmd"]