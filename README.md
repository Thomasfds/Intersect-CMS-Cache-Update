# Intersect-CMS-Cache-Update
Cache files for better cache update

## Installation

1. Copy and paste all files in the src folder of your site,
2. Go to config folder and services.yaml
3. After line 11 add `cache_json: '%kernel.project_dir%/public/cache/'`
4. Clear cache,
5. Go to https://yourwebsite.com/update/taskCron
6. If no error in step 4 : Go to players list or rank and check if data is displayed without error. If error in step 4 : contact me
7. If no error in step 5 : Create cron job from your web host on the https://yourwebsite.com/update/taskCron url
8. Your website is ready with cache powerfull
