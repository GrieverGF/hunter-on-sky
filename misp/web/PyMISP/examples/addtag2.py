#!/usr/bin/env python3

from pymisp import PyMISP
from keys import misp_url, misp_key, misp_verifycert
import argparse


def init(url, key):
    return PyMISP(url, key, misp_verifycert, 'json')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Tag something.')
    parser.add_argument("-u", "--uuid", help="UUID to tag.")
    parser.add_argument("-e", "--event", help="Event ID to tag.")
    parser.add_argument("-a", "--attribute", help="Attribute ID to tag")
    parser.add_argument("-t", "--tag", required=True, help="Tag ID.")
    args = parser.parse_args()

    if not args.event and not args.uuid and not args.attribute:
        print("Please provide at least one of the following : uuid, eventID or attribute ID, see --help")
        exit()

    misp = init(misp_url, misp_key)

    if args.event and not args.attribute:
        result = misp.search(eventid=args.event)
        for event in result:
            uuid = event['Event']['uuid']

    if args.attribute:
        if not args.event:
            print("Please provide event ID also")
            exit()
        result = misp.search(eventid=args.event)
        for event in result:
            for attribute in event['Event']['Attribute']:
                if attribute["id"] == args.attribute:
                    uuid = attribute["uuid"]

    if args.uuid:
        uuid = args.uuid

    print("UUID tagged: %s" % uuid)
    misp.tag(uuid, args.tag)
