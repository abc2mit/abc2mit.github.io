#!/ramdisk/bin/python

# server_version.py - retrieve and display database server version
import MySQLdb
import sys
import cgitb
cgitb.enable()
print "Content-Type: text/plain;charset=utf-8"
print

# print "1 Hello World!"
try:
  conn = MySQLdb.connect(host = "localhost",
                       user = "digressi_coson",
                       passwd = "yelpkiller",
                       db = "digressi_coson")
except MySQLdb.Error, e:
  print "Error %d: %s" % (e.args[0], e.args[1])
  sys.exit(1)

# print "2 Hello World!"
cursor = conn.cursor()
# print "3 Hello World!"
cursor.execute("SELECT VERSION()")
# print "4 Hello World!"
row = cursor.fetchone()
# print "5 Hello World!"
print "server version:", row[0]
# print "6 Hello World!"
cursor.close()
# print "7 Hello World!"
conn.close()
# print "8 Hello World!"
