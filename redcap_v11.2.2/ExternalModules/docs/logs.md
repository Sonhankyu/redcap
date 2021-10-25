## Module Logs

Modules have built-in `log()`, `queryLogs()`, and `removeLogs()` methods that can be used for any common logging or historical data storage purposes.  If you have not already, please read the documentation for these three methods on the [method documentation](methods.md) page.  Example usage for each method can be found below:

### Storing Logs
```php
$logId = $module->log("Some simple message");
```

```php
$logId = $this->log(
	"Some message and associated parameters",
	[
		"some_value"=> 123,
		"some_other_value"=> "some string"
	]
);
```

### Querying Logs
The `queryLogs()` method works similarly to the `query()` method, and can be used as follows:
```php
$result = $module->queryLogs($pseudoSql, $parameters);
while($row = $result->fetch_assoc()){
	...
}
```

Here are some example arguments for this method:

```php
$pseudoSql = "select timestamp, user where message = ?"
$parameters = ['some message'];
```

```php
$pseudoSql = "
	select log_id, message, ip, some_parameter
	where
		timestamp > ?
		and project_id in (?, ?)
		and user in (?, ?)
		or some_parameter like ?
	order by timestamp desc
";

$parameters = [
	'2017-07-07',
	'123',
	'456',
	'joe',
	'tom',
	'%' . 'abc' . '%'
];
```

### Removing Logs

```php
$module->removeLogs('some_parameter = ?', 'some value');
```

```php
$module->removeLogs('
	timestamp < ?
	and some_parameter in (?,?)
', ['2021', 'some value', 'some other value']);
```