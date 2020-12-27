# That program should work only on CMD

echo "Cleaning upload folders"
DEL g.clients/*.zip
del u.clients/*.lpgp
del usignatures.d/*.lpgp
del signatures.d/*.lpgp
echo "Done!"
