## The queryData() Method

**The `queryData()` method and related methods listed below are currently in BETA testing.  Use them at your own risk as they can cause system instability due to long running queries depending on the use case.  While not likely, it is possible that the behavior of these methods will change in non-backward compatible ways in future REDCap versions.  Any and all feedback is very much appreciated.**

The `queryData()` method is an experimental alternative to `REDCap::getData()` that executes filter logic via SQL rather than PHP.  The current implementation often uses less memory than `REDCap::getData()`, and is sometimes faster on larger projects when only a few fields are referenced and a large amount of filter logic is used.  However, it is currently slower in other cases (sometimes dangerously), and only supports a subset of `REDCap::getData()` functionality.  Performance is heavily dependent on project size & the particular filter logic used.  Single calls with monolithic filter logic are discouraged in favor of combining the results of several smaller queries that "include" or "exclude" certain record IDs, and a final query to pull the necessary data for the list of relevant record IDs.

### Supported Functionality
- The `queryData()` method accepts `$sql` and `$parameters` arguments containing pseudo-SQL similar to `queryLogs()`.  Standard REDCap filter logic or it's equivalent without the brackets can be included AS the `WHERE` clause.  Here are some query examples:
  - `SELECT [record_id], [some_other_field_name] WHERE [some_other_field_name] = 'some value'`
  - `SELECT record_id WHERE field_one = '' or datediff(field_one, now(), 'd') < 7`
- Basic arithmetic, boolean logic, and `datediff()` calls are fully supported.
- Advanced filter logic is not yet supported (like smart variables).
- A MySQL Result object is returned whose output should be identical to `REDCap::getData()` except that `*_complete` form values are NOT returned unless explicitly requested.

### Related Methods
- `$module->getData()` - A wrapper method around `queryData()` with `REDCap::getData()` compatible arguments and return values for easy testing/transition of existing code.  Framework version 7 or greater is required.  An older undocumented `getData()` method existed prior to that, and is still in us by some old modules.  The `compareGetDataImplementations()` method below may be used to determine if it is safe to switch to this method.  If you have verified that `$module->getData()` behaves as expected in your use cases, comparable `queryData()` calls will likely also behave as you'd expect, and may provide additional functionality.  
- `$module->compareGetDataImplementations()` - A convenience method that accepts the same parameters as `REDCap::getData()`, automatically compares the results of `REDCap::getData()` and `$module->getData()`, then returns a summary object.  Results are reported as "identical" even if `*_complete` values are returned from `REDCap::getData()` but not `$module->getData()`.

### Ideas For Future Improvements
- Additional/alternate indexing of the `redcap_data` table (like the `value` column)
- Potentially using GROUP_CONCAT() instead of joins
- Splitting the logic up until smaller field/instance specific sections when possible and executing each as field/instance specific "include" or "exclude"...
  - ...inner selects that return only the record/instance.
  - ...top level queries that return only the record/instance and are joined via PHP.  This is similar to what the private *Advanced Reporting* module does currently at Vanderbilt, and is significantly faster than `REDCap::getData()` in the cases used by the *COVID Data Mart* project.
- Further optimize `REDCap::getData()` using some of the concepts learned here.  Even just modifying the functions returned by `LogicParser::parse()` to cache their results and prevent re-processing of duplicate data might go a long way.  It may also be possible to detect when filter logic is compatible enough to delegate some of the longer running portions of a `REDCap::getData()` call to `queryData()` automatically under the hood.