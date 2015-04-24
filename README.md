#bitbucket-hook-manager

This simple PHP script try to answer this question.

[bitbucketjenkins-trigger-build-only-when-specific-branch-is-changed](http://stackoverflow.com/questions/27388145/bitbucketjenkins-trigger-build-only-when-specific-branch-is-changed)

[Here](https://gist.github.com/icalvete/d60d5080e18f76d6154f) there is a bash template to play lauching jenkins job by API, The simplest one.

##Usage

Configure [POST hook](https://confluence.atlassian.com/display/BITBUCKET/POST+hook+management) with https://yourserver/getpost.php?token=<yourtoken>&job_name=<yourjobname>

##Limitations

**Only the first (last in json) commit have the branch. In later is null.**

**So, push commits to origin for each branch separately**

##Authors:

Israel Calvete Talavera <icalvete@gmail.com>
