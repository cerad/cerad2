Dumped the s1games database into s1games20131203.sql

mysql -uroot
create database s1games20131203;
grant all on s1games20131203.* to 'impd'@'localhost';
flush privileges;
exit
mysql -uimpd -pPASSWORD s1games20131203 < s1games20131203.sql

===
Tagged the cerad2 repository in order to freeze it.

Updated and pushed readme.rst

git tag -a v2013.12.03 -m 'After S1games Fall 2013'
git tag
git push origin v2013.12.03

To work with composer, need a tag that looks like a version number

# To delete a tag
git push origin :s1games20131203

===
Branched aysos1games

ahundiak@SPIKE /c/home/ahundiak/zayso2016/aysos1games (master)
$ git checkout -b s1games20131203
Switched to a new branch 's1games20131203'
ahundiak@SPIKE /c/home/ahundiak/zayso2016/aysos1games (s1games20131203)
$ git push origin s1games20131203

The reason I branched the s1games instead of tagging it is that I need to update composer.json
so it will install the cerad2 tag under vendor.  Might make a tag once everything is working.

===
Clone the repo
ahundiak@SPIKE /c/home/ahundiak/zayso2016
$ git clone https://github.com/cerad/aysos1games.git aysos1games20131203

$ cd aysos1games20131203
ahundiak@SPIKE /c/home/ahundiak/zayso2016/aysos1games20131203 (master)

$ git checkout s1games20131203
Switched to branch 's1games20131203'
ahundiak@SPIKE /c/home/ahundiak/zayso2016/aysos1games20131203 (s1games20131203)

$ git pull origin s1games20131203
From https://github.com/cerad/aysos1games
 * branch            s1games20131203 -> FETCH_HEAD
Already up-to-date.

ahundiak@SPIKE /c/home/ahundiak/zayso2016/aysos1games20131203 (s1games20131203)
$ git status
# On branch s1games20131203
nothing to commit, working directory clean

===
Run composer update without cerad2 being added

composer self-update
composer update

app/console just to verify everything is running

===
Opened then renamed the project in netbeans.
Changed the readme file and checked in just to check the workflow.

created zayso/web/s1games20131203 and copied files to it.

Ran app/clearcache_local

Browsed to: http://local.zayso.org/s1games20131203 and verified all was working

====
Added "cerad/cerad": "v2013.12.03" to composer.json

composer update then loaded the cerad2 branch

Commented out loader line in app/autoloaded.php

Verified all was working

=== 

Should I go ahead an tag the s1games20131203 branch as well?

git tag -a v2013.12.03 -m 'After S1games Fall 2013'
git push origin v2013.12.03

===
Blow away local aysos1games20131203 and start fresh

git clone https://github.com/cerad/aysos1games.git aysos1games20131203

cd aysos1games20131203

git checkout tags/v2013.12.03 # Ignore detached head stuff

composer update

should have excluded web/config.php but oh well, can revert it to remove chage icon
