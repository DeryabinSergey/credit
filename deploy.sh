#!/bin/bash
sass --no-source-map --style=compressed /var/www/credit/src/user/templates/main.scss /tmp/main.css; postcss --no-map --use autoprefixer -o /var/www/credit/src/user/www/i/main.min.css /tmp/main.css
sass --no-source-map --style=compressed /var/www/credit/src/admin/templates/main.scss /tmp/main.css; postcss --no-map --use autoprefixer -o /var/www/credit/src/admin/www/i/main.min.css /tmp/main.css
sass --no-source-map --style=compressed /var/www/credit/src/credit/templates/main.scss /tmp/main.css; postcss --no-map --use autoprefixer -o /var/www/credit/src/credit/www/i/main.min.css /tmp/main.css
sass --no-source-map --style=compressed /var/www/credit/src/invest/templates/main.scss /tmp/main.css; postcss --no-map --use autoprefixer -o /var/www/credit/src/invest/www/i/main.min.css /tmp/main.css
cp /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js /var/www/credit/src/user/www/i/
cp /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js /var/www/credit/src/admin/www/i/
cp /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js /var/www/credit/src/credit/www/i/
cp /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js /var/www/credit/src/invest/www/i/
cp /var/www/fontawesome-free-5.12.0-web/webfonts/* /var/www/credit/src/user/www/i/
cp /var/www/fontawesome-free-5.12.0-web/webfonts/* /var/www/credit/src/admin/www/i/
cp /var/www/fontawesome-free-5.12.0-web/webfonts/* /var/www/credit/src/credit/www/i/
cp /var/www/fontawesome-free-5.12.0-web/webfonts/* /var/www/credit/src/invest/www/i/


cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.png /var/www/credit/src/user/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.png /var/www/credit/src/admin/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.png /var/www/credit/src/credit/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.png /var/www/credit/src/invest/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.svg /var/www/credit/src/user/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.svg /var/www/credit/src/admin/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.svg /var/www/credit/src/credit/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/default-skin.svg /var/www/credit/src/invest/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/preloader.gif /var/www/credit/src/user/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/preloader.gif /var/www/credit/src/admin/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/preloader.gif /var/www/credit/src/credit/www/i/
cp /var/www/PhotoSwipe/src/css/default-skin/preloader.gif /var/www/credit/src/invest/www/i/
