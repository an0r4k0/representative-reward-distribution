#!/usr/bin/env sh

# Migrate database
mkdir -p rrd
touch rrd/database.sqlite
php application migrate --force

# Start the server simulating the cron job
while true; do
    php application schedule:run --no-interaction --quiet
    sleep 60
done
```

