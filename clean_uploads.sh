#!/bin/bash
#############################
# WARNING: If the server is running on a windows based OS
# 			run the clean_uploads.bat

# @author Giuliano Rossi

# That shell-script removes all the files uploadeds to the server, except the u.images folder. 
# Remember to run it every month

rm -f ./g.clients/tmp/*
rm -f ./g.clients/*.zip

rm -f ./u.clients/tmp/*
rm -f ./u.clients/*.zip

rm -f ./usignatures.d/*.lpgp

rm -f ./signatures.d/*.lpgp