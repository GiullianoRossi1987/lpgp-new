#!/bin/bash

# That script set all the upload folders permissions, making they able to receive the 
# PHP uploads

chmod 777 -R ./usignatures.d
chmod 777 -R ./signatures.d

chmod 777 -R ./g.clients
chmod 777 -R ./u.clients

chmod 777 -R ./u.images

echo "Reset done!"
