FROM php:8.0-cli
RUN apt-get update && apt-get install -y apt-utils zip unzip
# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY . /usr/src/slim-validation
WORKDIR /usr/src/slim-validation
CMD [ "php" ]