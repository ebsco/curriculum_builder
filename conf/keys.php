<?php
  // replace these values with your own username and password
  // this username and password will be used to login to
  // http://yourdomain.com/path/to/tool/manageaccess.php
  $username = 'USERNAME';
  $password = 'PASSWORD';
  
  // these keys are used to encrypt data stored on the user's
  // browser.  pick a random string of characters for each
  // of these
  define('ENCRYPTION_KEY_HMAC','REPLACEWITHRANDOMSTRING');
  define('SIG_HMAC','REPLACEWITHDIFFERENTRANDOM');

?>
