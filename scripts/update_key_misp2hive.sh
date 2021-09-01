#/bin/bash
# Check service
curl -k -I https://localhost/users/login 
# Extract mispkey
misp_key=$(sudo docker exec -u 0 -it db  mysql --user="misp" --password="example"  --database="misp" -se "select authkey from users ;") 
# Set MISP key
misp_key=$(sed '1,2d' <<< $misp_key)
misp_key=$(echo ${misp_key//[$'\t\r\n ']})
sed -i "s/REPLACEMEMISP/${misp_key}/g" /opt/hunter-on-the-sky/opt/hunter-on-the-sky/thehive/application.conf
# Restart Service
sudo docker restart thehive4