Blockstrap Faucets
==================

This is the source code powering the [Blockstrap Faucets](http://faucets.blockstrap.com).

Need to create a config.ini file and host it at the root (parent of htdocs).

It should look something like this:

```
[salts]
emails = ADD_YOUR_SALT_HERE
addresses = ADD_YOUR_SALT_HERE

[keys]
mandrill = ADD_YOUR_API_KEY_HERE
doget = ADD_YOUR_PRIVATE_KEY_HERE
dasht = ADD_YOUR_PRIVATE_KEY_HERE
ltct = ADD_YOUR_PRIVATE_KEY_HERE
btct = ADD_YOUR_PRIVATE_KEY_HERE

[ports]
btct = ADD_YOUR_PORT_HERE
doget = ADD_YOUR_PORT_HERE
dasht = ADD_YOUR_PORT_HERE
ltct = ADD_YOUR_PORT_HERE

[app]
name = Blockstrap
email = founders@blockstrap.com
subject = Blockstrap Faucet Claim Code

[addresses]
btct = mxaFY8bG7DgH6AGe4dT3EAaTRd3NG96UHk
dasht = xyq7bwGUzPbSaCucM8r9LvhKZwKVfvacDU
doget = nhY8rzwwQBVj7UkPUDsdZGFMXDCyeCd9FK
ltct = mwJ9ebyT8kzaCECyUqwNU8cRj5aU8jL2op
```

Which should result in something like this:

[![PREVIEW](/htdocs/img/preview.png)](http://faucets.blockstrap.com)