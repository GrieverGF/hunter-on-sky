#/bin/bash
CORTEX_APP_URL='127.0.0.1'
API_KEY='vRwsz7ni9nwMIUIb9c3n0WUvFF/kvEnw'


#  set adming org key
adminorg_key=$(curl -H "Authorization: Bearer $API_KEY" "http://$CORTEX_APP_URL:9001/api/user/hive2cortex/key") 
echo $adminorg_key


#curl -H "Authorization: Bearer $adminorg_key" "http://$CORTEX_APP_URL:9001/api/analyzer?range=all"

# Restart service
#sudo docker restart thehive4


curl -XPOST -H "Authorization: Bearer $adminorg_key" "http://$CORTEX_APP_URL:9001/api/organization/analyzer/Abuse_Finder" -d '{
  "name": "Abuse_Finder",
  "configuration": {
    "auto_extract_artifacts": false,
    "check_tlp": true,
    "max_tlp": 2
  },
  "rate": 1000,
  "rateUnit": "Day",
  "jobCache": 5
}'

