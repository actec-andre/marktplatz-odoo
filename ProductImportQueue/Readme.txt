#Installation

Magento2 Akeneo Product Import module installation is very easy, please follow the steps for installation-

Note: User need to upload this module if user want to use faster csv based export.

1. Unzip the respective extension zip and then move "app" folder (inside "src" folder) into magento root directory.

Run Following Command via terminal
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile;
php bin/magento setup:static-content:deploy;

2. Flush the cache and reindex all.

php bin/magento cache:clean;
php bin/magento indexer:reindex;

Note: if composer doen't work please do entry in composer.json file "firegento/fastsimpleimport": "^1.2"

now module is properly installed

#User Guide

For Akeneo Product Import connector module's working process follow user guide -

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/

----------------------------------------------------------------------------------------
Note - This readme file is strictly need to use when you have purchased the software from
webkul store i.e https://store.webkul.com . If you purchase the module from magento marketplace
connect this readme file will not work.
