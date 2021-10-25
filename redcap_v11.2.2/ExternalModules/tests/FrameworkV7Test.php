<?php
namespace ExternalModules;

use DateTime;

class FrameworkV7Test extends FrameworkV6Test
{
	private function assertGetData($recordIds, $fields, $filterLogic, $message = '', $repeatCount = 0){
		if(is_array($fields) && count($fields) === 1){
			// Test passing in single fields directly.
			$fields = $fields[0];
		}

		$result = $this->compareGetDataImplementations(TEST_SETTING_PID, 'json', $recordIds, $fields, null, null, false, false, false, $filterLogic);
		$expected = $result['php']['results'];
		$actual = $result['sql']['results'];

		$this->assertSame($expected, $actual, $message);
		$this->assertTrue($result['identical']);

		$expectedTime = $result['php']['execution-time'];
		$actualTime = $result['sql']['execution-time'];

        if(($actualTime-$expectedTime) > .1){            
			if($repeatCount < 3){
				// Try again.  Sometimes individual queries are much slower than they are on average (due to intermittent network or server load).
				$this->assertGetData($recordIds, $fields, $filterLogic, $message, $repeatCount+1);
			}
			else{
				throw new \Exception("The new implementation took significantly longer ($actualTime seconds) than the old one ($expectedTime seconds).");
			}
        }

		return count($expected);
	}

