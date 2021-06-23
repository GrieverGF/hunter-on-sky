import logging

logger = logging.getLogger(__name__)


class Enum(object):

    def __new__(cls, *args, **kwargs):
        raise Exception("Enums cannot be instantiated.")

    @classmethod
    def values(cls):
        return [getattr(cls, attr) for attr in dir(cls) if not callable(getattr(cls, attr)) and not attr.startswith("__")]

    @classmethod
    def from_string(cls, string):
        """
        Simply logs a warning if the desired enum value is not found.

        :param string:
        :return:
        """

        # find enum value
        for attr in dir(cls):
            value = getattr(cls, attr)
            if value == string:
                return value

        # if not found, log warning and return the value passed in
        logger.warning("{} is not a valid enum value for {}.".format(string, cls.__name__))
        return string


class IndicatorType(Enum):

    IP = 'IP'
    CIDR_BLOCK = 'CIDR_BLOCK'
    URL = 'URL'
    EMAIL_ADDRESS = 'EMAIL_ADDRESS'
    MD5 = 'MD5'
    SHA1 = 'SHA1'
    SHA256 = 'SHA256'
    MALWARE = 'MALWARE'
    SOFTWARE = 'SOFTWARE'
    REGISTRY_KEY = 'REGISTRY_KEY'
    CVE = 'CVE'
    BITCOIN_ADDRESS = 'BITCOIN_ADDRESS'


class PriorityLevel(Enum):

    NOT_FOUND = "NOT_FOUND"
    LOW = "LOW"
    MEDIUM = "MEDIUM"
    HIGH = "HIGH"


class IdType(Enum):

    INTERNAL = "internal"
    EXTERNAL = "external"


class DistributionType(Enum):

    ENCLAVE = "ENCLAVE"
    COMMUNITY = "COMMUNITY"


class EnclaveType(Enum):

    OPEN = "OPEN"
    INTERNAL = "INTERNAL"
    CLOSED = "CLOSED"
    OTHER = "OTHER"
    RESEARCH = "RESEARCH"
    COMMUNITY = "COMMUNITY"

    @classmethod
    def from_string(cls, string):
        if string == "CLOSED_CONCRETE":
            return cls.CLOSED
        else:
            return super(cls, EnclaveType).from_string(string)
