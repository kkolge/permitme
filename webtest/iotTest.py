import hashlib
import requests
import json

url = 'https://testpm.ko-aaham.com/api/validateDevice'

deviceid = 'DEVKODEMO11'
macid = '483FDA7D'
random1 = (hashlib.md5(deviceid.encode())).hexdigest()
random2 = 'jHMeB23scZONn8cx'

data = {'deviceid':deviceid,'macid':macid,'random1':random1,'random2':random2}
headers = {'Content-Type': 'application/json', 'Accept':'text/json', 'User-Agent':'PermitMe'}

print(deviceid, macid, random1, random2)
print(json.dumps(data))

resp = requests.post(url=url, data=json.dumps(data), headers=headers)

if resp.status_code == 200 :
    r = resp.json()
    resp_dict = json.load(r)
    print('status:', resp_dict['status'])
    print('random2:', resp_dict['random2'])

else :
    print(resp.headers)
    print(resp.status_code)

#print(resp.status_code)
#print(resp.json())
