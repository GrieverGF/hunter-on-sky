#!/usr/bin/env python
import json
import logging
import requests
from requests_futures.sessions import FuturesSession
from concurrent.futures import wait
from backscatter.utils import gen_logger
from backscatter.utils import gen_headers
from typing import ClassVar
from typing import Dict
from typing import List


class Fetch(object):

    """Base module class to assist in writing new modules."""

    name = 'fetch'
    log = gen_logger(name, logging.DEBUG)
    limit = 500

    def __init__(self, log_level=logging.DEBUG):
        """Local variables for the module."""
        self.set_log_level(log_level)

    def set_log_level(self, level):
        """Override the default log level of the class."""
        if level == 20:
            to_set = logging.INFO
        if level == 10:
            to_set = logging.DEBUG
        if level == 40:
            to_set = logging.ERROR
        self.log.setLevel(to_set)

    def _request_bulk(self, urls):
        """Batch the requests going out."""
        if not urls:
            raise Exception("No results were found")
        session: FuturesSession = FuturesSession(max_workers=len(urls))
        self.log.info("Bulk requesting: %d" % len(urls))
        futures = [session.get(u, headers=gen_headers(), timeout=3) for u in urls]
        done, incomplete = wait(futures)
        results: List = list()
        for response in done:
            try:
                results.append(response.result())
            except Exception as err:
                self.log.warn("Failed result: %s" % err)
        return results

    def _request(self, endpoint, params, options):
        """Make the request to the API and get results."""
        self.log.debug("Requesting: %s" % endpoint)
        headers = gen_headers(options)
        print(headers)
        response = requests.get(endpoint, params=params, headers=headers)
        loaded = json.loads(response.content)
        return loaded
