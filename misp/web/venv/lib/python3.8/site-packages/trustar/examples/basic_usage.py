#!/usr/bin/env python

"""
Comprehensive script with various TruSTAR API usage examples
"""

from __future__ import print_function

import sys
import time
from random import randint

from trustar import log, Report, TruStar


logger = log.get_logger(__name__)

do_latest_reports = True
do_correlated = True
do_get_indicators = True
do_community_trends = True
do_query_indicators = True
do_comm_submissions = True
do_enclave_submissions = True

do_submit_report = True
do_report_details_by_ext_id = True
do_update_report_by_ext_id = True
do_report_details_by_guid = True
do_update_report_by_guid = True
do_release_report_by_ext_id = True
do_report_details_by_ext_id_2 = True
do_delete_report_by_ext_id = True
do_add_enclave_tag = True
do_delete_enclave_tag = True
do_get_enclave_tags = True
do_reports_by_community = True
do_reports_by_enclave = True
do_reports_mine = True
do_search_reports = True
do_search_indicators = True
do_redact_report = True

# search_string = "1.2.3.4 8.8.8.8 10.0.2.1 185.19.85.172 art-archiv.ru"
search_string = ','.join([
    "167.114.35.70",
    "103.255.61.39",
    "miel-maroc.com",
    "malware.exe"
])

submit_indicators = ' '.join([
    "google.com",
    "malware.exe",
    "103.255.61.39"
])


def to_milliseconds(days):
    """
    :return: the number of days expressed as milliseconds. e.g to_milliseconds(1) -> 86400
    """
    return days * 24 * 60 * 60 * 1000


