FROM debian:bookworm-slim AS fontconvert-builder

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        g++ \
        libfreetype6-dev \
        make \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /build
COPY fontconvert/ ./
RUN make

FROM debian:bookworm-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        lighttpd \
        php-cgi \
        php-gd \
    && rm -rf /var/lib/apt/lists/*

COPY docker/lighttpd.conf /etc/lighttpd/lighttpd.conf
COPY --from=fontconvert-builder /build/fontconvert /usr/local/bin/fontconvert
COPY --chown=www-data:www-data . /var/www/html

RUN chmod +x /usr/local/bin/fontconvert \
    && chown -R www-data:www-data /var/www/html/fonts/user

ENV FONTCONVERT_PATH=/usr/local/bin/fontconvert

EXPOSE 80
CMD ["lighttpd", "-D", "-f", "/etc/lighttpd/lighttpd.conf"]
