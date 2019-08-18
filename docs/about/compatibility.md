# Comparing to Suitup 1

We keep great compatibility with previous version talking about construct a project with Suitup of course, but there are
some differences as the list below. 

## Database connection
You can keep the usual file `config/database.config.php` but now its contents must to be like that:

```php
<?php
return array(
  'adapter' => 'mysql', // New property key
  'host' => '127.0.0.1',
  'port' => '3306',
  'dbname' => 'suitup', // Before was 'database'
  'username' => 'root',
  'password' => '',
);
```

  > Note the 'adapter' key that was included. So will be possible to create connection to
  another kind of database as postgres or DB2, but it is not implemented yet.
  Maybe you can help us with that... Why not? =)

### Transactions

To begin/commit/rollBack transactions use the methods below:

  - `Suitup\Database\DbAdapter::beginTransaction()`
  - `Suitup\Database\DbAdapter::commit()`
  - `Suitup\Database\DbAdapter::rollBack()`
  
## Layout Messages

To capture the messages from layout in the previous version there was a variable available called `$layoutMessages`, but
now its name is just `$messages`.

  

