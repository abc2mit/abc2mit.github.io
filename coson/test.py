#!/ramdisk/bin/python
# -*- coding: UTF-8 -*-

# enable debugging
import cgitb
cgitb.enable()

print "Content-Type: text/plain;charset=utf-8"
print

print "Hello World!"


#import MySQLdb

#conn = MySQLdb.connect (host = "localhost",
                       user = "digressi_coson",
                       passwd = "yelpkiller",
                       db = "digressi_coson")
#cursor = conn.cursor ()
#cursor.execute ("SELECT VERSION()")
#row = cursor.fetchone ()
#print "server version:", row[0]
#cursor.close ()
#conn.close ()
