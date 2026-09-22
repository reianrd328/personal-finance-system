#!/bin/bash
set -e

# Use PORT supplied by Render (default 80 if not set)
PORT="${PORT:-80}"

# Configure Apache to listen on $PORT
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Start Apache in foreground
exec apache2-foreground
