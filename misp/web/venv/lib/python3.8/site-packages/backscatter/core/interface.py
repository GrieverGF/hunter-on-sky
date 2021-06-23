"""Core client to interact with Backscatter.io services."""
import logging
import requests
from requests_futures.sessions import FuturesSession
from concurrent.futures import wait
from backscatter.core.observations import Observations
from backscatter.utils import gen_logger
from backscatter.utils import gen_headers


class Backscatter:

    """Primary interface to the platform."""

    api_key = None
    name = 'backscatter'
    log = gen_logger(name, logging.INFO)

    def __init__(self, host='http://api.backscatter.local', version='v0',
                 log_level=logging.INFO):
        """."""
        self.host = host
        self.version = version
        self.set_log_level(log_level)
        self.endpoint = '/'.join([self.host, self.version])

        options = {'endpoint': self.endpoint, 'api_key': self.api_key}
        local = object
        setattr(local, 'ip', self.ip)
        setattr(self, 'observations', object)
        print(dir(self))

    def set_log_level(self, level):
        """Override the default log level of the class."""
        if level == 20:
            to_set = logging.INFO
        if level == 10:
            to_set = logging.DEBUG
        if level == 40:
            to_set = logging.ERROR
        self.log.setLevel(to_set)

    def get_api_key(self):
        """Get the current API key."""
        return self.api_key

    def ip(self, query):
        """."""
        return "HELLO"
