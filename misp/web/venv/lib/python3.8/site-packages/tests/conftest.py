import time

import pytest
import requests_mock

from trustar import TruStar

BASE_URL = "/api/1.3"


@pytest.fixture
def mocked_request():
    with requests_mock.Mocker() as m:
        m.post(url="/oauth/token", text='{"access_token": "XXXXXXXXXXXXXXXXXXX"}')
        yield m


@pytest.fixture
def trustar():
    return TruStar(config_role='staging')


@pytest.fixture
def numbered_page():
    return {'items': [{"id": 1, "name": "mock"}],
            'pageNumber': 1, 'pageSize': 1,
            'totalElements': 1, 'hasNext': False}


@pytest.fixture
def current_time_millis():
    return int(time.time()) * 1000


@pytest.fixture
def milliseconds_in_a_day():
    return 24 * 60 * 60 * 1000


@pytest.fixture
def get_user_enclaves_fixture():
    return [{'id': 'xxxxxxx-xxx-xxxx-xxxx-xxxxxxxxxxx1', 'name': 'Community',
             'type': 'COMMUNITY', 'read': True, 'create': True, 'update': False},
            {'id': 'xxxxxxxx-xxxx-xxxx-xxxxxxxxxxxxxxx2', 'name': 'ncfta_stash',
             'type': 'INTERNAL', 'read': True, 'create': True, 'update': True},
            {'id': 'xxxxxxxx-xxxx-xxxx-xxxxxxxxxxxxxxx3',
             'name': 'not a real enclave',
             'type': 'INTERNAL', 'read': True, 'create': True, 'update': True},
            {'id': 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxx4',
             'name': 'Nemo Research',
             'type': 'RESEARCH', 'read': True, 'create': False, 'update': False}]
