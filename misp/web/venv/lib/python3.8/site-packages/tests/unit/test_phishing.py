from tests.conftest import BASE_URL
from trustar import CursorPage

PRIORITY_EVENT_SCORE = (3, 2, 1)
NORMALIZED_INDICATOR_SCORE = (3, 2, 1)
STATUSES = ("UNRESOLVED", "CONFIRMED", "IGNORED")
URL_ENDPOINT = BASE_URL + "/triage"


def test_get_phishing_submissions_page(mocked_request, trustar):
    mocked_request.post(url=f"{URL_ENDPOINT}/submissions", json={"items": [{"submissionId": "1234"}],
                                                                 'responseMetadata': {'nextCursor': ''}})
    page = trustar.get_phishing_submissions_page(priority_event_score=PRIORITY_EVENT_SCORE,
                                                 status=STATUSES)
    assert isinstance(page, CursorPage)
    assert isinstance(page.items, list)
    assert isinstance(page.response_metadata, dict)
    expected = {'items': [{'submissionId': '1234'}], 'responseMetadata': {'nextCursor': ''}}
    assert expected == page.to_dict(remove_nones=True)


def test_get_phishing_indicators_page(mocked_request, trustar):
    mocked_request.post(url=f"{URL_ENDPOINT}/indicators", json={'items': [
        {'indicatorType': 'IP', 'value': '220.178.71.156', 'sourceKey': 'alienvault_otx'}],
        'responseMetadata': {'nextCursor': ''}})
    page = trustar.get_phishing_indicators_page(
        normalized_indicator_score=NORMALIZED_INDICATOR_SCORE,
        priority_event_score=PRIORITY_EVENT_SCORE,
        status=STATUSES)
    assert isinstance(page, CursorPage)
    assert isinstance(page.items, list)
    assert isinstance(page.response_metadata, dict)
    expected = {'items': [{'indicatorType': 'IP', 'value': '220.178.71.156', 'sourceKey': 'alienvault_otx'}],
                'responseMetadata': {'nextCursor': ''}}
    assert expected == page.to_dict(remove_nones=True)
