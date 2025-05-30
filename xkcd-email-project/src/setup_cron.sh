#!/bin/bash

# This script sets up a CRON job to run cron.php every 24 hours.

# Define the path to the PHP executable and the cron.php file
PHP_PATH="/usr/bin/php"
CRON_JOB="0 * * * * $PHP_PATH /C:\Users\Asus\OneDrive\Desktop\rt\xkcd-Vrajc-main\xkcd-email-project\src"

# Add the CRON job
(crontab -l; echo "$CRON_JOB") | crontab -

echo "CRON job has been set up to run cron.php every 24 hours."