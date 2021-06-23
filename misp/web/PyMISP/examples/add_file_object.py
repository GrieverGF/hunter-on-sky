#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from pymisp import PyMISP
from pymisp.tools import make_binary_objects
import traceback
from keys import misp_url, misp_key, misp_verifycert
import glob
import argparse

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Extract indicators out of binaries and add MISP objects to a MISP instance.')
    parser.add_argument("-e", "--event", required=True, help="Event ID to update.")
    parser.add_argument("-p", "--path", required=True, help="Path to process (expanded using glob).")
    args = parser.parse_args()

    pymisp = PyMISP(misp_url, misp_key, misp_verifycert)

    for f in glob.glob(args.path):
        try:
            fo, peo, seos = make_binary_objects(f)
        except Exception:
            traceback.print_exc()
            continue

        if seos:
            for s in seos:
                r = pymisp.add_object(args.event, s)

        if peo:
            if hasattr(peo, 'certificates') and hasattr(peo, 'signers'):
                # special authenticode case for PE objects
                for c in peo.certificates:
                    pymisp.add_object(args.event, c, pythonify=True)
                for s in peo.signers:
                    pymisp.add_object(args.event, s, pythonify=True)
                del peo.certificates
                del peo.signers
            del peo.sections
            r = pymisp.add_object(args.event, peo, pythonify=True)
            for ref in peo.ObjectReference:
                r = pymisp.add_object_reference(ref)

        if fo:
            response = pymisp.add_object(args.event, fo, pythonify=True)
            for ref in fo.ObjectReference:
                r = pymisp.add_object_reference(ref)