	function testGetData($methodName = 'this param is required for PHP 8, even though it is not used'){
		$recordId1 = (string) rand();
		$recordId2 = $recordId1+1;
		$unusedRecordId = $recordId2+1;

		$expected = [
			[
				TEST_RECORD_ID => $recordId1,
				TEST_TEXT_FIELD => (string) rand()
			],
			[
				TEST_RECORD_ID => $recordId1,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => '1',
				TEST_REPEATING_FIELD_1 => (string) rand(),
				TEST_REPEATING_FIELD_2 => (string) rand()
			],
			[
				TEST_RECORD_ID => $recordId1,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM_2,
				'redcap_repeat_instance' => '1',
				TEST_REPEATING_FORM_2_FIELD_1 => (string) rand(),
			],
			[
				TEST_RECORD_ID => $recordId1,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM_2,
				'redcap_repeat_instance' => '2',
				TEST_REPEATING_FORM_2_FIELD_1 => (string) rand(),
			],
			[
				TEST_RECORD_ID => (string) $recordId2,
				TEST_TEXT_FIELD => (string) rand()
			],
			[
				TEST_RECORD_ID => $recordId2,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => '1',
				TEST_REPEATING_FIELD_1 => (string) rand(),
				TEST_REPEATING_FIELD_2 => (string) rand()
			],
			[
				TEST_RECORD_ID => $recordId2,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => '2',
				TEST_REPEATING_FIELD_1 => (string) rand(),
				TEST_REPEATING_FIELD_2 => (string) rand()
			],
			[
				TEST_RECORD_ID => $recordId2,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM_2,
				'redcap_repeat_instance' => '1',
				TEST_REPEATING_FORM_2_FIELD_1 => (string) rand(),
			],
		];

		$this->saveData($expected);

		$fieldGroups = [
			[TEST_RECORD_ID, TEST_TEXT_FIELD], // non-repeating
			[TEST_RECORD_ID, TEST_REPEATING_FIELD_1, TEST_REPEATING_FIELD_2], // first repeating form
			[TEST_RECORD_ID, TEST_REPEATING_FORM_2_FIELD_1] // second repeating form
		];

		$assert = function($recordIds, $fields, $filterLogic, $message, $expectedException = null){
			$rowCount = 0;
			$exception = null;
			try{
				$rowCount = $this->assertGetData($recordIds, $fields, $filterLogic, $message);
			}
			catch(\Exception $e){
				$exception = $e;
			}

			if($expectedException !== null){
				$this->assertStringContainsString($expectedException, $e->getMessage());
			}
			else if($exception !== null){
				throw $exception;
			}

			return $rowCount;
		};

		$assertionCount = 0;
		$rowCount = 0;

		$recordIdParameters = [
			null,
			$recordId1,
			$recordId2,
			$unusedRecordId,
			[$recordId1, $recordId2],
			[$recordId1, $unusedRecordId],
			[$recordId2, $unusedRecordId],
		];

		foreach($recordIdParameters as $recordIds){
			foreach($fieldGroups as $fields){
				foreach($expected as $row){
					foreach($fields as $whereField){
						$filterLogicOptions = [
							null
						];

						$isWhereFieldRepeating = in_array($whereField, [TEST_REPEATING_FIELD_1, TEST_REPEATING_FIELD_2, TEST_REPEATING_FORM_2_FIELD_1]);

						$value = @$row[$whereField];
						if($value === null && $isWhereFieldRepeating){		
							// Skip this case because REDCap::getData() will return top level record results even though that is misleading.
							// $module->getData() does not return rows in that case.
						}
						else{
							$filterLogicOptions[] = "[$whereField] = '{$row[$whereField]}'";

							if($whereField === TEST_REPEATING_FORM_2_FIELD_1){
								$repeatingField = TEST_REPEATING_FORM_2_FIELD_1;
							}
							else{
								$repeatingField = TEST_REPEATING_FIELD_1;
							}

							// Make sure empty string not equals logic for both repeating & non-repeating fields behaves as expected.
							$emptyStringNotLogic = "([" . TEST_TEXT_FIELD . '] != "" or [' . $repeatingField . '] != "")';

							$filterLogicOptions[] = "[$whereField] = '{$row[$whereField]}' and $emptyStringNotLogic";

							// Exclude cases where REDCap::getData() returns extraneous if not incorrect top level rows.
							if(!$isWhereFieldRepeating){
								$filterLogicOptions[] = "[$whereField] != '{$row[$whereField]}'";
								$filterLogicOptions[] = "[$whereField] != '{$row[$whereField]}' and $emptyStringNotLogic";
							}
						}

						foreach($filterLogicOptions as $filterLogic){
							$expectedException = null;
							if($filterLogic !== null && $whereField === TEST_RECORD_ID){
								$expectedException = ExternalModules::tt('em_errors_150');
							}

							try{
								$assertionCount++;
								$rowCount += $assert($recordIds, $fields, $filterLogic, "Selecting multiple fields (" . implode(', ', $fields) . ") with logic: $filterLogic", $expectedException);
			
								foreach($fields as $selectField){
									$assertionCount++;
									$rowCount += $assert($recordIds, [$selectField], $filterLogic, "Selecting the '$selectField' field with logic: $filterLogic", $expectedException);
								}
							}
							catch(\Exception $e){
								var_dump("Failed after $rowCount assertions.");
								throw $e;
							}
						}
					}
				}
			}
		}

		$this->assertSame(4718, $assertionCount, 'Make sure the nested loops above actually perform the expected number of assertions');
		$this->assertSame(3016, $rowCount, 'Make sure the nested loops above actually process the expected number of rows');
	}

	function testGetData_or(){
		$recordId1 = (string) rand();
		$recordId2 = $recordId1+1;

		$value1 = (string) rand();
		$value2 = (string) $value1+1;

		$this->saveData([
			[
				TEST_RECORD_ID => $recordId1,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => '1',
				TEST_REPEATING_FIELD_1 => $value1
			],
			[
				TEST_RECORD_ID => $recordId2,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => '1',
				TEST_REPEATING_FIELD_1 => $value2
			],
		]);		

		// Ensure the or clause does not cause record 2 to be returned because only record 1 is requested.
		$filterLogic = "[" . TEST_REPEATING_FIELD_1 . "] = $value1 or [" . TEST_REPEATING_FIELD_1 . "] = $value2";
		$recordIds = [$recordId1];
		$rowCount = $this->assertGetData($recordIds, TEST_RECORD_ID, $filterLogic);
		$this->assertSame(count($recordIds), $rowCount);
	}

