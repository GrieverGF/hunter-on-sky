#!/usr/bin/env python
"""url_archiver is a simple library to fetch and archive URL."""

import requests
import url_normalize
import os
import hashlib
import json
import base64

__author__ = "Alexandre Dulaunoy"
__copyright__ = "Copyright 2016, Alexandre Dulaunoy"
__license__ = "AGPL version 3"
__version__ = "0.2"

class Archive:

    """url_archive Archive is base class to fetch and store URL in a raw format.
    The storage is done on the filesystem and a history of the URL is preserved."""

    def __init__(self, archive_path=False, debug=False, meta=True):
        if not archive_path:
            self.archive_path = os.path.join(os.path.split(os.path.realpath(__file__))[0], ".url_archiver")
        else:
            self.archive_path = os.path.join(archive_path, ".url_archiver")
        if debug:
            self.debug = True
            print (self.archive_path)
        else:
            self.debug = False
        if not os.path.exists(self.archive_path):
            os.mkdir(self.archive_path)

    def _hash(self, v=False):
        if not v:
            return False
        urlhash = hashlib.sha1()
        urlhash.update(v.encode('utf-8'))
        return urlhash.hexdigest()

    def _hashdir(self, v=False):
        if not v or not self.archive_path:
            return False
        v = os.path.join(self.archive_path, v)
        if os.path.exists(v):
            return True
        return os.mkdir(v)

    def _archived(self, v=False):
        if not v:
            return False
        v = os.path.join(self.archive_path, v)
        v = os.path.join(v, "archive")
        if self.debug:
            print ("Archive path {}".format(v))
        if os.path.exists(v):
            return True
        else:
            return False

    def _get(self, v=False, armor=False):
        if not v:
            return False
        v = os.path.join(self.archive_path, v)
        v = os.path.join(v, "archive")
        if os.path.exists(v):
            f = open(v, 'rb')
            archive = f.read()
            f.close()
            if not armor:
                return archive
            else:
                return base64.b64encode(archive)
        else:
            return False

    def _store(self, raw=False, u=False, meta=False):
        if not raw or not u:
            return False
        filename = os.path.join(self.archive_path, self._hash(u))
        filename = os.path.join(filename, "archive")
        if not os.path.exists(filename):
            f = open(filename, 'w')
            f.write(raw)
            f.close()
            if meta:
                metafilename = os.path.join(self.archive_path, self._hash(u))
                metafilename = os.path.join(metafilename, "meta")
                f = open(metafilename, 'w')
                f.write(json.dumps(dict(meta)))
                f.close()
            return True
        else:
            if self.debug:
                print ("{} already archived".format(u))
            return False

    def fetch(self, url=False, armor=False):
        if not url:
            return False
        normalizedurl = url_normalize.url_normalize(url)
        if self.debug:
            print (normalizedurl)
        urlhash = self._hash(v=normalizedurl)
        if self.debug:
            print (urlhash)
        self._hashdir(urlhash)
        if not self._archived(v=urlhash):
            fetcher = requests.get(normalizedurl)
            if fetcher.status_code == 200:
                meta = fetcher.headers
                meta['url_archiver:url'] = normalizedurl
                meta['url_archiver:urlhash'] = urlhash
                meta['url_archiver:version'] = __version__
                self._store(raw=fetcher.text, u=normalizedurl, meta=meta)
                return self._get(v=urlhash, armor=armor)
        else:
                return self._get(v=urlhash, armor=armor)

if __name__ == "__main__":
    a = Archive(archive_path='/tmp', debug=True)
    raw = a.fetch(url='http://www.foo.be:80///', armor=True)
    print (base64.b64decode(raw))
