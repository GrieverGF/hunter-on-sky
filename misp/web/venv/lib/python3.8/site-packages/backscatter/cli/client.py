#!/usr/bin/env python
"""."""
import json
import logging
import os
import sys
from collections import OrderedDict
from operator import itemgetter
from tabulate import tabulate

from argparse import ArgumentParser
from backscatter import Backscatter
from backscatter.utils import valid_ip


CONFIG_PATH = os.path.expanduser('~/.config/backscatter')
CONFIG_FILE = os.path.join(CONFIG_PATH, 'config.json')
CONFIG_DEFAULTS = {'version': 'v0', 'api_key': ''}


def detect_query(query):
    """Attempt to identify the query type being made."""
    if valid_ip(query) and query.count('.') == 3:
        return 'ip'
    if '/' in query:
        return 'network'
    if not query.isdigit():
        return 'country'
    return None


def main():
    """Run the core."""
    parser = ArgumentParser()
    subs = parser.add_subparsers(dest='cmd')
    setup_desc = "Configure this client with your API key in order to run "
    setup_desc += "commands without issue. Find your API key at https://backscatter.io/account."
    setup_parser = subs.add_parser('setup', description=setup_desc)
    setup_parser.add_argument('-k', '--api-key', dest='api_key', required=True,
                              help='API key for Backscatter from your account page.', type=str)

    observations_desc = "Query observation endpoints as documented on the developer "
    observations_desc += "portal page. https://backscatter.io/developers#observations"
    setup_parser = subs.add_parser('observations', description=observations_desc)
    setup_parser.add_argument('-q', '--query', dest='query', required=True,
                              help='Query to search with.', type=str)
    setup_parser.add_argument('-t', '--type', dest='query_type', required=False,
                              help='Query type to search.', type=str,
                              choices=['ip', 'network', 'asn', 'port', 'country'])
    setup_parser.add_argument('-f', '--format', dest='format', required=False,
                              help='Output format of the results.', type=str,
                              default='table', choices=['table', 'json'])
    setup_parser.add_argument('--scope', dest='scope', required=False,
                              help='Days to search back through.', type=str)

    trends_desc = "Query trends endpoints as documented on the developer "
    trends_desc += "portal page. https://backscatter.io/developers#trends"
    setup_parser = subs.add_parser('trends', description=trends_desc)
    setup_parser.add_argument('-t', '--type', dest='trend_type', required=True,
                              help='Trend type to search.', type=str)
    setup_parser.add_argument('-c', '--count', dest='count', required=False,
                              help='Number of results to show.', type=str)
    setup_parser.add_argument('-f', '--format', dest='format', required=False,
                              help='Output format of the results.', type=str,
                              default='table', choices=['table', 'json'])
    setup_parser.add_argument('--scope', dest='scope', required=False,
                              help='Days to search back through.', type=str)

    enrich_desc = "Query enrichment endpoints as documented on the developer "
    enrich_desc += "portal page. https://backscatter.io/developers#enrichment"
    setup_parser = subs.add_parser('enrich', description=enrich_desc)
    setup_parser.add_argument('-q', '--query', dest='query', required=True,
                              help='Query to search with.', type=str)
    setup_parser.add_argument('-t', '--type', dest='query_type', required=False,
                              help='Query type to search.', type=str,
                              choices=['ip', 'network', 'asn', 'port'])
    setup_parser.add_argument('-f', '--format', dest='format', required=False,
                              help='Output format of the results.', type=str,
                              default='table', choices=['table', 'json'])

    args = parser.parse_args()

    if not args.cmd:
        parser.print_help()

    if not os.path.exists(CONFIG_PATH):
        os.makedirs(CONFIG_PATH)
    if not os.path.exists(CONFIG_FILE):
        json.dump(CONFIG_DEFAULTS, open(CONFIG_FILE, 'w'), indent=4,
                  separators=(',', ': '))

    if args.cmd == 'setup':
        config = CONFIG_DEFAULTS
        config['api_key'] = args.api_key
        json.dump(config, open(CONFIG_FILE, 'w'), indent=4,
                  separators=(',', ': '))

    config = json.load(open(CONFIG_FILE))
    if config['api_key'] == '':
        raise Exception("Run setup before any other actions!")

    bs = Backscatter(api_key=config['api_key'], version=config['version'])

    if args.cmd == 'observations':
        kwargs = {'query': args.query, 'query_type': args.query_type}
        if args.scope:
            kwargs.update({'scope': args.scope})
        if not args.query_type:
            query_type = detect_query(args.query)
            if not query_type:
                raise Exception("Ambiguous query type found, please specify using the '-t' option.")
            kwargs.update({'query_type': query_type})
        response = bs.get_observations(**kwargs)
        if not response['success']:
            print('[!] %s: %s' % (response['error'], response['message']))
            sys.exit(1)

        if args.format == 'json':
            print(json.dumps(response, indent=4))
        else:
            results = response.get('results', dict())
            observations = sorted(results.get('observations', list()),
                                  key=lambda k: k['observed'], reverse=True)
            for idx, item in enumerate(observations):
                item = {'Observed': item['observed'],
                        'Source IP': item['src_ip'],
                        'Protocol': item['protocol'],
                        'Flags': item.get('fragment_flags', 'N/A'),
                        'ID': item['id'],
                        'Dest. Port': item.get('dst_port', 'N/A'),
                        'Length': item['length'],
                        'TTL': item['ttl'],
                        'TOS': item['tos'],
                        'Precedence': item['precedence'],
                        'Window': item.get('window', 'N/A')}
                observations[idx] = item
            print("Summary")
            print(''.join(['=' for x in range(0, len("Summary"))]))
            data = [{'key': k.replace('_', ' ').title(), 'value': v} for k, v in results['summary'].items()]
            print(tabulate(data, tablefmt="fancy_grid"))
            print("")
            title = "Observations %s - Now" % (response['query']['after_time'].replace('-', '/'))
            print(title)
            print(''.join(['=' for x in range(0, len(title))]))
            print(tabulate(observations, headers="keys", tablefmt="fancy_grid"))

    if args.cmd == 'trends':
        kwargs = {'trend_type': args.trend_type}
        if args.scope:
            kwargs.update({'scope': args.scope, 'size': args.count})
        response = bs.get_trends(**kwargs)
        if not response['success']:
            print('[!] %s: %s' % (response['error'], response['message']))
            sys.exit(1)

        if args.format == 'json':
            output = json.dumps(response, indent=4)
            print(output)
        else:
            results = response.get('results', dict())
            results = OrderedDict(sorted(results.items(), key=itemgetter(1),
                                         reverse=True))
            records = list()
            for k, v in results.items():
                records.append({'Value': k, 'Count': v})
            scope = response['query']['scope'].split('-')[1].replace('d', '-Day')
            title = "Top %s %s %s Trends" % (args.count, scope,
                                             args.trend_type.title())
            print(title)
            print(''.join(['=' for x in range(0, len(title))]))
            print(tabulate(records, headers="keys", tablefmt="fancy_grid"))

    if args.cmd == 'enrich':
        kwargs = {'query': args.query, 'query_type': args.query_type}
        if not args.query_type:
            query_type = detect_query(args.query)
            args.query_type = query_type
            if not query_type:
                raise Exception("Ambiguous query type found, please specify using the '-t' option.")
            kwargs.update({'query_type': query_type})
        response = bs.enrich(**kwargs)
        if not response['success']:
            print('[!] %s: %s' % (response['error'], response['message']))
            sys.exit(1)

        if args.format == 'json':
            output = json.dumps(response, indent=4)
            print(output)
        else:
            if args.query_type in ['ip', 'network', 'asn']:
                results = response.get('results', dict())
                data = list()
                for k, v in results.items():
                    if isinstance(v, list):
                        v = ', '.join(v)
                    data.append({'Key': k.replace('_', ' ').title(),
                                 'Value':v})
                print(tabulate(data, headers="keys", tablefmt="fancy_grid"))
            else:
                results = response.get('results', list())
                print(tabulate(results, headers="keys", tablefmt="fancy_grid"))