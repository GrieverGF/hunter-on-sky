import pytest
from requests.exceptions import HTTPError

from tests.conftest import BASE_URL

URL_ENDPOINT = BASE_URL + "/enclaves"


def test_trustar_get_enclaves(mocked_request, trustar, get_user_enclaves_fixture):
    mocked_request.get(url=f"{URL_ENDPOINT}", json=get_user_enclaves_fixture)
    assert len(trustar.get_user_enclaves()) == 4


def test_trustar_get_exception(mocked_request, trustar):
    mocked_request.get(url=f"{URL_ENDPOINT}", exc=HTTPError)
    with pytest.raises(HTTPError):
        trustar.get_user_enclaves()
