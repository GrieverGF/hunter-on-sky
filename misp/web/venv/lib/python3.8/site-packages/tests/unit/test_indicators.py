import pytest

from tests.conftest import BASE_URL
from trustar import IndicatorType, NumberedPage, Indicator, Tag

URL_ENDPOINT = BASE_URL + "/indicators"
URL_ENDPOINT_WHITELIST = BASE_URL + "/whitelist"


@pytest.fixture
def indicators(current_time_millis, milliseconds_in_a_day):
    return [Indicator(value="1.5.8.7",
                      first_seen=current_time_millis - milliseconds_in_a_day,
                      last_seen=current_time_millis,
                      sightings=100, source="Somewhere", notes="This is a note."),
            Indicator(value="1.5.8.9",
                      first_seen=current_time_millis - milliseconds_in_a_day,
                      last_seen=current_time_millis,
                      sightings=50, source="Somewhere else", notes="This is another note.")]


@pytest.fixture
def indicators_dict(indicators):
    return [i.to_dict() for i in indicators]


@pytest.fixture
def tags(trustar):
    return [Tag(name="tag_1", enclave_id=trustar.enclave_ids[0]), Tag(name="tag_2", enclave_id=trustar.enclave_ids[0])]


def test_get_indicator_details(mocked_request, trustar, indicators_dict):
    _url = f"{URL_ENDPOINT}/details?indicatorValues={indicators_dict[0]['value']}" \
           f"&indicatorValues={indicators_dict[1]['value']}"
    mocked_request.get(url=_url, json=indicators_dict)
    details = trustar.get_indicator_details([indicators_dict[0]['value'], indicators_dict[1]['value']])
    assert details[0].to_dict() == indicators_dict[0]


@pytest.mark.parametrize("indicator_type", (IndicatorType.CVE, IndicatorType.MALWARE, None))
def test_community_trends(mocked_request, trustar, indicators_dict, indicator_type):
    _url = f"{URL_ENDPOINT}/community-trending"
    if indicator_type:
        _url += f"?type={indicator_type}"
    indicators_dict[0]["indicatorType"] = indicator_type
    mocked_request.get(url=_url, json=indicators_dict)
    result = trustar.get_community_trends(indicator_type=indicator_type)
    assert result[0].type == indicator_type


def test_add_to_whitelist(mocked_request, trustar, indicators):
    expected = [i.to_dict(remove_nones=True) for i in indicators]
    mocked_request.post(url=f"{URL_ENDPOINT_WHITELIST}", json=expected)
    result = trustar.add_terms_to_whitelist(expected)
    # TODO implement __eq__ method for indicators
    assert [i.to_dict(remove_nones=True) for i in result] == expected


def test_get_whitelist_page(mocked_request, trustar, numbered_page, indicators_dict):
    numbered_page["items"] = indicators_dict
    mocked_request.get(url=f"{URL_ENDPOINT_WHITELIST}", json=numbered_page)
    result = trustar.get_whitelist_page()
    assert result.to_dict() == numbered_page


def test_delete_indicator_from_whitelist(mocked_request, trustar, indicators):
    _url = f"{URL_ENDPOINT_WHITELIST}?value={indicators[0].value}" \
           f"&firstSeen={indicators[0].first_seen}&lastSeen={indicators[0].last_seen}" \
           f"&source={indicators[0].source}&notes={indicators[0].notes}"
    mocked_request.delete(url=_url)
    trustar.delete_indicator_from_whitelist(indicator=indicators[0])


def test_get_related_indicators(mocked_request, trustar, numbered_page):
    _url = f"{URL_ENDPOINT}/related?indicators=evil&indicators=1.2.3.4&indicators=wannacry"
    mocked_request.get(url=_url, json=numbered_page)
    result = trustar.get_related_indicators_page(indicators=("evil", "1.2.3.4", "wannacry"))
    assert isinstance(result, NumberedPage)


def test_search_indicators(mocked_request, trustar, numbered_page):
    mocked_request.post(f"{URL_ENDPOINT}/search?pageNumber=0", json=numbered_page)
    indicators = trustar.search_indicators("abc")
    assert len(list(indicators)) > 0


def test_submit_indicators(mocked_request, trustar, indicators, tags):
    mocked_request.post(f"{URL_ENDPOINT}")
    # TODO test exceptions
    trustar.submit_indicators(indicators=indicators, tags=tags)


def test_get_indicator_metadata(mocked_request, trustar, indicators, tags):
    indicators[0].tags = [tags[0]]
    mocked_request.post(f"{URL_ENDPOINT}/metadata", json=[indicators[0].to_dict(remove_nones=True)])
    expected = trustar.get_indicator_metadata(value=indicators[0].value)
    # TODO implement __eq__ for indicator and tags.
    assert expected['indicator'].to_dict() == indicators[0].to_dict()
    assert expected['tags'][0].to_dict() == indicators[0].tags[0].to_dict()
    assert expected['enclaveIds'] == indicators[0].enclave_ids


@pytest.mark.parametrize("indicator_type", (IndicatorType.IP, IndicatorType.URL, None))
def test_get_indicators_metadata(mocked_request, trustar, indicators, indicators_dict, indicator_type):
    for i in indicators:
        i.type = indicator_type
    mocked_request.post(url=f"{URL_ENDPOINT}/metadata", json=indicators_dict)
    metadata = trustar.get_indicators_metadata(indicators)
    assert len(metadata) == 2


def test_add_tag_to_indicator(mocked_request, trustar, tags):
    expected = tags[0].to_dict()
    mocked_request.post(f"{URL_ENDPOINT}/tags", json=expected)
    result = trustar.add_indicator_tag("blah.com", name="indicator_tag", enclave_id=trustar.enclave_ids[0])
    assert result.to_dict() == expected


def test_get_indicator_tags(mocked_request, trustar, tags):
    expected = [tags[0].to_dict()]
    _url = f"{URL_ENDPOINT}/tags?enclaveIds={trustar.enclave_ids[0]}"
    mocked_request.get(url=_url, json=expected)
    response = trustar.get_all_indicator_tags()
    assert expected[0] == response[0].to_dict()


def test_delete_tag_from_indicator(mocked_request, trustar):
    metadata = "xxxxx"
    tag_id = 12345
    mocked_request.delete(url=f"{URL_ENDPOINT}/tags/{tag_id}?value={metadata}")
    trustar.delete_indicator_tag(metadata, tag_id=tag_id)
