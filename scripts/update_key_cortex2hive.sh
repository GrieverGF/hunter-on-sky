#/bin/bash
CORTEX_APP_URL='127.0.0.1'
API_KEY='vRwsz7ni9nwMIUIb9c3n0WUvFF/kvEnw'

#create org
curl -XPOST -H "Authorization: Bearer $API_KEY" -H 'Content-Type: application/json' "http://$CORTEX_APP_URL:9001/api/organization" -d '{
  "name": "DemoOrg",
  "description": "Demo organization",
  "status": "Active"
}'

# Create Admnin ORG
curl -XPOST -H "Authorization: Bearer $API_KEY" -H 'Content-Type: application/json' "http://$CORTEX_APP_URL:9001/api/user" -d '{
  "name": "Demo org Admin",
  "roles": [
    "read",
    "analyze",
    "orgadmin"
  ],
  "organization": "DemoOrg",
  "login": "hive2cortex"
}'

#  set adming org key
hive_key=$(curl -XPOST -H "Authorization: Bearer $API_KEY" "http://$CORTEX_APP_URL:9001/api/user/hive2cortex/key/renew") 
sed -i "s#REPLACEMECORTEX#${hive_key}#g" /opt/hunter-on-the-sky/thehive/application.conf
# Restart service
sudo docker restart thehive4



