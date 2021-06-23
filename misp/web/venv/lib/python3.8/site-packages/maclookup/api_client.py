# -*- coding: utf-8 -*-
from future.standard_library import install_aliases
install_aliases()

import json
from sys import version_info
from .models import *
from .exceptions import UnparsableResponseException, EmptyResponseException
from .requester import Requester


class ApiClient:
    __version = '1.0.3'
    __url = "https://api.macaddress.io/v1"
    __separator = "/"
    __user_agent = "Python Client Library v" + __version
    _FORMAT_F = 'output'
    _SEARCH_F = 'search'
    _VERBOSE_T = 'vendor'

    def __init__(self, api_key):
        """Init ApiClient instance.

            Keyword arguments:
            api_key -- Your api_key
        """

        self.api_key = api_key
        self.__parse_as_object = False
        self.requester = Requester(api_key, self.__user_agent)

    def get(self, mac):
        """Get data from API as instance of ResponseModel.

            Keyword arguments:
            mac -- MAC address or OUI for searching
        """

        data = {
            self._FORMAT_F: 'json',
            self._SEARCH_F: mac
        }

        response = self.__decode_str(self.__call_api(self.__url, data), 'utf-8')

        if len(response) > 0:
            return self.__parse(response)
        raise EmptyResponseException()

    def get_raw_data(self, mac, response_format='json'):
        """Get data from API as string.

            Keyword arguments:
            mac -- MAC address or OUI for searching
            response_format -- supported types you can see on the https://macaddress.io
        """

        data = {
            self._FORMAT_F: response_format,
            self._SEARCH_F: mac
        }

        response = self.__decode_str(self.__call_api(self.__url, data), 'utf-8')

        if len(response) > 0:
            return response
        raise EmptyResponseException()

    def get_vendor(self, mac):
        """Get vendor company name.

            Keyword arguments:
            mac -- MAC address or OUI for searching
        """

        data = {
            self._SEARCH_F: mac,
            self._FORMAT_F: self._VERBOSE_T
        }

        response = self.__decode_str(self.__call_api(self.__url, data), 'utf-8')

        return response

    def __call_api(self, url, data):
        return self.requester.request(url, data)

    def __parse(self, string_response):
        try:
            dictionary = json.loads(string_response)
        except Exception as e:
            raise UnparsableResponseException(e.__str__())

        return ResponseModel(dictionary)

    def parse_json_response_into_models(self, json_text):
        return self.__parse(json_text)

    def set_base_url(self, url):
        """Set url to API.

            Keyword arguments:
            url -- API endpoint URL
        """

        self.__url = url

    def set_requester(self, requester):
        """Set the requestor instance.

            Keyword arguments:
            requestor -- The Requester instance
        """

        if isinstance(requester, Requester):
            self.requester = requester

    def __decode_str(self, string, encoding='utf-8'):
        if version_info < (3, 0):
            return string.decode(encoding)
        else:
            return string