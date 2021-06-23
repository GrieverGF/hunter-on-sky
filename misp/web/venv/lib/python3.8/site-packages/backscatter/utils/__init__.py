#!/usr/bin/env python
import datetime
import logging
import os
import random
import re
import sys
from typing import Dict
from typing import List
from typing import Pattern


def gen_logger(name, log_level):
    """Create a logger to be used between processes.

    :returns: Logging instance.
    """
    logger = logging.getLogger(name)
    logger.setLevel(log_level)
    shandler: logging.StreamHandler = logging.StreamHandler(sys.stdout)
    fmt: str = '\033[1;32m%(levelname)-5s %(module)s:%(funcName)s():'
    fmt += '%(lineno)d %(asctime)s\033[0m| %(message)s'
    shandler.setFormatter(logging.Formatter(fmt))
    logger.addHandler(shandler)
    return logger


def gen_headers(options):
    """Generate a header pairing."""
    ua_list = ['Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36']
    headers = {'User-Agent': ua_list[random.randint(0, len(ua_list) - 1)]}
    headers.update(options)
    return headers


def str_datetime(stamp):
    """Convert datetime to str format."""
    return stamp.strftime("%Y-%m-%d %H:%M:%S")


def now_time():
    """Get the current time."""
    return datetime.datetime.now()


def valid_ip(query):
    """Check if an IP address is valid."""
    import socket

    try:
        socket.inet_aton(query)
        return True
    except socket.error:
        return False
