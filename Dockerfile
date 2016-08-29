FROM php:7.0.10-cli

# make app dir
RUN mkdir -p /var/app
WORKDIR /var/app

# install git
RUN apt-get update \
    && apt-get install -y git \
    && rm -rf /var/lib/apt/lists/*

# install & self-update composer
RUN apt-get update \
    && apt-get install -y wget \
    && wget https://getcomposer.org/composer.phar -O /usr/local/bin/composer.phar \
    && apt-get purge --auto-remove -y wget \
    && rm -rf /var/lib/apt/lists/* \
    && chmod 755 /usr/local/bin/composer.phar \
    && /usr/local/bin/composer.phar self-update

# keep container alive
CMD ["tail", "-f", "/dev/null"]
