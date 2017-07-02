# GitHook

Easy deployment to LEMP servers using GitHub or BitBucket web hooks. 
GitHook will update your website and clear cache automatically. It will also send email upon deployment!

# Features

The following is what GitHook does for you when you push files to your site through Git:

* Updates your Git based website using ```git pull```.
* Clears website cache for known website types. Supports clearing cache upon deployment for Magento 1 and Wordpress.
* Clears Varnish cache for the website if you told it to.
* Clears PHP opcache and realstat cache so that you can run your server with best TTL settings for performance.

# Installation

1. Add it as Git submodule to your project. 

```git submodule add git://github.com/chneukirchen/rack.git rack.```


2. Configure Nginx to pass appropriate variables to the GitHook.

```
server {
    # your usual yada-yada website configuration goes here
    
    location /GitHook/ {
        fastcgi_param GIT_WEBSITE_TYPE magento1;
        fastcgi_param GIT_USES_VARNISH 1;
        fastcgi_param GIT_HOOK_SECRET XXXXX;
        fastcgi_param GIT_HOOK_PROJECT_NAME MyProject;
        fastcgi_param GIT_HOOK_BRANCH staging;
        fastcgi_param GIT_HOOK_EMAILS dev@example.com;
        fastcgi_param GIT_HOOK_SLACK_TOKEN BLAHBLAH;
        fastcgi_param GIT_HOOK_SLACK_CHANNEL server;
        fastcgi_pass unix:/var/run/php-fpm/php-fpm-example.com.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```  

Configuration directives are as follows:

* GIT_WEBSITE_TYPE: can be one of magento1 or wordpress. Will use appropriate command line utility for clering application cache.
E.g. for Magento 1, n98-magerun is being used to flush cache

* GIT_USES_VARNISH: 0 or 1. Indicates whether we want GitHook to send BAN request for complete website.

* GIT_HOOK_SECRET: should be your secret token. It is something you make up to secure your communication between GitHub and the hook.

* GIT_HOOK_PROJECT_NAME: project name, that will be used in the deployment emails

* GIT_HOOK_BRANCH: branch name. This is typically the branch name that your want GitHook to work with. You might want to have different GIT_HOOK_BRANCH if you have two servers (staging and master) and two hooks for the same.

* GIT_HOOK_EMAILS: a comma separated list of email addresses where notification about deployment will be sent.

3. Configure the web hook using GitHub or Bitbucket UI:

  * Specify the same secret code as previous step
  * Put the proper URL to GitHook's ```fire.php```, 
  i.e.: ```https://www.example.com/GitHook/fire.php```
