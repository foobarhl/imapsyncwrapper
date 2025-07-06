A utility for invoking imapsync mail transfers using a CSV file containing a
list of email addresses to migrate.

Credits and references:

 IMAPSync: https://imapsync.lamiral.info/ | https://github.com/imapsync/imapsync
 This script: Many thanks to Gemini for drafting the initial script and saving me a bunch of time.  You earned your monthly subscription.
 PHP:  Tested with PHP 8.x

Usage:

Generate a CSV (use xfer.csv.example as a base) and list email addresses one
per line:

```
source_host,source_user,source_pass,dest_host,dest_user,dest_pass,imapsync_options
mail.oldhost.com,_xferoptions@domain.com,,,,--OptionsThatApplyToAllInThisDomain
mail.oldhost.com,foo@domain.com,xyzzy,mail.newhost.com,foo@domain.com,newpassword,--exclude 'SPAM'
mail.oldhost.com,foo2@domain.com,xyzzy,mail.newhost.com,foo2@domain.com,newpassword,--exclude 'SPAM'
mail.oldhost.com,foo3@domain.com,xyzzy,mail.newhost.com,foo3@domain.com,newpassword,--exclude 'SPAM'
mail.oldhost2.com,oof@example.com,xyzzy,mail.newhost.com,oof@example.com,newpassword1233,--exclude 'SPAM' --OtherOptionsgoHere
```


Options may be specified on a per user basis (for custom tailoring transfers
on unique mailboxes) as well as on a per domain basis.  

To specify options on a per domain basis, add an entry for a user named
_xferoptions@domain.com.   ie:

```
mail.oldhost.com,_xferoptions@domain.com,,,,--OptionsThatApplyToAllInThisDomain
```
