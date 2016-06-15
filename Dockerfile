FROM modpreneur/trinity-test:alpine

MAINTAINER Barbora Čápová <capova@modpreneur.com>

# Install app
RUN rm -rf /var/app/*
ADD . /var/app

WORKDIR /var/app

RUN chmod +x entrypoint.sh
ENTRYPOINT ["sh", "entrypoint.sh"]