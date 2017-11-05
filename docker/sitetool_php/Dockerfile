FROM debian:9

USER root

RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y curl php7.0-fpm \
    php-xml php-mbstring php-intl php-redis tidy \
    ca-certificates lsb-release apt-transport-https

# gnupg

WORKDIR /var/app

# Set up configuration volumes
RUN mkdir -p /etc/config

# Copy initweb script
# COPY initweb.sh /usr/local/bin/initweb.sh

# Site syncing script
# COPY sync_sites.sh /usr/local/bin/sync_sites.sh

# Used by jenkins to package up dijon
# in to container
#COPY web /var/www
#RUN mkdir -p /var/www/sites && mkdir -p /var/sites
#COPY sites /var/sites

# CMD ["/usr/sbin/nginx", "-c", "/etc/nginx/nginx.conf"]

RUN pwd
RUN ls -l

CMD tail -f ./instruction.txt
