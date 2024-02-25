FROM php:8.1-cli-alpine

ARG REPOSITORY=an0r4k0/representative-reward-distribution
LABEL org.opencontainers.image.source https://github.com/$REPOSITORY

# Install dependencies
RUN apk update && apk add --no-cache \
    libzip-dev \
    oniguruma-dev \
    gmp-dev

RUN docker-php-ext-install zip bcmath gmp

# Copy the application
COPY builds/application /root/application

# Copy the entrypoint script
COPY ./docker/entry.sh /root/entry.sh
RUN chmod +x /root/entry.sh

# Set the working directory
WORKDIR /root
USER root

# Set the entrypoint
ENTRYPOINT ["/root/entry.sh"]