	function testGetData_instanceSkippedAndlaterInstanceMissingFields(){
		$recordId = rand();

		$this->saveData([
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 1,
				TEST_REPEATING_FIELD_1 => 1,
				TEST_REPEATING_FIELD_3 => 2,
			],
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 3,
				TEST_REPEATING_FIELD_1 => 1
			],
		]);

		$rowCount = $this->assertGetData($recordId, [TEST_RECORD_ID, TEST_REPEATING_FIELD_1, TEST_REPEATING_FIELD_2, TEST_REPEATING_FIELD_3], "1 = 1");
		$this->assertSame(3, $rowCount);

		$this->saveData([
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 3,
				TEST_REPEATING_FIELD_3 => 2,
			],
		]);

		$rowCount = $this->assertGetData($recordId, [TEST_RECORD_ID, TEST_REPEATING_FIELD_1, TEST_REPEATING_FIELD_2, TEST_REPEATING_FIELD_3], "1 = 1");
		$this->assertSame(3, $rowCount);
	}

	function testGetData_comparingRepeatingAndNonRepeatingFields(){
		// If our joins aren't just right, non-repeating values could default to the empty string or null on repeating rows,
		// changing the meaning of comparisons.  This test asserts one such case.

		$recordId = rand();

		$this->saveData([
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => 3
			],
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 1,
				TEST_REPEATING_FIELD_1 => 2
			],
		]);

		$fieldSets = [
			[TEST_RECORD_ID],
			[TEST_REPEATING_FIELD_1],
			[TEST_TEXT_FIELD],
			[TEST_TEXT_FIELD, TEST_REPEATING_FIELD_1],
		];

		foreach($fieldSets as $fieldSet){
			$this->assertGetData([], $fieldSet, '[' . TEST_TEXT_FIELD . '] > [' . TEST_REPEATING_FIELD_1 . ']');
		}
	}

	function testGetData_repeatingFieldQueriedButNotSet(){
		$this->saveData([
			[
				TEST_RECORD_ID => rand(),
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => rand()
			],
		]);

		$this->assertGetData([], [TEST_TEXT_FIELD, TEST_REPEATING_FIELD_1], "1 = 1");
	}

	function testGetData_emptyRepeatingAndNonRepeatingFields(){
		$recordId = rand();

		$this->saveData([
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => rand()
			],
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 1,
				TEST_REPEATING_FIELD_1 => rand()
			],
		]);

		$this->assertGetData([], [TEST_TEXT_FIELD, TEST_REPEATING_FIELD_1], "1 = 1");
	}

	function testGetData_notLogic(){
		// The $module->getData() method is a little different than REDCap::getData() in this case.
		// REDCap misleadingly returns some record level rows that logically should not match.		

		$data = [
			[
				TEST_RECORD_ID => '1',
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 1,
				TEST_REPEATING_FIELD_1 => '1'
			],
			[
				TEST_RECORD_ID => '2',
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 1,
				TEST_REPEATING_FIELD_1 => '2'
			]
		];

		$this->saveData($data);

		$expected = [
			$data[0]
		];
		
		$pid = TEST_SETTING_PID;
		$fields = [
			TEST_RECORD_ID,
			TEST_REPEATING_FIELD_1
		];

		$filterLogic = '[' . TEST_REPEATING_FIELD_1 . '] != "2"';

		$actual = json_decode($this->getData($pid, 'json', null, $fields, null, null, false, false, false, $filterLogic), true);
		
		$this->assertSame($expected, $actual);
	}

	function testGetData_v1(){
		parent::testGetData('getData_v1');
	}

	function testGetData_exceptions(){
		$_GET['pid'] = TEST_SETTING_PID;

		$this->assertThrowsException(function(){
			$this->getData(1, 'array');
		}, ExternalModules::tt('em_errors_147'));

		$this->assertThrowsException(function(){
			$this->getData(1, 'json', null, [TEST_TEXT_FIELD], 123);
		}, ExternalModules::tt('em_errors_149', 'null', 'events'));

		$this->assertThrowsException(function(){
			$this->assertGetData(null, [TEST_TEXT_FIELD], '[');
		}, 'Unable to find next token');

		$this->assertThrowsException(function(){
			$this->assertGetData(null, null, '[');
		}, ExternalModules::tt('em_errors_148'));

		$invalidFieldName = 'invalid_char!';
		$this->assertThrowsException(function() use ($invalidFieldName){
			$this->assertGetData(null, $invalidFieldName, null);
		}, ExternalModules::tt('em_errors_153', $invalidFieldName));
	}

	function testGetData_simpleNotLogicWithoutARepeatingInstance(){
		$this->saveData([
			[
				TEST_RECORD_ID => '1',
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => '1'
			]
		]);

		$this->assertGetData([], TEST_RECORD_ID, '[' . TEST_REPEATING_FIELD_1 . '] != "2"');
	}
	
	function testGetData_datediff(){
		$this->saveData([[
			TEST_RECORD_ID => rand(),
			'redcap_repeat_instrument' => '',
			'redcap_repeat_instance' => '',
			TEST_TEXT_FIELD => '2020-01-01'
		]]);

		$assert = function($filterLogic){
			$rowCount = $this->assertGetData([], TEST_RECORD_ID, $filterLogic);
			$this->assertSame(1, $rowCount, 'The filter logic did not match the saved data!');
		};

		// REDCap considers a year to be or 31556952 seconds (365.2425 days) which comes out to the following in 2020 (since it's a leap year).
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-12-31 05:49:12', 'y') = 1");

		// REDCap considers a month to be 2630016 seconds (30.44 days) which comes out the following.
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-31 10:33:36', 'M') = 1");
		
		// Other units
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-02', 'd') = 1");
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-01 01:00', 'h') = 1");
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-01 00:01', 'm') = 1");
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-01 00:00:01', 's') = 1");

		// Signed values
		$assert("datediff('2020-01-02', '2020-01-01', 'd', false) = 1");
		$assert("datediff('2020-01-02', '2020-01-01', 'd', true) = -1");

		// now
		$date = (new DateTime())->format('Y-m-d H:i:s');
		$assert("datediff('$date', 'now', 'm') < 1");

		// today
		$date = (new DateTime())->format('Y-m-d');
		$assert("datediff('$date 00:01', 'today', 'm') = 1");

		// Fractional results
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-02', 'y') = 1/365.2425");
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-02', 'M') = 1/30.44");
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-01 01:00', 'd') = 1/24");
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-01 00:01', 'h') = 1/60");
		$assert("datediff([".TEST_TEXT_FIELD."], '2020-01-01 00:00:01', 'm') = 1/60");

		/**
		 * We do not have to test comparisons to empty field values because
		 * they are already ambiguous and risky in REDCap::getData() calls.
		 * They should be clarified using "[field_name] = ''" checks in the logic anyway.
		 */
	}

	function testGetData_duplicateDataRows(){
		$recordId = rand();

		// Add the record
		$this->saveData([[
			TEST_RECORD_ID => $recordId
		]]);

		$insertSql = "insert into redcap_data values (?, ?, ?, ?, ?, ?)";
		$args = [TEST_SETTING_PID, $this->getEventId(TEST_SETTING_PID), $recordId, TEST_TEXT_FIELD, 'foo', null];

		// Insert two rows
		$this->query($insertSql, $args);
		$this->query($insertSql, $args);

		// Ensure that the DISTINCT keyword works and prevents duplicate rows
		$rowCount = $this->assertGetData([], TEST_TEXT_FIELD, null);
		$this->assertSame(1, $rowCount);
	}
}