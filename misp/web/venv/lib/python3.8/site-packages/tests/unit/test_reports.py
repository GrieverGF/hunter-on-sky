import pytest

from trustar import Report, IdType
from tests.conftest import BASE_URL, mocked_request

FAKE_REPORT_ID = 45
URL_ENDPOINT = BASE_URL + "/reports"
ORIGINAL_TAG = 'original_tag'


@pytest.fixture
def report(trustar, current_time_millis, milliseconds_in_a_day):
    original_report = Report(title="Report 1",
                             id=FAKE_REPORT_ID,
                             body="Blah blah blah",
                             time_began=current_time_millis - milliseconds_in_a_day,
                             enclave_ids=trustar.enclave_ids)
    return original_report


@pytest.fixture
def original_report(mocked_request, trustar, report):
    # TODO test different report types, and different return types
    mocked_request.post(url=URL_ENDPOINT, text=str(report.id))
    o_r = trustar.submit_report(report=report)
    return o_r


@pytest.fixture()
def copied_report(trustar, original_report):
    copied_report_id = FAKE_REPORT_ID + 1
    dest_enclave_id = trustar.enclave_ids[0]
    return {"id": copied_report_id,
            "reportBody": original_report.body,
            "title": original_report.title,
            "enclaveIds": dest_enclave_id}


def test_delete_report(mocked_request, trustar):
    mocked_request.delete(url=f"{URL_ENDPOINT}/{FAKE_REPORT_ID}")
    trustar.delete_report(FAKE_REPORT_ID)


def test_add_enclave_tag(mocked_request, trustar, original_report):
    mocked_request.post(url=f"{URL_ENDPOINT}/{original_report.id}/alter-tags", json={"id": original_report.id})
    name = trustar.add_enclave_tag(original_report.id, ORIGINAL_TAG, None)
    assert name == ORIGINAL_TAG


def test_copy_report(mocked_request, trustar, original_report):
    dest_enclave_id = trustar.enclave_ids[0]
    _url = f"{URL_ENDPOINT}/copy/{FAKE_REPORT_ID}?destEnclaveId={dest_enclave_id}&copyFromProvidedSubmission=False"
    mocked_request.post(url=_url, json={"id": FAKE_REPORT_ID + 1})
    # TODO test exceptions from_provided_submission True
    copied_report_id = trustar.copy_report(src_report_id=original_report.id,
                                           dest_enclave_id=dest_enclave_id,
                                           from_provided_submission=False)
    assert copied_report_id == FAKE_REPORT_ID + 1


def test_get_enclave_tags(mocked_request, trustar):
    copied_report_id = FAKE_REPORT_ID + 1
    mocked_request.get(url=f"{URL_ENDPOINT}/{copied_report_id}/tags", json=[{"name": ORIGINAL_TAG}])
    original_tags = trustar.get_enclave_tags(copied_report_id)
    assert {ORIGINAL_TAG} == set(t.name for t in original_tags)


def test_get_report_details(mocked_request, trustar, original_report, copied_report):
    mocked_request.get(url=f"{URL_ENDPOINT}/{copied_report['id']}", json=copied_report)
    copied_report_result = trustar.get_report_details(copied_report['id'])
    # TODO: add a __eq__ method to the report class
    assert original_report.title == copied_report_result.title
    assert original_report.body == copied_report_result.body
    assert copied_report_result.enclave_ids[0] == trustar.enclave_ids[0]


def test_move_report(mocked_request, trustar, copied_report):
    _url = f"{URL_ENDPOINT}/move/{copied_report['id']}?destEnclaveId={trustar.enclave_ids[0]}"
    mocked_request.post(url=_url, json=copied_report)
    moved_report_id = trustar.move_report(report_id=copied_report['id'], dest_enclave_id=trustar.enclave_ids[0])
    assert moved_report_id == moved_report_id


def test_delete_enclave_tag(mocked_request, trustar, original_report):
    tag_id = id_type = "XXX-XXXX"
    mocked_request.post(url=f"{URL_ENDPOINT}/{FAKE_REPORT_ID}/alter-tags", json={"id": original_report.id})
    result = trustar.delete_enclave_tag(report_id=original_report.id, tag_id=tag_id, id_type=id_type)
    assert result == original_report.id


def test_get_report_deeplink(mocked_request, trustar, original_report):
    mocked_request.delete(url=f"{URL_ENDPOINT}/{FAKE_REPORT_ID}")
    # TODO check for exceptions
    response = trustar.get_report_deeplink(report=original_report.id)
    assert response == f"{trustar._client.station}/constellation/reports/{original_report.id}"


def test_update_report(mocked_request, trustar, original_report):
    original_report.body = "Bleh bleh bleh"
    mocked_request.put(url=f"{URL_ENDPOINT}/{FAKE_REPORT_ID}?idType={IdType.INTERNAL}")
    # TODO check for exceptions
    report = trustar.update_report(report=original_report)
    # TODO __eq__ method for reports
    assert original_report.body == report.body


def test_search_reports(mocked_request, trustar, numbered_page):
    mocked_request.post(url=f"{URL_ENDPOINT}/search?pageNumber=0", json=numbered_page)
    reports = trustar.search_reports("abc")
    assert len(list(reports)) > 0


def test_get_correlated_reports(mocked_request, trustar):
    indicators = ("evil", "wannacry")
    _url = f"{URL_ENDPOINT}/correlate?indicators={indicators[0]}&indicators={indicators[1]}"
    expected = {"response": "fake"}
    mocked_request.get(url=_url, json=expected)
    result = trustar.get_correlated_report_ids(indicators)
    assert expected == result


def test_get_reports(mocked_request, trustar, numbered_page, current_time_millis):
    from_time = current_time_millis - 60
    tag = "XXXXXX"
    numbered_page["items"][0]["updated"] = from_time
    _url = f"{URL_ENDPOINT}?from={from_time}&to={current_time_millis}&" \
           f"distributionType=COMMUNITY&enclaveIds={trustar.enclave_ids[0]}&tags={tag}"
    mocked_request.get(url=_url, json=numbered_page)
    result = trustar.get_reports(tag=tag, from_time=from_time, to_time=current_time_millis)
    # TODO check the content
    assert len(list(result)) > 0


def test_get_report_status(trustar, mocked_request):
    lookup = "d088bb2b-7df5-469f-b831-0ec3733d33e1"
    expected = {'errorMessage': '', 'id': 'd088bb2b-7df5-469f-b831-0ec3733d33e1', 'status': 'SUBMISSION_SUCCESS'}
    mocked_request = mocked_request.get(url=f"{URL_ENDPOINT}/{lookup}/status", json=expected)
    result = trustar.get_report_status(lookup)
    assert result['status'] == "SUBMISSION_SUCCESS"