def main():
    role = "trustar"
    if len(sys.argv) > 1:
        role = sys.argv[1]

    ts = TruStar(config_file="trustar.conf", config_role=role)

    # generate random id to use as external_id
    external_id = str(randint(1, 100000))

    # or use a specific external_id
    # external_id = "321"

    report_guid = None
    current_time = int(time.time()) * 1000
    yesterday_time = current_time - to_milliseconds(days=1)

    if do_latest_reports:

        logger.info("Getting Latest Accessible Incident Reports Since 24 hours ago ...")
        try:

            # get each successive page of reports
            report_generator = ts.get_reports(from_time=yesterday_time,
                                              to_time=current_time,
                                              is_enclave=True,
                                              enclave_ids=ts.enclave_ids)

            for report in report_generator:
                logger.info(report)

        except Exception as e:
            logger.error('Could not get latest reports, error: %s' % e)

        print('')

    if do_reports_by_community:

        two_days_ago = current_time - to_milliseconds(days=2)

        logger.info("Getting community only reports for the previous day ...")
        try:
            reports = ts.get_reports(from_time=two_days_ago,
                                     to_time=yesterday_time,
                                     is_enclave=False)

            for report in reports:
                logger.info(report)

        except Exception as e:
            logger.error('Could not get community reports, error: %s' % e)

        print('')

    if do_reports_by_enclave:

        a_week_ago = current_time - to_milliseconds(days=7)

        logger.info("Getting enclave only reports for the previous week ...")
        try:
            reports = ts.get_reports(from_time=a_week_ago,
                                     to_time=current_time,
                                     is_enclave=True,
                                     enclave_ids=ts.enclave_ids)

            for report in reports:
                logger.info(report)

        except Exception as e:
            logger.error('Could not get community reports, error: %s' % e)

        print('')

    if do_correlated:
        logger.info("Querying Accessible Correlated Reports...")
        try:
            report_ids = ts.get_correlated_report_ids(search_string)

            logger.info(report_ids)
            logger.info("%d report(s) correlated with indicators '%s':\n" % (len(report_ids), search_string))
            logger.info("\n".join(report_ids))
        except Exception as e:
            logger.error('Could not get correlated reports, error: %s' % e)

        print('')

    if do_community_trends:
        logger.info("Get community trends...")

        try:
            indicators = ts.get_community_trends(indicator_type=None,
                                                 days_back=1)
            for indicator in indicators:
                logger.info(indicator)
        except Exception as e:
            logger.error('Could not get community trends, error: %s' % e)

        print('')

    if do_query_indicators:
        try:
            logger.info("Getting related indicators...")
            indicators = ts.get_related_indicators(indicators=search_string)
            for indicator in indicators:
                logger.info(indicator)
        except Exception as e:
            logger.error('Could not get related indicators, error: %s' % e)

    # Submit simple test report to community
    if do_comm_submissions:
        logger.info("Submit New Community Incident Report")
        try:
            report = Report(title="COMMUNITY API SUBMISSION TEST",
                            body=submit_indicators,
                            time_began="2017-02-01T01:23:45",
                            is_enclave=False)
            report = ts.submit_report(report)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))

        except Exception as e:
            logger.error('Could not submit community report, error: %s' % e)

        print('')

    # Submit simple test report to your enclave
    if do_enclave_submissions:
        logger.info("Submit New Enclave Incident Report")

        try:
            report = Report(title="ENCLAVE API SUBMISSION TEST ",
                            body=submit_indicators,
                            time_began="2017-02-01T01:23:45",
                            enclave_ids=ts.enclave_ids)
            report = ts.submit_report(report)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))

            logger.info(report)

        except Exception as e:
            logger.error('Could not submit enclave report, error: %s' % e)

        print('')

    # Submit a test report and retrieve it
    if do_submit_report:
        logger.info("Submit New Enclave Incident Report with External ID")

        try:
            report = Report(title="Sample SDK Test Report",
                            body=submit_indicators,
                            time_began="2017-02-01T01:23:45",
                            is_enclave=True,
                            enclave_ids=ts.enclave_ids,
                            external_id=external_id)
            report = ts.submit_report(report)

            logger.info("Report Submitted")
            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
        except Exception as e:
            logger.error('Could not submit report, error: %s' % e)

        print('')

    # Get test report previously submitted
    if do_report_details_by_ext_id:
        logger.info("Get Incident Report By External ID")
        try:
            report = ts.get_report_details(report_id=external_id, id_type=Report.ID_TYPE_EXTERNAL)

            logger.info("\ttitle: %s" % report.title)
            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
            report_guid = report.id
        except Exception as e:
            logger.error('Could not get report, error: %s' % e)

        print('')

    # Update a test report and test with get report
    if do_update_report_by_ext_id:
        logger.info("Update Incident Report By External ID")
        try:
            report = Report(title="Updated Sample Title",
                            body="updated report body: 21.22.23.24",
                            external_id=external_id,
                            enclave_ids=ts.enclave_ids)
            report = ts.update_report(report)

            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
        except Exception as e:
            logger.error('Could not update report, error: %s' % e)

        print('')

    # Get test report previously submitted
    if do_report_details_by_guid:
        logger.info("Get Incident Report Details by GUID (TruSTAR internal ID)")

        try:
            report = ts.get_report_details(report_guid)

            logger.info("\ttitle: %s" % report.title)
            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
        except Exception as e:
            logger.error('Could not get report, error: %s' % e)

        print('')

    # Update a test report and test with get report
    if do_update_report_by_guid:
        logger.info("Update Incident Report by GUID (TruSTAR internal ID)")
        try:
            report = Report(id=report_guid,
                            title="New Sample Title",
                            body="new sample body - 7.8.9.10",
                            enclave_ids=ts.enclave_ids)
            report = ts.update_report(report)

            logger.info("Updated Report using GUID")
            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
        except Exception as e:
            logger.error('Could not update report, error: %s' % e)

        print('')

    # Get test report previously submitted
    if do_report_details_by_guid:
        logger.info("Get Report by GUID (TruSTAR internal ID)")
        try:
            report = ts.get_report_details(report_guid)

            logger.info("\ttitle: %s" % report.title)
            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
        except Exception as e:
            logger.error('Could not get report, error: %s' % e)

        print('')

    # Release report to community
    if do_release_report_by_ext_id:
        logger.info("Release Incident Report by External ID")
        try:
            report = Report(external_id=external_id,
                            is_enclave=False)
            report = ts.update_report(report)

            logger.info("Report Released using External ID:")
            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
        except Exception as e:
            logger.error('Could not release report, error: %s' % e)

        print('')

    # Get test report previously submitted
    if do_report_details_by_ext_id_2:
        logger.info("Get Incident Report Details by External ID")

        try:
            report = ts.get_report_details(report_id=external_id, id_type=Report.ID_TYPE_EXTERNAL)

            logger.info("\ttitle: %s" % report.title)
            logger.info("\texternalTrackingId: %s" % report.external_id)
            logger.info("\tURL: %s\n" % ts.get_report_url(report.id))
        except Exception as e:
            logger.error('Could not get report, error: %s' % e)

        print('')

    # Delete test report previously submitted
    if do_delete_report_by_ext_id:
        logger.info("Delete Incident Report by External ID")
        try:
            ts.delete_report(report_id=external_id, id_type=Report.ID_TYPE_EXTERNAL)
            logger.info("Report Deleted using External ID\n")

        except Exception as e:
            logger.error('Could not delete report, error: %s' % e)

        print('')

    # Add an enclave tag to a newly created report
    if do_add_enclave_tag:
        logger.info("Add enclave tag to incident report")

        try:
            # submit report
            report = Report(title="Enclave report with tag",
                            body=submit_indicators,
                            is_enclave=True,
                            enclave_ids=ts.enclave_ids)
            report = ts.submit_report(report)
            logger.info("\tId of new report %s\n" % report.id)

            # get back report details, including the enclave it's in
            report = ts.get_report_details(report_id=report.id)
            enclave_id = report.enclave_ids[0]

            # add an enclave tag
            tag_id = ts.add_enclave_tag(report_id=report.id, name="triage", enclave_id=enclave_id)
            # logger.info the added enclave tag
            logger.info("\tId of new enclave tag %s\n" % tag_id)

            # add another enclave tag
            tag_id = ts.add_enclave_tag(report_id=report.id, name="resolved", enclave_id=enclave_id)
            # logger.info the added enclave tag
            logger.info("\tId of new enclave tag %s\n" % tag_id)

            # Get enclave tag info
            if do_get_enclave_tags:
                logger.info("Get enclave tags for report")
                tags = ts.get_enclave_tags(report.id)
                logger.info("\tEnclave tags for report %s\n" % report.id)
                logger.info(tags)

            # delete enclave tag by name
            if do_delete_enclave_tag:
                logger.info("Delete enclave tag from report")
                response = ts.delete_enclave_tag(report.id, tag_id)
                logger.info("\tDeleted enclave tag for report %s\n" % report.id)
                logger.info(response)

            # add it back
            ts.add_enclave_tag(report_id=report.id, name="triage", enclave_id=enclave_id)

            # List all enclave tags
            tags = ts.get_all_enclave_tags(enclave_ids=ts.enclave_ids)
            logger.info("List of enclave tags for enclave %s\n" % enclave_id)
            logger.info(tags)

            # Search report by tag
            logger.info("Getting reports tagged 'triage'.")
            reports = ts.get_reports(from_time=yesterday_time,
                                     to_time=current_time,
                                     enclave_ids=ts.enclave_ids,
                                     tag="triage")

            for report in reports:
                logger.info(report)

        except Exception as e:
            logger.error('Could not handle enclave tag operation, error: %s' % e)

        print('')

    # search for reports containing term "abc"
    if do_search_reports:

        try:
            logger.info("Searching reports:")

            reports = ts.search_reports("abc")
            for report in reports:
                logger.info(report)

        except Exception as e:
            logger.error("Could not search reports, error: %s" % e)

        print('')

    # search for indicators matching pattern "abc"
    if do_search_indicators:

        try:
            logger.info("Searching indicators:")

            indicators = ts.search_indicators("abc")
            for indicator in indicators:
                logger.info(indicator)

        except Exception as e:
            logger.error("Could not search indicators, error: %s" % e)

        print('')

    if do_redact_report:

        try:
            logger.info("Redacting report:")

            redacted_report = ts.redact_report(title="amazon phishing scam",
                                               report_body="apple, microsoft, and amazon suffered "
                                                           "from a phishing scam via evil@amazon.com")
            logger.info(redacted_report)

        except Exception as e:
            logger.error("Could not redact report, error: %s" % e)

        print('')


if __name__ == '__main__':
    main()
