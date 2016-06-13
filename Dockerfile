FROM modpreneur/trinity-test

MAINTAINER Tomáš Jančar <jancar@modpreneur.com>

# Install app
RUN rm -rf /var/app/*
ADD . /var/app

WORKDIR /var/app

RUN chmod +x entrypoint.sh
ENTRYPOINT ["sh", "entrypoint.sh"]