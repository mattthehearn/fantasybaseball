#!/ramdisk/bin/python

import mysql.connector

cnx = mysql.connector.connect(user='matthear_hearnmd', password='',
                              host='127.0.0.1',
                              database='matthear_fantasybaseball')
cnx.close()
