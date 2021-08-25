CORTEX_APP_URL='127.0.0.1'
API_KEY='vRwsz7ni9nwMIUIb9c3n0WUvFF/kvEnw'

#testlog

curl -H "Authorization: Bearer $API_KEY" 'http://127.0.0.1:9001/api/user'

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

echo $hive_key

sed -i "s#REPLACEMECORTEX#${hive_key}#g" thehive/application.conf
sudo docker restart thehive4



