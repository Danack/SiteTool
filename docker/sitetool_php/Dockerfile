FROM debian:9

USER root

RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y curl php7.0-fpm \
    php-xml php-mbstring php-intl php-redis tidy graphviz \
    ca-certificates lsb-release apt-transport-https

WORKDIR /var/app

# Set up configuration volumes
RUN mkdir -p /etc/config

RUN pwd
RUN ls -l

CMD tail -f ./instruction.txt
