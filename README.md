# Paw Tribe Cli

A simple tribe reward system: it will sample all the tribe delegators balances and reward them fairly.

## Installation 

Install PHP and all needed extensions:
```bash
sudo apt-get install --no-install-recommends php8.1
sudo apt-get install -y php8.1 php8.1-cli php8.1-common php8.1-zip php8.1-mbstring php8.1-curl php8.1-xml php8.1-bcmath php8.1-sqlite3 php8.1-gmp
```

Download the last release of the binary (or compile it yourself) and copy it into the server.

Copy it to `/usr/local/bin` and give it execution permission:

```bash
sudo cp paw-tribe /usr/local/bin
sudo chmod +x /usr/local/bin
```

Execute the installer and give it all the needed info:
```bash
php8.1 /usr/local/bin/paw-tribe install
```

The wallet ID can be recovered from the `paw_node` executable using:
```bash
paw_node --wallet_list
```

Lastly add this to your crontab:
```
* * * * * cd /usr/local/bin && php paw-tribe schedule:run >> /dev/null 2>&1
```

All set! Delegators will be rewarded after about 48h.

## Advanced configuration

After the installation with the installer you will find a `.paw-tribe` directory containing the database and the config file. Customize it as you like!

Some "advanced" config like banning and whitelisting are also available. To access them simply execute the binary:
```bash
php8.1 /usr/local/bin/paw-tribe
```
