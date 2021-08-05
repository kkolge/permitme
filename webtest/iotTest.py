import hashlib
import requests
import json
import random
import time

url1 = 'http://localhost/api/validateDevice'
url2 = 'http://localhost/api/updateDevStatus'
url3 = 'http://localhost/api/saveDeviceData'

deviceid = 'DEVKODEMO11'
macid = '483FDA7D'
random1 = (hashlib.md5(deviceid.encode())).hexdigest()
random2 = 'n9lONZwVD8VCL8Y7'

data = {'deviceid':deviceid,'macid':macid,'random1':random1,'random2':random2}
headers = {'Content-Type': 'application/json', 'Accept':'text/json', 'User-Agent':'PermitMe'}

print(deviceid, macid, random1, random2)
print(json.dumps(data))

resp = requests.post(url=url1, data=json.dumps(data), headers=headers)

if resp.status_code == 200 :
    resp_dict = json.loads(resp.text)
    print('status:', resp_dict['status'])
    print('random2:', resp_dict['random2'])
    #returning the success for save token
    data = {'random1':random1,'random2':resp_dict['random2'], 'status':'success'}
    resp1 = requests.post(url=url2, data=json.dumps(data), headers=headers)
    if resp.status_code == 200 :
        for x in range(1000):
            data = {'deviceid': deviceid,'random1': random1,'random2': resp_dict['random2'],'identifier': '7718865005','temp': random.randint(87,98), 'spo2': random.randint(85,100), 'hbcount': random.randint(110,130)}
            print(json.dumps(data))
            resp2 = requests.post(url=url3, data=json.dumps(data), headers=headers)
            if resp2.status_code == 200:
                print("OK")
            else:
                print (resp2.status_code)
else :
    print(resp.headers)
    print(resp.status_code)

#print(resp.status_code)
#print(resp.json())
