FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

ARG REPOSITORY=an0r4k0/representative-reward-distribution
LABEL org.opencontainers.image.source https://github.com/$REPOSITORY

# Install dependencies
RUN apt-get update && apt-get install -y \
    php8.1 \
    php8.1-cli \
    php8.1-common \
    php8.1-zip \
    php8.1-mbstring \
    php8.1-curl \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-sqlite3 \
    php8.1-gmp

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

