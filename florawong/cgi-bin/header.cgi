#!/usr/local/bin/perl -w 
###############
# Footer
#
# by Mike Wheeler
#
# Command-O Software
#
# Copyright 1996
#put this on page you want footer to appear 
# All Rights Reserved
###############

$footer_file = "../header.txt";          

print "Content-type: text/html\n\n";
open(FILE,"$footer_file");


while (  <FILE> ) {
print $_;
}
exit;