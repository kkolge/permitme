host = "localhost"
port = "3306"
username = "permitme"
password = "SuuOFar2IpR95NgD$"
database = "permitmemass_25Apr21"

import pandas as pd
import sqlalchemy as sqla
import matplotlib.pyplot as plt
from datetime import date, timedelta

print("Starting program...")

print ("Connecting to DB to get data")
conn_string = 'mysql+pymysql://'+username+':'+password+'@'+host+':'+port+'/'+database
#print(conn_string)
engine = sqla.create_engine(conn_string)

#query = "select device, fordate, highpulserate, lowspo2, hightemp, highpulseratelowspo2, highpulseratehightemp, lowspo2hightemp, allabnormal, allnormal from iotdatasummary where fordate >=  date_sub(curdate(), interval 15 day) " 

#query = """select fordate, SUM(HIGHTEMP)+ SUM(highpulseratehightemp)+SUM(lowspo2hightemp)+ SUM(highpulseratelowspo2) + SUM(allabnormal) as allnormal from iotdatasummary where fordate >= date_sub(curdate(), interval 60 day) GROUP BY fordate"""

query3 = """select fordate, device, hightemp, highpulseratehightemp, lowspo2hightemp, highpulseratelowspo2, allabnormal, allnormal from iotdatasummary """


df = pd.read_sql_query(query3,engine,parse_dates='fordate', index_col='fordate')

#print('Sample data...')
#print (df.head())

#generating plot 
#dfDEV = df[df.device=='DEVTEST10']
#print(dfDEV.head(10))
#print(dfDEV['allnormal'])
#print (df.index)

dfGroupByDate = df.groupby("fordate")

print(dfGroupByDate)


plt.plot( dfGroupByDate.allabnormal.sum())

plt.show()

print("End program...")

