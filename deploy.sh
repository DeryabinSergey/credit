#!/bin/bash
sass --no-source-map --style=compressed /var/www/credit/src/user/templates/main.scss /tmp/main.css; postcss --no-map --use autoprefixer -o /var/www/credit/src/user/www/i/main.min.css /tmp/main.css
cp /var/www/bootstrap-4.4.1/dist/js/bootstrap.min.js /var/www/credit/src/user/www/i/
