#!/bin/bash

sass --no-source-map --style=compressed src/user/templates/main.scss /tmp/main.css
postcss --no-map --use autoprefixer -o /tmp/main.css /tmp/main.css
cat /tmp/main.css vendor/components/jqueryui/themes/base/jquery-ui.min.css vendor/snapappointments/bootstrap-select/dist/css/bootstrap-select.min.css > src/user/www/i/main.min.css

sass --no-source-map --style=compressed src/admin/templates/main.scss /tmp/main.css
postcss --no-map --use autoprefixer -o /tmp/main.css /tmp/main.css
cat /tmp/main.css vendor/components/jqueryui/themes/base/jquery-ui.min.css vendor/snapappointments/bootstrap-select/dist/css/bootstrap-select.min.css > src/admin/www/i/main.min.css

sass --no-source-map --style=compressed /var/www/credit/src/credit/templates/main.scss /tmp/main.css; postcss --no-map --use autoprefixer -o /var/www/credit/src/credit/www/i/main.min.css /tmp/main.css
sass --no-source-map --style=compressed /var/www/credit/src/invest/templates/main.scss /tmp/main.css; postcss --no-map --use autoprefixer -o /var/www/credit/src/invest/www/i/main.min.css /tmp/main.css

cp vendor/components/jquery/jquery.min.js src/user/www/i/
cp vendor/components/jquery/jquery.min.js src/admin/www/i/
sed '$d' vendor/snapappointments/bootstrap-select/dist/js/bootstrap-select.min.js > src/user/www/i/bootstrap-select.min.js
sed '$d' vendor/snapappointments/bootstrap-select/dist/js/bootstrap-select.min.js > src/admin/www/i/bootstrap-select.min.js
cp vendor/snapappointments/bootstrap-select/dist/js/i18n/defaults-ru_RU.min.js src/user/www/i/
cp vendor/snapappointments/bootstrap-select/dist/js/i18n/defaults-ru_RU.min.js src/admin/www/i/

if [ -d src/admin/www/i/tiny ]; then
rm -rf src/admin/www/i/tiny
fi
mkdir src/admin/www/i/tiny
cp vendor/tinymce/tinymce/tinymce.min.js /var/www/credit/src/admin/www/i/tiny/
cp -r vendor/tinymce/tinymce/themes /var/www/credit/src/admin/www/i/tiny/
cp -r vendor/tinymce/tinymce/icons /var/www/credit/src/admin/www/i/tiny/
cp -r vendor/tinymce/tinymce/skins /var/www/credit/src/admin/www/i/tiny/
cp -r vendor/tinymce/tinymce/plugins /var/www/credit/src/admin/www/i/tiny/

sed '$d' /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js > /var/www/credit/src/user/www/i/bootstrap.bundle.min.js
sed '$d' /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js > /var/www/credit/src/admin/www/i/bootstrap.bundle.min.js
sed '$d' /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js > /var/www/credit/src/credit/www/i/bootstrap.bundle.min.js
sed '$d' /var/www/bootstrap-4.4.1/dist/js/bootstrap.bundle.min.js > /var/www/credit/src/invest/www/i/bootstrap.bundle.min.js

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

echo -e "<?php\n\ndefine('ASSETS_HASH', 'v="$(date +%s)"');\n\n?>" > config-asset.inc.php