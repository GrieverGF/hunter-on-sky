"""
This script will delete all reports for your enclaves that were submitted yesterday.
This is just an example, DO NOT RUN THIS UNLESS YOU ARE SURE YOU REALLY WANT TO!!!
"""

from datetime import datetime, timedelta

from trustar import datetime_to_millis, log, TruStar

# initialize SDK
ts = TruStar()

# initialize logger
logger = log.get_logger(__name__)

# set 'from' to the start of yesterday and 'to' to the end of yesterday
to_time = datetime.now() - timedelta(days=1)
from_time = to_time - timedelta(days=1)

# convert to millis since epoch
to_time = datetime_to_millis(to_time)
from_time = datetime_to_millis(from_time)

# keep a count of how many reports have been deleted
count = 0

# initialize reports list to None
reports = None

# Loop until no reports remain.  We can use the "get_reports_page" method
# without adjusting the time frame on subsequent calls, since we know that
# no reports will be repeated (because they are being deleted).
while reports is None or len(reports) > 0:
    try:

        # get all reports from the specified enclaves and in the given time interval
        reports = ts.get_reports_page(from_time=from_time,
                                      to_time=to_time,
                                      is_enclave=True,
                                      enclave_ids=ts.enclave_ids)

        # delete each report in the page
        for report in reports:
            logger.info("Deleting report %s" % report.id)
            ts.delete_report(report_id=report.id)
            count += 1

    except Exception as e:
        logger.error("Error: %s" % e)

logger.info("Deleted %d reports." % count)
