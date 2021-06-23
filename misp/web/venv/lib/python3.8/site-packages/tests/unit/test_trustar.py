from tests.conftest import BASE_URL


def test_ping(mocked_request, trustar):
    # TODO tests different return values from the ping endpoint
    mocked_request.get(url=f"{BASE_URL}/ping")
    trustar.ping()


