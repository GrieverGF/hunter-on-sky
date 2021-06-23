"""Interface for the observation endpoints."""
from backscatter.core.fetch import Fetch


class Observations(Fetch):

    """Wrapper for the observations endpoints."""

    def __init__(self, endpoint, api_key):
        """."""
        self.endpoint = endpoint + "/observations"
        self.api_key = api_key

    def ip(self, query):
        """IP address endpoint."""
        return self._request(self.endpoint + "/ip", {'query': query},
                             {'X-API-KEY': self.api_key})

    def network(self, query):
        """network endpoint."""
        return self._request(self.endpoint + "/network", {'query': query},
                             {'X-API-KEY': self.api_key})

    def asn(self, query):
        """ASN endpoint."""
        return self._request(self.endpoint + "/asn", {'query': query},
                             {'X-API-KEY': self.api_key})

    def port(self, query):
        """Port endpoint."""
        return self._request(self.endpoint + "/port", {'query': query},
                             {'X-API-KEY': self.api_key})

    def country(self, query):
        """Country endpoint."""
        return self._request(self.endpoint + "/country", {'query': query},
                             {'X-API-KEY': self.api_key})
