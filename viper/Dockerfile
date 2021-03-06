#
# This file is part of Viper - https://github.com/viper-framework/viper
# See the file 'LICENSE' for copying permission.
#

FROM ubuntu:20.04

ENV YARA_VERSION       4.1.1
ENV PYEXIF_VERSION     0.2.0
ENV ANDROGUARD_VERSION 1.9
ENV VIPER_VERSION      1.3

USER root
RUN apt update && apt -y install software-properties-common && \
    apt-add-repository universe && apt update && apt install -y \
    git \
    gcc \
    python3-dev \
    python3-pip \
    curl \
    libtool \
    autoconf \
    flex \
    python3-socks \
    python3-m2crypto \
    python3-levenshtein \
    swig \
    libssl-dev \
    pff-tools \
    libimage-exiftool-perl \
    automake \
    libtool \
    make \
    swig \
    libffi-dev \
    ssdeep \
    libfuzzy-dev \
    unrar-free \
    p7zip-full \
    pkg-config && \
  rm -rf /var/lib/apt/lists/*
RUN apt update && apt install libusb-1.0-0 


# Make Tmp Dir
RUN mkdir ~/tmp_build

# Install Yara
RUN cd ~/tmp_build && \
  git clone -b v${YARA_VERSION} https://github.com/VirusTotal/yara.git && \
  cd yara && \
  bash build.sh && \
  make install && \
  git clone https://github.com/VirusTotal/yara-python.git && \
  cd yara-python && \
  python3 setup.py build && \
  python3 setup.py install && \
  cd ../.. && \
  rm -rf yara && \
  ldconfig


# Install PyExif
RUN cd ~/tmp_build && \
  git clone -b v${PYEXIF_VERSION} git://github.com/smarnach/pyexiftool.git && \
  cd pyexiftool && \
  python3 setup.py install

# Install AndroGuard
RUN cd ~/tmp_build && \
  curl -sSL https://github.com/androguard/androguard/archive/${ANDROGUARD_VERSION}.tar.gz | \
  tar -xzC .  && \
  cd androguard-${ANDROGUARD_VERSION} && \
  python3 setup.py install

# Create Viper User
RUN groupadd -r viper && \
  useradd -r -g viper -d /home/viper -s /sbin/nologin -c "Viper User" viper && \
  mkdir /home/viper && \
  chown -R viper:viper /home/viper

# Clean tmp_build
RUN rm -rf ~/tmp_build


# Install Viper-Web

USER root
WORKDIR /home/viper
RUN git clone https://github.com/viper-framework/viper-web.git
RUN sed -i 's/MarkupSafe==1.1.1/MarkupSafe>=2.0/g' viper-web/requirements.txt
RUN pip3 install viper-framework 
RUN git clone https://github.com/viper-framework/viper-modules.git /home/viper/.viper/modules
RUN pip3 install -U -r /home/viper/.viper/modules/requirements.txt
RUN viper
RUN cd /home/viper/viper-web && python3 setup.py install
RUN chown -R viper:viper /home/viper/.viper
COPY docker-entrypoint.sh /home/viper/viper-web/docker-entrypoint.sh 


USER viper
WORKDIR /home/viper/viper-web
CMD ["sh","docker-entrypoint.sh"]
