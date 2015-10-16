#!/ramdisk/bin/python
import sys, os

# Add a custom Python path.
sys.path.insert(0, "/home4/digressi/.local/lib/python")

# Switch to the directory of your project. (Optional.)
os.chdir("/home4/digressi/.local/lib/python/mocoso")

# Set the DJANGO_SETTINGS_MODULE environment variable.
os.environ['DJANGO_SETTINGS_MODULE'] = "mocoso.settings"

from django.core.servers.fastcgi import runfastcgi
runfastcgi(method="threaded", daemonize="false")
