FROM debian:9

USER root



RUN apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y git \
    mariadb-client wget curl \
    ca-certificates lsb-release apt-transport-https gnupg


RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee -a /etc/apt/sources.list.d/php.list \
    && curl https://packages.sury.org/php/apt.gpg | apt-key add - \
    && apt-get update -qq \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y php7.2 php7.2-common php7.2-cli php7.2-fpm \
    php7.2-mysql  php7.2-curl php7.2-xml php7.2-mbstring \
    php7.2-intl php7.2-redis php7.2-zip tidy graphviz

WORKDIR /var/app

# Set up configuration volumes
RUN mkdir -p /etc/config

RUN pwd
RUN ls -l

CMD tail -f ./instruction.txt
