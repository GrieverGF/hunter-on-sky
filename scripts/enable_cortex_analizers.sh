#/bin/bash
CORTEX_APP_URL='127.0.0.1'
API_KEY='vRwsz7ni9nwMIUIb9c3n0WUvFF/kvEnw'


#  set adming org key
hive_key=$(curl -XPOST -H "Authorization: Bearer $API_KEY" "http://$CORTEX_APP_URL:9001/api/user/hive2cortex/key") 
echo $hive_key
# Restart service
#sudo docker restart thehive4



