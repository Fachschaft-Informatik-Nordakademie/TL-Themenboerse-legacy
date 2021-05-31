FROM gitpod/workspace-mysql:latest

# Docker build does not rebuild an image when a base image is changed, increase this counter to trigger it.
ENV TRIGGER_REBUILD 4

# Install custom tools, runtime, etc.
RUN sudo add-apt-repository ppa:ondrej/php \
    && sudo apt update \
    # PHP
    && sudo apt install software-properties-common php8.0-common php8.0-cli php8.0-xml php8.0-curl php8.0-mbstring php8.0-pdo php8.0-mysql php8.0-ldap php8.0-sqlite3 -y

ADD ./.gitpod/install-composer.sh /temp/install-composer.sh
RUN sudo sh /temp/install-composer.sh

ENV NODE_VERSION="12.14.1"
RUN bash -c ". .nvm/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm use $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && npm install -g yarn"
ENV PATH=$HOME/.nvm/versions/node/v${NODE_VERSION}/bin:$PATH
