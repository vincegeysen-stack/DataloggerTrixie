#!/usr/bin/python3

#  Jan Moeskops - 22 september 2019
#  MatplotlibTest2 : test toegang tot de database
#   indien 4 keer per uur vochtigheid gelogd wordt,
#   dan zijn er 96 meetresultaten per dag
#   toon de laatste dag,  zet foto in /var/www/html als sudo
#  probleem: Matplotlib chooses Xwindows backend by default.
#   You need to set matplotlib to not use the Xwindows backend.
#  oplossing: https://stackoverflow.com/questions/37604289/tkinter-tclerror-no-display-name-and-no-display-environment-variable

import os
import matplotlib as mpl
if os.environ.get('DISPLAY','') == '':
    print('no display found. Using non-interactive Agg backend')
    mpl.use('Agg')        # gebruik van Agg wanneer script start uit crontab
import matplotlib.pyplot as plt
import mysql.connector
import time
import shutil
from pathlib import Path

home = Path.home()

# Zoek datum vandaag
vandaag=time.strftime("%d-%m-%Y, %H:%M")
# vandaag=time.strftime("%Y-%m-%d")
print(vandaag)

# connect to MySQL database
conn = mysql.connector.connect(host="localhost", user="logger", passwd="paswoord", db="temperatures")

# prepare a cursor
cur = conn.cursor()

# in deze query selecteren we de 96 laatste metingen
query = """
SELECT dateandtime, humidity  FROM temperaturedata
ORDER BY dateandtime DESC LIMIT 20832;
"""

# execute the query
cur.execute(query)

# retrieve the whole result set
data = cur.fetchall()

# close cursor and connection
cur.close()
conn.close()

# unpack data in TimeStamp (x axis) and Pac (y axis)
dateandtime, temperature = zip(*data)

##print(temperature, end='\n')

# graph code, plot lijntjes,  scatter puntjes
plt.plot(dateandtime, temperature)

# set title, X/Y labels
plt.title("maand temperatuur"+" Raspi16 " + vandaag)
plt.xlabel("Time of Day")
plt.ylabel("graden celsius")
fig = plt.gcf()

# plt.xticks(TimeStamp, (hour))
fig.set_size_inches(10,7)
plt.grid(True)
plt.draw()

# plt.show()
mijnafbeelding = str(home) + "/WEEKvochtigheid.png"
print(mijnafbeelding)
plt.savefig(mijnafbeelding , dpi=100)
