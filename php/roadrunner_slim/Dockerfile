FROM golang:1.12 as builder

ENV GO111MODULE on
ENV RR_VERSION v1.4.1

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    && rm -rf /var/lib/apt/lists/*

# Build RoadRunner
WORKDIR /go/src
RUN git clone --depth 1 --branch ${RR_VERSION} https://github.com/spiral/roadrunner \
  && cd /go/src/roadrunner \
  && make \
  && make install

# PHP Image
FROM php:7.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
  git \
  vim \
  libzip-dev \
  unzip

# Copy RoadRunner binary and config
COPY --from=builder /go/src/roadrunner/rr /usr/local/bin/rr
COPY config /etc/roadrunner

# Install PHP Extensions
RUN docker-php-ext-install zip \
  && docker-php-ext-install opcache \
  && docker-php-ext-enable opcache

WORKDIR /var/www
RUN git clone --branch slimphp https://github.com/n1215/roadrunner-docker-skeleton.git app
WORKDIR /var/www/app

COPY index.php worker.php

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install
RUN composer init-config

ENTRYPOINT ["/usr/local/bin/rr", "serve", "-d", "-c", "/etc/roadrunner/.rr.yaml"]

EXPOSE 3000