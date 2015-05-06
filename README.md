Blockstrap Faucets
==================

This is the source code powering the [Blockstrap Faucets](http://faucets.blockstrap.com).

It currently requires working nodes with active rpc-ports, but does not store any coins there and is only used in order to sign the un-signed raw transactions created within the browser. This is in favour of requiring GMP-PHP.

For it to work you will need to create a config.ini file and host it at the root (parent of htdocs).

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

[usernames]
btct = ADD_YOUR_RPC_USERNAME_HERE
doget = ADD_YOUR_RPC_USERNAME_HERE
dasht = ADD_YOUR_RPC_USERNAME_HERE
ltct = ADD_YOUR_RPC_USERNAME_HERE

[passwords]
btct = ADD_YOUR_RPC_PASSWORD_HERE
doget = ADD_YOUR_RPC_PASSWORD_HERE
dasht = ADD_YOUR_RPC_PASSWORD_HERE
ltct = ADD_YOUR_RPC_PASSWORD_HERE

[hosts]
btct = 127.0.0.1
doget = 127.0.0.1
dasht = 127.0.0.1
ltct = 127.0.0.1

[app]
name = Blockstrap
email = founders@blockstrap.com
subject = Blockstrap Faucet Claim Code

[addresses]
btct = mxaFY8bG7DgH6AGe4dT3EAaTRd3NG96UHk
dasht = xyq7bwGUzPbSaCucM8r9LvhKZwKVfvacDU
doget = nhY8rzwwQBVj7UkPUDsdZGFMXDCyeCd9FK
ltct = mwJ9ebyT8kzaCECyUqwNU8cRj5aU8jL2op

[codes]
lifetime = 10
daily = 2
weekly = 4
monthly = 6
whitelist = admin@your-domain.com, admin@your-friends-domain.com
```

Which should result in something like this:

[![PREVIEW](/htdocs/img/preview.png)](http://faucets.blockstrap.com)