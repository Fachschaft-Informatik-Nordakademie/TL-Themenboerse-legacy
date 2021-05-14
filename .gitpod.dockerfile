FROM gitpod/workspace-full:latest

# Docker build does not rebuild an image when a base image is changed, increase this counter to trigger it.
ENV TRIGGER_REBUILD 3

# Install custom tools, runtime, etc.
RUN sudo add-apt-repository ppa:ondrej/php \
    && sudo apt update \
    # PHP
    && sudo apt install software-properties-common php8.0-common php8.0-cli php8.0-xml -y \
    # MySQL
    && sudo apt install mysql-server -y

ENV NODE_VERSION="12.14.1"
RUN bash -c ". .nvm/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm use $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && npm install -g yarn"
ENV PATH=$HOME/.nvm/versions/node/v${NODE_VERSION}/bin:$PATH