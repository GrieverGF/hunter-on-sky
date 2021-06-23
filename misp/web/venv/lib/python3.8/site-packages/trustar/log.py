# Seems like from time to time people do a from trustar import *, lets minimize the chances of something ugly happening
__all__ = ["get_logger", "TrustarJSONFormatter"]

import datetime
import logging
import os
import sys

import json_log_formatter

from .config import LOGGING_ENV_VAR


class TrustarJSONFormatter(json_log_formatter.JSONFormatter):
    """
    Custom class to override the default behaviour of the JSONFormatter
    """

    def format(self, record):
        """
        the default behaviour of JSONFormatter is to cast everything into a string
        we avoid calling getMessage and leave the object as it is, this way our json messages can have nested dicts
        """
        message = record.msg
        extra = self.extra_from_record(record)
        json_record = self.json_record(message, extra, record)
        mutated_record = self.mutate_json_record(json_record)
        # Backwards compatibility: Functions that overwrite this but don't
        # return a new value will return None because they modified the
        # argument passed in.
        if mutated_record is None:
            mutated_record = json_record
        return self.to_json(mutated_record)

    def json_record(self, message, extra, record):
        extra['message'] = message
        extra['level'] = record.levelname
        extra['module'] = record.name
        extra['time'] = datetime.datetime.utcnow()
        if record.exc_info:
            extra['exec_info'] = self.formatException(record.exc_info)
        return extra

    def to_json(self, record):
        """Converts record dict to a JSON string.

        It makes best effort to serialize a record (represents an object as a string)
        instead of raising TypeError if json library supports default argument.
        Note, ujson doesn't support it.
        """
        return self.json_lib.dumps(record, default=_json_object_encoder)


def _json_object_encoder(obj):
    try:
        return obj.to_json()
    except AttributeError:
        return str(obj)



def get_handler():
    """
    Gets the handler to manage the output of the logger, default: stdout
    """
    handler = logging.StreamHandler(sys.stdout)
    # TODO: read from a config file, or env var, and override the default formatter.
    # IE: logging.FileHandler(filename='/path/to/file.log')
    return handler


def get_formatter():
    formatter = TrustarJSONFormatter
    # TODO: read from a config file, or env var, and override the default formatter.
    return formatter()


def get_logging_level():
    return int(os.environ.get(LOGGING_ENV_VAR, logging.INFO))


output_handler = get_handler()
output_handler.setFormatter(get_formatter())


def get_logger(name=None):
    logger = logging.getLogger(name or __name__)
    logger.addHandler(output_handler)
    logger.setLevel(get_logging_level())
    return logger
