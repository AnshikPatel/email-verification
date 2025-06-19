#!/bin/bash

# Path to your PHP executable
PHP_PATH=$(which php)

# Absolute path to cron.php
SCRIPT_PATH="$(cd "$(dirname "$0")"; pwd)/cron.php"

# CRON job line: runs once every day at 9 AM
CRON_JOB="0 9 * * * $PHP_PATH $SCRIPT_PATH"

# Check if the cron job already exists
(crontab -l 2>/dev/null | grep -v -F "$SCRIPT_PATH"; echo "$CRON_JOB") | crontab -

echo "✅ CRON job set to run cron.php every day at 9 AM"