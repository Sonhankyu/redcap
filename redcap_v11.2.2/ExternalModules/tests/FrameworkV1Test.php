<?php
namespace ExternalModules;

use DateTime;
use Exception;
use REDCap;

class FrameworkV1Test extends BaseTest
{
	function __construct(){
		parent::__construct();

		preg_match('/[0-9]+/', get_class($this), $matches);
		$this->frameworkVersion = (int) $matches[0];
	}

	protected function getReflectionClass()
	{
		return $this->getFramework();
	}

	function getFrameworkVersion(){
		return $this->frameworkVersion;
	}

	function testImportDataDictionary(){
		// BaseTest::setUp() calls importDataDictionary() once when the first test runs
		// (so it will already have been called at this point).
		// So far this test only contains assertions for things that have changed since the initial implementation

		$actual = $this->query(
			'select element_enum from redcap_metadata where project_id = ? and field_name = ?',
			[TEST_SETTING_PID, TEST_FORM . '_complete']
		)->fetch_assoc()['element_enum'];
		
		$this->assertSame('0, Incomplete \n 1, Unverified \n 2, Complete', $actual);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	function testCheckSettings_emptyConfig()
	{
		self::assertConfigValid([]);
	}

    function testCheckSettings_duplicateKeys()
    {
    	$assertMultipleSettingException = function($config){
			self::assertConfigInvalid($config, 'setting multiple times!');
		};

		$assertMultipleSettingException([
			'system-settings' => [
				['key' => 'some-key']
			],
			'project-settings' => [
				['key' => 'some-key']
			],
		]);

		$assertMultipleSettingException([
			'system-settings' => [
				['key' => 'some-key']
			],
			'project-settings' => [
				['key' => 'some-key']
			],
		]);

		$assertMultipleSettingException([
			'system-settings' => [
				['key' => 'some-key']
			],
			'project-settings' => [
				[
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				]
			],
		]);

		$assertMultipleSettingException([
			'system-settings' => [
				[
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				]
			],
			'project-settings' => [
				['key' => 'some-key']
			],
		]);

		$assertMultipleSettingException([
			'system-settings' => [
				['key' => 'some-key'],
				['key' => 'some-key'],
			],
		]);

		$assertMultipleSettingException([
			'system-settings' => [
				['key' => 'some-key'],
				[
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				]
			],
		]);

		$assertMultipleSettingException([
			'system-settings' => [
				[
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				],
				['key' => 'some-key']
			],
		]);

		$assertMultipleSettingException([
			'system-settings' => [
				[
					'key' => 'some-key',
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				]
			],
		]);

		$assertMultipleSettingException([
			'project-settings' => [
				['key' => 'some-key'],
				['key' => 'some-key'],
			],
		]);

		$assertMultipleSettingException([
			'project-settings' => [
				['key' => 'some-key'],
				[
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				]
			],
		]);

		$assertMultipleSettingException([
			'project-settings' => [
				[
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				],
				['key' => 'some-key']
			],
		]);

		$assertMultipleSettingException([
			'project-settings' => [
				[
					'key' => 'some-key',
					'type' => 'sub_settings',
					'sub_settings' => [
						['key' => 'some-key']
					]
				]
			],
		]);

		// Assert a double nested sub_settings
		$assertMultipleSettingException([
			'project-settings' => [
				[
					'key' => 'some-key',
					'type' => 'sub_settings',
					'sub_settings' => [
						[
							'key' => 'some-other-key',
							'type' => 'sub_settings',
							'sub_settings' => [
								[
									'key' => 'some-other-key'
								]
							]
						]
					]
				]
			],
		]);
    }

	/**
	 * @doesNotPerformAssertions
	 */
	function testCheckSettingKey_valid()
	{
		self::assertConfigValid([
			'system-settings' => [
				['key' => 'key1']
			],
			'project-settings' => [
				['key' => 'key-two']
			],
		]);
	}

	function testCheckSettingKey_invalidChars()
	{
		$this->assertConfigInvalid([
			'system-settings' => [
				['key' => 'A']
			]
		], ExternalModules::tt("em_errors_62", TEST_MODULE_PREFIX, 'A'));

		$this->assertConfigInvalid([
			'project-settings' => [
				['key' => '!']
			]
		], ExternalModules::tt("em_errors_62", TEST_MODULE_PREFIX, '!'));
	}

	function testIsSettingKeyValid()
	{
		$isSettingKeyValid = function($key){
			return $this->callPrivateMethodForClass($this->getFramework(), 'isSettingKeyValid', $key);
		};

		$this->assertTrue($isSettingKeyValid('a'));
		$this->assertTrue($isSettingKeyValid('2'));
		$this->assertTrue($isSettingKeyValid('-'));
		$this->assertTrue($isSettingKeyValid('_'));

		$this->assertFalse($isSettingKeyValid('A'));
		$this->assertFalse($isSettingKeyValid('!'));
		$this->assertFalse($isSettingKeyValid('"'));
		$this->assertFalse($isSettingKeyValid('\''));
		$this->assertFalse($isSettingKeyValid(' '));
	}

	private function assertConfigValid($config)
	{
		$this->setConfig($config);

		// Attempt to make a new instance of the module (which throws an exception on any config issues).
		new TestModule(TEST_MODULE_PREFIX);
	}

	private function assertConfigInvalid($config, $exceptionExcerpt)
	{
		$this->assertThrowsException(function() use ($config){
			self::assertConfigValid($config);
		}, $exceptionExcerpt);
	}

	function testQuery_noParameters(){
		$value = (string)rand();
		$result = $this->query("select $value", []);
		$row = $result->fetch_row();
		$this->assertSame($value, $row[0]);

		$frameworkVersion = $this->getFrameworkVersion();
		if($frameworkVersion < 4){
			$value = (string)rand();
			$result = $this->query("select $value");
			$row = $result->fetch_row();
			$this->assertSame($value, $row[0]);	
		}
		else{
			$this->assertThrowsException((function(){
				$this->query("select 1");
			}), ExternalModules::tt('em_errors_117'));
		}
	}

	function testQuery_trueReturnForDatalessQueries(){
		$r = $this->query('update redcap_ip_banned set time_of_ban=now() where ?=?', [1,2]);
        $this->assertTrue($r);
	}

	function testQuery_invalidQuery(){
		$this->assertThrowsException(function(){
			ob_start();
			$this->query("select * from some_table_that_does_not_exist", []);
		}, ExternalModules::tt("em_errors_29"));

		ob_end_clean();
	}

	function testQuery_paramTypes(){
		$dateTimeString = '2001-02-03 04:05:06';

		$values = [
			true,
			2,
			3.3,
			'four',
			null,
			new DateTime($dateTimeString)
		];

		$row = $this->query('select ?, ?, ?, ?, ?, ?', $values)->fetch_row();

		$values[0] = 1; // The boolean 'true' will get converted to the integer '1'.  This is excepted.
		$values[5] = $dateTimeString;

		$this->assertSame($values, $row);
	}

	function testQuery_invalidParamType(){
		$this->assertThrowsException(function(){
			ob_start();
			$invalidParam = new \stdClass();
			$this->query("select ?", [$invalidParam]);
		}, ExternalModules::tt('em_errors_109'));

		ob_end_clean();
	}
	
	function testQuery_singleParams(){
		$values = [
			rand(),
			
			// Check falsy values
			0,
			'0',
			''
		];

		foreach($values as $value){
			$row = $this->query('select ?', $value)->fetch_row();
			$this->assertSame($value, $row[0]);
		}
	}

	function testGetSubSettings_complexNesting()
	{
		$m = $this->getInstance();
		$_GET['pid'] = TEST_SETTING_PID;

		// This json file can be copied into a module for hands on testing/modification via the settings dialog.
		$this->setConfig(json_decode(file_get_contents(__DIR__ . '/complex-nested-settings.json'), true));

		// These values were copied directly from the database after saving them through the settings dialog (as configured by the json file above).
		$m->setProjectSetting('countries', ["true","true"]);
		$m->setProjectSetting('country-name', ["USA","Canada"]);
		$m->setProjectSetting('states', [["true","true"],["true"]]);
		$m->setProjectSetting('state-name', [["Tennessee","Alabama"],["Ontario"]]);
		$m->setProjectSetting('cities', [[["true","true"],["true"]],[["true"]]]);
		$m->setProjectSetting('city-name', [[["Nashville","Franklin"],["Huntsville"]],[["Toronto"]]]);
		$m->setProjectSetting('city-size', [[["large","small"],["medium"]],[[null]]]); // The null is an important scenario to test here, as it can change output behavior.

		$assert = function($actualCountries, $newImplementation){
			if($newImplementation){
				$states = [
					[
						"state-name" => "Tennessee",
						"cities" => [
							[
								"city-name" => "Nashville",
								"city-size" => "large"
							],
							[
								"city-name" => "Franklin",
								"city-size" => "small"
							]
						]
					],
					[
						"state-name" => "Alabama",
						"cities" => [
							[
								"city-name" => "Huntsville",
								"city-size" => "medium"
							]
						]
					]
				];
	
				$provinces = [
					[
						"state-name" => "Ontario",
						"cities" => [
							[
								"city-name" => "Toronto",
								"city-size" => null
							]
						]
					]
				];
			}
			else{
				$states = ['true', 'true'];
				$provinces = ['true'];
			}
			
			$expectedCountries = [
				[
					"states" => $states,
					"country-name" => "USA"
				],
				[
					"states" => $provinces,
					"country-name" => "Canada"
				]
			];
			$this->assertEquals($expectedCountries, $actualCountries);
		};

		$assert($this->getFramework()->getSubSettings('countries'), true);
		$assert($this->getFramework()->getSubSettings_v1('countries'), false);
		$assert($m->getSubSettings('countries'), $this->getFrameworkVersion() >= 5);
	}

	function testGetSubSettings_plainOldRepeatableInsideSubSettings(){
		$m = $this->getInstance();
		$_GET['pid'] = TEST_SETTING_PID;

		$this->setConfig('
			{
				"project-settings": [
					{
						"key": "one",
						"name": "one",
						"type": "sub_settings",
						"repeatable": true,
						"sub_settings": [
							{
								"key": "two",
								"name": "two",
								"type": "text",
								"repeatable": true
							}
						]
					}
				]
			}
		');

		$m->setProjectSetting('one', ["true"]);
		$m->setProjectSetting('two', [["value"]]);

		$this->assertEquals(
			[
				[
					'two' => [
						'value'
					]
				]
			],
			$m->getSubSettings('one')
		);
	}

	function testGetProjectsWithModuleEnabled(){
		$assert = function($enableValue, $expectedPids){
			$m = $this->getInstance();
			$m->setProjectSetting(ExternalModules::KEY_ENABLED, $enableValue, TEST_SETTING_PID);
			$pids = $this->getProjectsWithModuleEnabled();
			$this->assertSame($expectedPids, $pids);
		};

		$assert(true, [TEST_SETTING_PID]);
		$assert(false, []);
	}

	function testProject_getLogTableName(){
		$result = $this->query('select log_event_table from redcap_projects where project_id = ?', TEST_SETTING_PID);
		$expected = $result->fetch_assoc()['log_event_table'];
		$actual = $this->getProject(TEST_SETTING_PID)->getLogTable();
		$this->assertSame($expected, $actual);
	}

	function testProject_getUsers(){
		$assert = function($actualUsers){
			$this->assertNotEmpty($actualUsers);

			$result = $this->getFramework()->query("
				select user_email
				from redcap_user_rights r
				join redcap_user_information i
					on r.username = i.username
				where project_id = ?
				order by r.username
			", TEST_SETTING_PID);

			$i = 0;
			while($row = $result->fetch_assoc()){
				$this->assertSame($row['user_email'], $actualUsers[$i]->getEmail());
				$i++;
			}
		};

		$username = $this->getRandomUsername();
		$project = $this->getProject(TEST_SETTING_PID);

		$project->removeUser($username);
		$project->addUser($username);

		$actualUsers = $project->getUsers();

		$assert($actualUsers);

		$_GET['pid'] = TEST_SETTING_PID;
		if($this->getFrameworkVersion() >= 7){
			// Make sure callable from framework object directly.
			// This assertion currently covers all method forwards from the framework to project object (not just getUsers()).
			$assert($this->getFramework()->getUsers());
		}
		else{
			$this->assertThrowsException(function(){
				$this->getFramework()->getUsers();
			}, 'Call to undefined method: getUsers');
		}

		/**
		 * This probably isn't the most appropriate location for this assertion,
		 * but I like having it close to the forward related assertions above.
		 */
		$this->assertThrowsException(function(){
			$this->getFramework()->someMethodThatDoesNotExist();
		}, 'Call to undefined method: someMethodThatDoesNotExist');

		$project->removeUser($username);
	}

	function testProject_getProjectId(){
		$this->assertSame((int)TEST_SETTING_PID, $this->getProject(TEST_SETTING_PID)->getProjectId());
	}

	private function assertAddOrUpdateInstances($instanceData, $expected, $keyFields, $message = null){
		$_GET['pid'] = TEST_SETTING_PID;
		
		// Run the assertion twice, to make sure subsequent calls with the same data have no effect.
		for($i=0; $i<2; $i++){
			$addOrUpdateResult = $this->addOrUpdateInstances($instanceData, $keyFields);
			$this->assertTrue(isset($addOrUpdateResult['item_count']), 'Make sure the underlying saveData() result is returned');

			$fields = [$this->getRecordIdField(), TEST_REPEATING_FIELD_1, TEST_REPEATING_FIELD_2, TEST_REPEATING_FIELD_3];
			$results = json_decode(\REDCap::getData($this->getFramework()->getProjectId(), 'json', null, $fields), true);

			$actual = [];
			foreach($results as $result){
				if($result['redcap_repeat_instance'] === ''){
					continue;
				}

				$actual[] = $result;
			}
			
			$this->assertSame($expected, $actual, $message);
		}
	}

	function testProject_addOrUpdateInstances(){
		$nextRecordId = rand();
		$uniqueFieldValue = rand();
		$expected = [];
		
		$createInstanceData = function($recordId, $instanceNumber) use(&$uniqueFieldValue, &$expected){
			$instanceExpected = [
				$this->getRecordIdField(TEST_SETTING_PID) => (string) $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => $instanceNumber,
				TEST_REPEATING_FIELD_1 => (string) ($uniqueFieldValue++),
				TEST_REPEATING_FIELD_2 => (string) rand(),
				TEST_REPEATING_FIELD_3 => ''
			];

			$expected[] = $instanceExpected;
			$instanceData = $instanceExpected;
			
			// Unset these so that the test verifies that they gets added appropriately.
			unset($instanceData['redcap_repeat_instrument']);
			unset($instanceData['redcap_repeat_instance']);

			return $instanceData;
		};

		$assert = function($instanceData, $message) use (&$expected){	
			$this->assertAddOrUpdateInstances($instanceData, $expected, TEST_REPEATING_FIELD_1, $message);
		};

		$recordId1 = $nextRecordId++;
		$instanceData1 = $createInstanceData($recordId1, 1);
		$assert([$instanceData1], 'Add one instance');

		$this->assertThrowsException(function() use ($assert, $instanceData1){
			$assert([$instanceData1, $instanceData1], 'An exception should be thrown before this assertion message is ever reached');
		}, ExternalModules::tt('em_errors_138'));

		$instanceData2 = $createInstanceData($recordId1, 2);
		$instanceData3 = $createInstanceData($recordId1, 3);
		$instanceData3['redcap_repeat_instrument'] = TEST_REPEATING_FORM; // Also ensure that manually specifying the form makes no difference
		$assert([$instanceData2, $instanceData3], 'Add two more instances for the same record');
		
		$updatedValue1 = (string) rand();
		$instanceData1[TEST_REPEATING_FIELD_2] = $updatedValue1;
		$expected[count($expected)-3][TEST_REPEATING_FIELD_2] = $updatedValue1;
		$updatedValue2 = (string) rand();
		$instanceData2[TEST_REPEATING_FIELD_2] = $updatedValue2;
		$expected[count($expected)-2][TEST_REPEATING_FIELD_2] = $updatedValue2;
		$assert([$instanceData1, $instanceData2], 'Updating a couple of instances');

		$instanceData4 = $createInstanceData($recordId1, 4);
		$recordId2 = $nextRecordId++;
		$record2InstanceData1 = $createInstanceData($recordId2, 1);
		$assert([$instanceData4, $record2InstanceData1], 'Adding instances for multiple records');

		$record2UpdatedValue = (string) rand();
		$record2InstanceData1[TEST_REPEATING_FIELD_2] = $record2UpdatedValue;
		$expected[count($expected)-1][TEST_REPEATING_FIELD_2] = $record2UpdatedValue;
		$assert([$record2InstanceData1], 'Updating an instance for another record');

		$duplicateInstance = $expected[count($expected)-1];
		$duplicateInstance['redcap_repeat_instance']++;
		REDCap::saveData($this->getFramework()->getProjectId(), 'json', json_encode([$duplicateInstance]));
		$this->assertThrowsException(function() use($assert, $duplicateInstance){
			$assert([$duplicateInstance], 'An exception should be thrown before this assertion message is ever reached');
		}, ExternalModules::tt('em_errors_135', TEST_REPEATING_FORM));
	}

	function testProject_addOrUpdateInstances_multipleKeys(){
		$firstInstance = [
			TEST_RECORD_ID => (string) rand(),
			'redcap_repeat_instrument' => TEST_REPEATING_FORM,
			'redcap_repeat_instance' => 1,
			TEST_REPEATING_FIELD_1 => (string) rand(),
			TEST_REPEATING_FIELD_2 => (string) rand(),
			TEST_REPEATING_FIELD_3 => (string) rand(),
		];

		$expectedResult = [
			$firstInstance
		];

		$assert = function($instances, $message) use (&$expectedResult){
			$this->assertAddOrUpdateInstances($instances, $expectedResult, [
				TEST_REPEATING_FIELD_1,
				TEST_REPEATING_FIELD_2
			], $message);
		};

		$assert($expectedResult, 'initial save');

		$firstInstance[TEST_REPEATING_FIELD_3] = (string) rand();
		$expectedResult[0] = $firstInstance;
		$assert([$firstInstance], 'update non-key value on existing instance');

		$secondInstance = $firstInstance;
		$secondInstance['redcap_repeat_instance'] = 2;
		$secondInstance[TEST_REPEATING_FIELD_1] = (string) rand();
		$secondInstance[TEST_REPEATING_FIELD_3] = (string) rand();
		$expectedResult[] = $secondInstance;
		$assert([$secondInstance], 'updating the first of two keys causes a new instance');

		$thirdInstance = $secondInstance;
		$thirdInstance['redcap_repeat_instance'] = 3;
		$thirdInstance[TEST_REPEATING_FIELD_2] = (string) rand();
		$thirdInstance[TEST_REPEATING_FIELD_3] = (string) rand();
		$expectedResult[] = $thirdInstance;
		$assert([$thirdInstance], 'updating the second of two keys causes a new instance');

		$record2Instance1 = $firstInstance;
		$record2Instance1[TEST_RECORD_ID] = (string) ($record2Instance1[TEST_RECORD_ID] + 1); // Add one so it appears next in the result list.
		$record2Instance1[TEST_REPEATING_FIELD_3] = (string) rand();
		$expectedResult[] = $record2Instance1;
		$assert([$firstInstance, $record2Instance1], 'using the same key fields on a different records results in separate instances for each record');
	}

	function testProject_addOrUpdateInstances_falsyValues(){
		$recordId = (string) rand();

		$expected = [
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 1,
				TEST_REPEATING_FIELD_1 => '0',
				TEST_REPEATING_FIELD_2 => (string) rand(),
				TEST_REPEATING_FIELD_3 => (string) rand(),
			],
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				'redcap_repeat_instance' => 2,
				TEST_REPEATING_FIELD_1 => '',
				TEST_REPEATING_FIELD_2 => (string) rand(),
				TEST_REPEATING_FIELD_3 => (string) rand(),
			]
		];

		$this->assertAddOrUpdateInstances($expected, $expected, [TEST_REPEATING_FIELD_1], "Make sure zero and empty string are considered separate values");
	}

	function testProject_addOrUpdateInstances_numericTypeComparison(){
		$instance = [
			TEST_RECORD_ID => rand(),
			'redcap_repeat_instrument' => TEST_REPEATING_FORM,
			'redcap_repeat_instance' => 1,
			TEST_REPEATING_FIELD_1 => 0,
			TEST_REPEATING_FIELD_2 => '',
			TEST_REPEATING_FIELD_3 => '',
		];

		$expected = $instance;
		$expected[TEST_RECORD_ID] = (string) $expected[TEST_RECORD_ID];
		$expected[TEST_REPEATING_FIELD_1] = (string) $expected[TEST_REPEATING_FIELD_1];
		
		unset($instance['redcap_repeat_instance']);

		$this->assertAddOrUpdateInstances(
			[$instance],
			[$expected], 
			[TEST_REPEATING_FIELD_1], 
			'Ensure that passing in integers instead of strings does not result in duplicate instances (relies on the duplicate call loop in assertAddOrUpdateInstances())'
		);

		$this->assertThrowsException(function(){
			$this->addOrUpdateInstances(
				[
					[
						TEST_RECORD_ID => TEST_RECORD_ID,
						'redcap_repeat_instrument' => TEST_REPEATING_FORM,
						TEST_REPEATING_FIELD_1 => '0',
						TEST_REPEATING_FIELD_2 => '',
						TEST_REPEATING_FIELD_3 => '',
					],
					[
						TEST_RECORD_ID => TEST_RECORD_ID,
						'redcap_repeat_instrument' => TEST_REPEATING_FORM,
						TEST_REPEATING_FIELD_1 => 0,
						TEST_REPEATING_FIELD_2 => '',
						TEST_REPEATING_FIELD_3 => '',
					],
				],
				[TEST_REPEATING_FIELD_1]
			);
		}, ExternalModules::tt('em_errors_138'), 'Make sure duplicate keys that vary in type are caught when passed in at the same time');
	}

	function testProject_addOrUpdateInstances_exceptions(){
		$_GET['pid'] = TEST_SETTING_PID;
		$recordIdFieldName = $this->getRecordIdField();

		$assertException = function($instances, $message){
			$this->assertThrowsException(function() use ($instances){
				$this->addOrUpdateInstances($instances, TEST_REPEATING_FIELD_1);
			}, $message);
		};

		$assertException([
			[
				TEST_REPEATING_FIELD_1 => 1
			],
		], ExternalModules::tt('em_errors_134', TEST_RECORD_ID));

		$assertException([
			[
				$recordIdFieldName => 1
			],
		], ExternalModules::tt('em_errors_134', TEST_REPEATING_FIELD_1));

		$assertException([
			[
				'redcap_repeat_instrument' => 'one',
			]
		], ExternalModules::tt('em_errors_137', TEST_REPEATING_FORM, 'one'));

		$fakeFieldName = 'some_nonexistent_field';
		$results = $this->addOrUpdateInstances([
			[
				$recordIdFieldName => 'one',
				'redcap_repeat_instrument' => TEST_REPEATING_FORM,
				TEST_REPEATING_FIELD_1 => 1,
				$fakeFieldName => 1
			],
		], TEST_REPEATING_FIELD_1);
		$this->assertStringContainsString("not found in the project as real data fields: $fakeFieldName", $results['errors']);

		$assertException([1,2,3], ExternalModules::tt('em_errors_136'));

		$this->assertThrowsException(function(){
			$this->addOrUpdateInstances([[]], []);
		}, ExternalModules::tt('em_errors_132'));

		$this->assertThrowsException(function(){
			$this->addOrUpdateInstances([[]], [TEST_REPEATING_FIELD_1, TEST_TEXT_FIELD]);
		}, ExternalModules::tt('em_errors_133'));

		$this->assertThrowsException(function() use ($fakeFieldName){
			$this->addOrUpdateInstances([[]], [$fakeFieldName]);
		}, ExternalModules::tt('em_errors_139', $fakeFieldName));

		$setValidation = function($project, $field, $validation){
			$this->query("
				update redcap_metadata
				set element_validation_type = ?
				where project_id = ?
				and field_name = ?
			", [$validation, $project, $field]);
		};

		$setValidation(TEST_SETTING_PID, TEST_REPEATING_FIELD_1, 'float');
		$result = $this->addOrUpdateInstances([[
			$recordIdFieldName => 'one',
			'redcap_repeat_instrument' => TEST_REPEATING_FORM,
			TEST_REPEATING_FIELD_1 => 'some non-numeric value',
		]], [TEST_REPEATING_FIELD_1]);
		
		$this->assertSame(80, strpos($result['errors'][0], 'could not be validated'));
		$setValidation(TEST_SETTING_PID, TEST_REPEATING_FIELD_1, null);
	}

	function testProject_addUser(){
		$username = $this->getRandomUsername();
		$project = $this->getProject(TEST_SETTING_PID);

		$project->removeUser($username);
		$project->addUser($username);
		$this->assertSame('0', $project->getRights($username)['design']);

		$project->removeUser($username);		
		$project->addUser($username, ['design' => 1]);
		$this->assertSame('1', $project->getRights($username)['design']);

		$project->removeUser($username);
	}

	function testProject_removeUser(){
		$username = $this->getRandomUsername();
		$project = $this->getProject(TEST_SETTING_PID);

		$project->addUser($username);
		$project->removeUser($username);
		$this->assertNull($project->getRights($username));
	}

	function testProject_getRights(){
		$username = $this->getRandomUsername();
		$project = $this->getProject(TEST_SETTING_PID);

		$project->removeUser($username);

		$value = (string) rand(0, 1);
		$project->addUser($username, ['design' => $value]);

		$this->assertSame($value, $project->getRights($username)['design']);

		$project->removeUser($username);
	}

	function testProject_setRights(){
		$username = $this->getRandomUsername();
		$project = $this->getProject(TEST_SETTING_PID);

		$project->removeUser($username);
		$project->addUser($username);
		$this->assertSame('0', $project->getRights($username)['design']);

		$project->setRights($username, ['design' => 1]);
		$this->assertSame('1', $project->getRights($username)['design']);

		$project->removeUser($username);		
	}

	function testField_getType(){
		$project = $this->getProject(TEST_SETTING_PID);

		$this->assertSame('text', $project->getField(TEST_TEXT_FIELD)->getType());

		$fieldName = 'some_field_that_does_not_exist';
		$this->assertThrowsException(function() use ($project, $fieldName){
			$project->getField($fieldName)->getType();
		}, ExternalModules::tt('em_errors_144', $fieldName, TEST_SETTING_PID));
	}

	function testProject_getRepeatingForms(){
		$expected = [TEST_REPEATING_FORM, TEST_REPEATING_FORM_2];

		$assert = function($actual) use ($expected){
			$this->assertSame($expected, $actual);
		};
		
		$secondEventId = $this->getEventIds(TEST_SETTING_PID_2)[1];
		$assert($this->getProject(TEST_SETTING_PID_2)->getRepeatingForms($secondEventId));
		
		$assert($this->getProject(TEST_SETTING_PID)->getRepeatingForms());
		
		$_GET['pid'] = TEST_SETTING_PID;
		$assert($this->getFramework()->getRepeatingForms());
	}

	function testGetFieldNames(){
		$_GET['pid'] = TEST_SETTING_PID;
		$this->assertSame([TEST_REPEATING_FORM_2_FIELD_1], $this->getFieldNames(TEST_REPEATING_FORM_2));
	}

	function testForm_getFieldNames(){
		$actual = $this->getProject(TEST_SETTING_PID)->getForm(TEST_REPEATING_FORM_2)->getFieldNames();
		$this->assertSame([TEST_REPEATING_FORM_2_FIELD_1], $actual);
	}

	function testRecords_lock(){
		$_GET['pid'] = TEST_SETTING_PID;
		$recordIds = [1, 2];
		$records = $this->getFramework()->records;
		
		foreach($recordIds as $recordId){
			$this->ensureRecordExists($recordId);
		}

		$records->lock($recordIds);
		foreach($recordIds as $recordId){
			$this->assertTrue($records->isLocked($recordId));
		}

		$records->unlock($recordIds);
		foreach($recordIds as $recordId){
			$this->assertFalse($records->isLocked($recordId));
		}
	}

	function testUser_isSuperUser(){
		$result = ExternalModules::query('select username from redcap_user_information where super_user = 1 limit 1', []);
		$row = $result->fetch_assoc();
		$username = $row['username'];
		
		$user = $this->getUser($username);
		$this->assertTrue($user->isSuperUser());
	}

	function testUser_getRights(){
		$result = ExternalModules::query("
			select * from redcap_user_rights
			where username != ''
			order by rand() limit 1
		", []);

		$row = $result->fetch_assoc();
		$projectId = $row['project_id'];
		$username = $row['username'];
		$expectedRights = \UserRights::getPrivileges($projectId, $username)[$projectId][$username];

		$user = $this->getUser($username);
		
		$actualRights = $user->getRights($projectId, $username);
		$this->assertSame($expectedRights, $actualRights);

		$_GET['pid'] = $projectId;
		$actualRights = $user->getRights(null, $username);
		$this->assertSame($expectedRights, $actualRights);
	}
	
	function testGetEventId(){
		$this->assertThrowsException(function(){
			$this->getEventId();
		}, ExternalModules::tt('em_errors_65', 'pid'));

		$expected = $this->getEventIds(TEST_SETTING_PID)[0];

		$this->assertSame($expected, $this->getEventId(TEST_SETTING_PID));

		$_GET['pid'] = (string) TEST_SETTING_PID;
		$this->assertSame($expected, $this->getEventId());

		$urlEventId = 99999999;
		$_GET['event_id'] = $urlEventId;
		$this->assertEquals($urlEventId,  $this->getEventId());
	}

    function testGetSafePath(){
        $test = function($path, $root=null){
            // Get the actual value before manipulating the root for testing.
            $actual = call_user_func_array([$this, 'getSafePath'], func_get_args());

			$moduleDirectory = ExternalModules::getModuleDirectoryPath(TEST_MODULE_PREFIX);
            if(!$root){
                $root = $moduleDirectory;
            }
            else if(!file_exists($root)){
                $root = "$moduleDirectory/$root";
            }

            $root = realpath($root);
            $expected = $root . DS . $path;
            if(file_exists($expected)){
                $expected = realpath($expected);
            }

            $this->assertEquals($expected, $actual);
        };

        $test(basename(__FILE__));
        $test('.');
        $test('non-existant-file.php');
        $test('test-subdirectory');
        $test('test-file.php', 'test-subdirectory'); // relative path
        $test('test-file.php', ExternalModules::getTestModuleDirectoryPath() . '/test-subdirectory'); // absolute path

        $expectedExceptions = [
            'outside of your allowed parent directory' => [
                '../index.php',
                '..',
                '../non-existant-file',
                '../../../passwd'
            ],
            'only works on directories that exist' => [
                'non-existant-directory/non-existant-file.php',
            ],
            'does not exist as either an absolute path or a relative path' => [
                ['foo', 'non-existent-root']
            ]
        ];

        foreach($expectedExceptions as $excerpt=>$calls){
            foreach($calls as $args){
                if(!is_array($args)){
                    $args = [$args];
                }    

                $this->assertThrowsException(function() use ($test, $args){
                    call_user_func_array($test, $args);
                }, $excerpt);
            }
        }
    }

    function testConvertIntsToStrings(){
        $assert = function($expected, $data){
            $actual = $this->convertIntsToStrings($data);
            $this->assertSame($expected, $actual);
        };

        $assert(['1', 'b', null], [1, 'b', null]);
        $assert(['a' => '1', 'b'=>'b', 'c' => null], ['a' => 1, 'b'=>'b', 'c' => null]);
    }

    function testIsPage(){
        $originalRequestURI = $_SERVER['REQUEST_URI'];
        
        $path = 'foo/goo.php';

        $this->assertFalse($this->isPage($path));
        
        $_SERVER['REQUEST_URI'] = APP_PATH_WEBROOT . $path;
        $this->assertTrue($this->isPage($path));

        $_SERVER['REQUEST_URI'] = $originalRequestURI;
    }
	
	function testGetLinkIconHtml(){
		$iconName = 'fas fa-whatever';
		$link = ['icon' => $iconName];
		$html = $this->getLinkIconHtml($link);

		if($this->getFrameworkVersion() < 3){
			$expected = "<img src='" . APP_PATH_IMAGES . "$iconName.png'";
		}
		else{
			$expected = "<i class='$iconName'";
		}

		$this->assertTrue(strpos($html, $expected) > 0, "Could not find '$expected' in '$html'");
	}
	
	function testGetSQLInClause(){
		// This method is tested more thoroughly in ExternalModulesTest.

		$getSQLInClause = function(){
			$clause = $this->getSQLInClause('a', [1]);
			$this->assertSame("(a IN ('1'))", $clause);
		};

		if($this->getFrameworkVersion() < 4){
			$getSQLInClause();
		}
		else{
			$this->assertThrowsException(function() use ($getSQLInClause){
				$getSQLInClause();
			}, ExternalModules::tt('em_errors_122'));
		}
	}

	function testCountLogs(){
		$whereClause = "message = ?";
		$message = rand();

		$assert = function($expected) use ($whereClause, $message){
			$actual = $this->countLogs($whereClause, $message);
			$this->assertSame($expected, $actual);
		};
		
		$assert(0);

		$this->log($message);
		$assert(1);

		$this->log($message);
		$assert(2);

		$this->getInstance()->removeLogs($whereClause, $message);
		$assert(0);
	}

	function testIsSafeToForwardMethodToFramework(){
		// The 'tt' methods are grandfathered in.
		$this->assertTrue($this->isSafeToForwardMethodToFramework('tt'));

		// This assertion specifically checks the method_exists() call in isSafeToForwardMethodToFramework()
		// to ensure infinite loops cannot occur.
		$this->assertThrowsException(function(){
			$this->getInstance()->someNonExistentMethod();
		}, 'method does not exist');
		
		$passThroughAllowed = $this->getFrameworkVersion() >= 5;
		$this->assertSame($passThroughAllowed, $this->isSafeToForwardMethodToFramework('getRecordIdField'));

		$methodName = 'getRecordIdField';
		$passThroughCall = function() use ($methodName){
			$this->getInstance()->{$methodName}(TEST_SETTING_PID);
		};
		
		if($passThroughAllowed){
			// Make sure no exception is thrown.
			$passThroughCall();
		}
		else{
			$this->assertThrowsException(function() use ($passThroughCall){
				$passThroughCall();
			}, ExternalModules::tt("em_errors_69", $methodName));
		}
	}

	function testIsSafeToForwardMethodToFramework_projectForwards(){
		$projectMethodName = 'getUsers';

		$action = function() use ($projectMethodName){
			$_GET['pid'] = TEST_SETTING_PID;
			$m = $this->getInstance();
			$m->{$projectMethodName}();
		};

		if($this->getFrameworkVersion() >= 7){
			$this->expectNotToPerformAssertions();
			$action();
		}
		else{
			$this->assertThrowsException($action, ExternalModules::tt("em_errors_69", $projectMethodName));
		}
	}

	function testGetRecordIdField(){
		$metadata = ExternalModules::getMetadata(TEST_SETTING_PID);
		$expected = array_keys($metadata)[0];
		
		$this->assertThrowsException(function(){
			$this->getRecordIdField();
		}, ExternalModules::tt('em_errors_65', 'pid'));

		$this->assertSame($expected, $this->getRecordIdField(TEST_SETTING_PID));

		$_GET['pid'] = TEST_SETTING_PID;
		$this->assertSame($expected, $this->getRecordIdField());
	}

	function testGetProjectSettings(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$_GET['pid'] = TEST_SETTING_PID;

		$value = rand();
		$this->setProjectSetting($value);
		$array = $m->getProjectSettings();

		$actual = $array[TEST_SETTING_KEY];

		if($this->getFrameworkVersion() < 5){
			$this->assertSame(null, @$actual['system_value']);
			$actual = $actual['value'];
		}

		$this->assertSame($value, $actual);
	}

	function testSetProjectSettings(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$_GET['pid'] = TEST_SETTING_PID;

		$value = rand();
		$m->setProjectSettings([
			TEST_SETTING_KEY => $value
		]);

		if($this->getFrameworkVersion() >= 5){
			$expected = $value;
		}
		else{
			$expected = null;
		}

		$this->assertSame($expected, $m->getProjectSetting(TEST_SETTING_KEY));
	}

	function testObjectReferencePassThrough(){
		$name = 'records';
		$expected = $this->getFramework()->{$name};
		$this->assertNotNull($expected);
		$this->assertSame($expected, $this->getInstance()->{$name});
	}

	function testGetProjectStatus(){
		$this->assertThrowsException(function(){
			$this->getProjectStatus(-1);
		}, ExternalModules::tt("em_errors_131"));

		// Test behavior for a PID that doesn't exist.
		$this->assertSame(null, $this->getProjectStatus(PHP_INT_MAX));

		$assert = function($expected, $status, $completedTime = null){
			// Clear the Project cache in REDCap core.
			$this->setPrivateVariable('project_cache', null, 'Project');
			
			$this->query('update redcap_projects set status = ?, completed_time = ? where project_id = ?', [$status, $completedTime, TEST_SETTING_PID]);
			$this->assertSame($expected, $this->getProjectStatus(TEST_SETTING_PID));
		};

		$assert(null, 3); // some status that isn't checked in this method
		$assert('DONE', 2, ExternalModules::makeTimestamp());
		$assert('AC', 2);
		$assert('PROD', 1);
		$assert('DEV', 0);
	}

	function testIsPHPGreaterThan()
	{
		$isPHPGreaterThan = function($requiredVersion){
			return $this->callPrivateMethodForClass($this->getFramework(), 'isPHPGreaterThan', $requiredVersion);
		};

		$versionParts = explode('.', PHP_VERSION);
		$lastNumber = $versionParts[2];

		$versionParts[2] = $lastNumber-1;
		$lowerVersion = implode('.', $versionParts);

		$versionParts[2] = $lastNumber+1;
		$higherVersion = implode('.', $versionParts);

		$this->assertTrue($isPHPGreaterThan(PHP_VERSION));
		$this->assertFalse($isPHPGreaterThan($higherVersion));
		$this->assertTrue($isPHPGreaterThan($lowerVersion));
	}	

	function testQueryLogs_parameters()
	{
		$m = $this->getInstance();
		$value = rand();
		$m->log('test', [
			'value' => $value
		]);

		$result = $m->queryLogs("select count(*) as count where value = ?", $value);
		$row = $result->fetch_assoc();

		$this->assertSame(1, $row['count']);
	}

	function testQueryLogs_parametersArgumentRequirement()
	{
		// On older framework versions, parameters are not required.
		$this->queryLogs("select 1");
		$this->expectNotToPerformAssertions();
	}

	function testQueryLogs_complexStatements()
	{
		$m = $this->getInstance();

		// Just make sure this query is parsable, and runs without an exception.
		$m->queryLogs("select 1 where a = 1 and (b = 2 or c = 3)", []);

		$this->assertTrue(true); // Each test requires an assertion
	}

	function testQueryLogs_complexSelectClauses()
	{
		$m = $this->getInstance();

		$paramName = 'some_param';
		$logId = $m->log('test', [
			$paramName => '12345'
		]);
		$whereClause = 'log_id = ?';

		// Make sure a function and an "as" clause work on a regular column.
		$result = $m->queryLogs("select length($paramName) as abc where $whereClause", $logId);
		$this->assertSame(5, $result->fetch_assoc()['abc']);

		// Make sure a function and an "as" clause work on a regular column.
		$result = $m->queryLogs("select unix_timestamp(timestamp) as abc where $whereClause", $logId);
		
		$row = $result->fetch_assoc();
		$aDayAgo = time() - ExternalModules::DAY_IN_SECONDS;
		$this->assertTrue($row['abc'] > $aDayAgo);

		$m->removeLogs($whereClause, $logId);
	}

	function testQueryLogs_multipleReferencesToSameColumn()
	{
		$m = $this->getInstance();

		// Just make sure this query is parsable, and runs without an exception.
		$m->queryLogs("select 1 where a > 1 and a < 5", []);

		$this->assertTrue(true); // Each test requires an assertion
	}

	function testQueryLogs_groupBy()
	{
		$paramName = 'some_param';
		for($i=0; $i<2; $i++){
			$this->log('some_message', [
				$paramName => 'some_value'
			]);
		}

		$assert = function($sql, $expectedCount){
			$result = $this->queryLogs($sql, []);
			$this->assertSame($expectedCount, $result->num_rows);
		};

		$sql = 'select log_id, message';
		$assert($sql, 2);
		$assert($sql . " group by $paramName", 1);
	}

	function testQueryLogs_orderBy()
	{
		$expected = [];
		$paramName = 'some_param';
		for($i=0; $i<3; $i++){
			$logId = $this->log('some message', [
				$paramName => $i
			]);

			$expected[] = [
				'log_id' => (string) $logId,
				$paramName => (string) $i
			];
		}

		$assert = function($order, $expected) use ($paramName){
			foreach(['log_id', $paramName] as $orderColumn){
				$result = $this->queryLogs("select log_id, $paramName order by $orderColumn $order", []);
	
				$actual = [];
				while($row = $result->fetch_assoc()){
					$actual[] = $row;
				}
	
				$this->assertSame($expected, $actual);
			}
		};

		$assert('asc', $expected);
		$assert('desc', array_reverse($expected));
	}

	function testQueryLogs_stars()
	{
		$m = $this->getInstance();

		// "select count(*)" should be allowed
		$result = $m->queryLogs("select count(*) as count where some_fake_parameter = 1", []);
		$row = $result->fetch_assoc();
		$this->assertSame('0', $row['count']);

		// "select *" should not be allowed
		$this->assertThrowsException(function() use ($m){
			$m->queryLogs('select * where some_fake_parameter = 1');
		}, "Columns must be explicitly defined in all log queries");
	}

	function testRemoveLogs()
	{
		$m = $this->getInstance();
		$message = rand();
		$logId1 = $m->log($message);
		$logId2 = $m->log($message);

		$m->removeLogs("log_id = ?", $logId1);

		$result = $m->queryLogs('select log_id where message = ?', $message);
		$this->assertSame($logId2, $result->fetch_assoc()['log_id']);
		
		// Make sure only one row exists
		$this->assertNull($result->fetch_assoc());

		$this->assertThrowsException(function() use ($m){
			$m->removeLogs('');
		}, 'must specify a where clause');

		$this->assertThrowsException(function() use ($m){
			$m->removeLogs('external_module_id = 1');
		}, 'not allowed to prevent modules from accidentally removing logs for other modules');
	}

	function testRemoveLogs_parametersArgumentRequirement()
	{
		// On older framework versions, parameters are not required.
		$this->removeLogs("1 = 2");
		$this->expectNotToPerformAssertions();
	}

	private function assertIndex($expectedExcerpt = null){
        $csrfToken = @$_POST['redcap_external_module_csrf_token'];

        $m = $this->getInstance();

        $require = function() use ($m){
            return $this->captureOutput(function() use ($m){
                require __DIR__ . '/../index.php';
                if($module !== $m){
                    throw new Exception('The module instance was not defined as expected.');
                }
            });
        };

        if($expectedExcerpt){
            $this->assertThrowsException($require, $expectedExcerpt);
        }
        else{
            $expectedOutput = (string) rand();
            if(!isset($_GET['NOAUTH'])){
                $expectedOutput = $this->captureOutput(function(){
                    require APP_PATH_DOCROOT . '/ControlCenter/header.php';
                    echo $expectedOutput;
                    require APP_PATH_DOCROOT . '/ControlCenter/footer.php';
                });
            }

            $m->pageLoadOutput = $expectedOutput;
            $actualOutput = $require();
            $this->assertSame($expectedOutput, $actualOutput);

			// Make sure the token is unset.
			$this->assertFalse(isset($_POST['redcap_external_module_csrf_token']));
        }

        // Put the token back the way it was beforehand.
		$_POST['redcap_external_module_csrf_token'] = $csrfToken;
    }

    private function captureOutput($action){
        ob_start();
        try{
            $action();
        }
        finally{
            $output = ob_get_contents();
            ob_end_clean();
        }

        return $output;
    }

    function testIndex(){
        $this->assertIndex(ExternalModules::tt('em_errors_123'));

        $prefix = 'some_disabled_prefix';
        $_GET['prefix'] = $prefix;
        $this->assertIndex(ExternalModules::tt('em_errors_124', $prefix));

        $prefix = TEST_MODULE_PREFIX;
        $_GET['prefix'] = TEST_MODULE_PREFIX;
        $_GET['NOAUTH'] = '';
        $this->assertIndex(ExternalModules::tt('em_errors_125', $prefix));

        $pid = TEST_SETTING_PID;
        $_GET['pid'] = TEST_SETTING_PID;
        unset($_GET['NOAUTH']);
        $this->assertIndex(ExternalModules::tt('em_errors_126', $prefix, $pid));

        unset($_GET['pid']);
        $page = 'some_page_that_does_not_exist';
        $_GET['page'] = $page;
        $this->assertIndex(ExternalModules::tt('em_errors_127', $prefix, $page));

        $page = 'unit_test_page';
        $_GET['page'] = $page;
        $this->assertIndex();
        
        $m = $this->getInstance();
        $m->setLinkCheckDisplayReturnValue(false);
        $this->assertIndex();

        $config = [
            'links' => [
                'control-center' => [
                    [
                        'name' => 'Unit Test Page',
                        'url' => $page
                    ]
                ]
            ]
        ];

        $this->setConfig($config);
        $this->assertIndex(ExternalModules::tt('em_errors_128'));

        $m->setLinkCheckDisplayReturnValue(true);
        $this->assertIndex();
        
        $_GET['NOAUTH'] = '';
        $config['no-auth-pages'] = [$page];
        $this->setConfig($config);
        $this->assertIndex();
	}

	function testInitCSRFDoubleSubmitCookie(){
		$_GET['NOAUTH'] = ''; // Use double submit cookies instead of System::getCsrfToken()

		unset($_COOKIE['redcap_external_module_csrf_token']);
		$this->assertNull($this->getCSRFToken());
		
		ExternalModules::initCSRFDoubleSubmitCookie();
		$this->assertSame(80, strlen($this->getCSRFToken()));
	}

	function testGetCSRFToken(){
		\Authentication::setAAFCsrfToken(null);
		$this->assertSame($_POST['redcap_csrf_token'], $this->getCSRFToken());
		
		$_GET['NOAUTH'] = '';
		$token = rand();
		$_COOKIE['redcap_external_module_csrf_token'] = $token;
		$this->assertSame($token, $this->getCSRFToken());
	}

	function testIndex_csrfToken(){
		$isCSRFFrameworkVersion = $this->getFrameworkVersion() >= 8;
		
		// This call also sets $_POST['redcap_csrf_token']
		\Authentication::setAAFCsrfToken(null);
		
		// Simulate the correct token being posted
		$_POST['redcap_external_module_csrf_token'] = $_POST['redcap_csrf_token'];

		// Unset the original token like REDCap would internally.
		unset($_POST['redcap_csrf_token']);
		
		$page = 'unit_test_page';
		$_GET['page'] = $page;
		$_GET['prefix'] = TEST_MODULE_PREFIX;

		$this->setConfig([
			'no-auth-pages' => [
				$page
			]
		]);

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_COOKIE['redcap_external_module_csrf_token'] = $_POST['redcap_external_module_csrf_token'];
		$this->assertIndex(); // Valid token
		
		$expectedError = null;
		if($isCSRFFrameworkVersion){
			$expectedError = ExternalModules::tt('em_errors_158');
		}
		
		$_GET['NOAUTH'] = '';
		$this->assertIndex(); // CSRF checking works on NOAUTH pages

		unset($_COOKIE['redcap_external_module_csrf_token']);
		$this->assertIndex($expectedError); // Posted token doesn't match cookie
		
		$expectedError = null;
		if($isCSRFFrameworkVersion){
			$expectedError = ExternalModules::tt('em_errors_157');
		}

		unset($_POST['redcap_external_module_csrf_token']);
		$this->assertIndex($expectedError);

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$this->assertIndex(); // CSRF checking is not required for GET requests

		$_SERVER['REQUEST_METHOD'] = 'POST';
		$this->assertIndex($expectedError); // Make sure we're still failing before the next assertion

		$this->setConfig([
			'no-auth-pages' => [
				$page
			],
			'no-csrf-pages' => [
				$page
			]
		]);

		$this->assertIndex(); // CSRF tokens are not required on "no-csrf-pages"
    }

	function testGetChoiceLabel_data(){
		$recordId = 1;

		$this->saveData([[
			TEST_RECORD_ID => $recordId,
			TEST_CHECKBOX_FIELD . '___1' => true,
			TEST_CHECKBOX_FIELD . '___3' => true,
		]]);

		// We may want to consider this feature deprecated, since it was only ever implemented for checkboxes, and possibly only used in the Email Alerts module.
		$this->assertSame('a, c', $this->getChoiceLabel([
			'project_id' => TEST_SETTING_PID,
			'event_id' => $this->getEventId(TEST_SETTING_PID),
			'record_id' => $recordId,
			'field_name' => TEST_CHECKBOX_FIELD,
		]));
	}

	function testGetChoiceLabel_radio(){
		$assert = function($pid = null){
			// These values are defined in test-project-data-dictionary.csv.
			$value = '1';
			$expected = 'a';

			// Old syntax
			$this->assertSame($expected, $this->getChoiceLabel(TEST_RADIO_FIELD, $value, $pid));
		
			$args = [
				'field_name' => TEST_RADIO_FIELD,
				'value' => $value,
				'project_id' => $pid,
			];

			// New syntax
			$this->assertSame($expected, $this->getChoiceLabel($args));
		};
		
		$assert(TEST_SETTING_PID);

		$_GET['pid'] = TEST_SETTING_PID;
		$assert();
	}

	function testGetChoiceLabel_sql(){
		$result = $this->query("
			select * from redcap_metadata
			where
				project_id = ?
				and field_name = ?
		", [TEST_SETTING_PID, TEST_SQL_FIELD]);

		$field = $result->fetch_assoc();
		$result = $this->query($field['element_enum'], []);
		$choices = $result->fetch_all();

		foreach($choices as $choice){
			$code = $choice[0];
			if($code === '' || $code === null
			){
				// Ensure historical behavior is maintained.
				$expectedLabel = '';
			}
			else{
				$expectedLabel = $choice[1];
			}

			$actualLabel = $this->getChoiceLabel($field['field_name'], $code, $field['project_id']);
			
			$this->assertSame($expectedLabel, $actualLabel, "Failed on field: " . json_encode($field));
		}
	}

	function testGetChoiceLabels(){
		$fieldName = 'test_radio_field';
		$expected = [
			1 => 'a',
			2 => 'b',
			3 => 'c'
		];
		
		$this->assertSame($expected, $this->getChoiceLabels($fieldName, TEST_SETTING_PID));

		$this->assertThrowsException(function() use ($fieldName){
			$this->getChoiceLabels($fieldName);
		}, ExternalModules::tt('em_errors_65', 'pid'));

		$_GET['pid'] = TEST_SETTING_PID;
		$this->assertSame($expected, $this->getChoiceLabels($fieldName));
	}

	function testInitializeJavascriptModuleObject(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		ob_start();
		$m->initializeJavascriptModuleObject();
		$output = ob_get_clean();

		$reflectionClass = new \ReflectionClass(TestModule::class);
		$namespace = $reflectionClass->getNamespaceName();
		$namespace = str_replace('\\', '.', $namespace);

		$expectedExcerpt = "var module = ExternalModules.$namespace";
		$this->assertTrue(strpos($output, $expectedExcerpt) !== false);
	}

	function testGetFirstEventId(){
		$_GET['pid'] = TEST_SETTING_PID;
		$eventId = $this->getEventId(TEST_SETTING_PID);

		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();
		$this->assertSame($eventId, $m->getFirstEventId());
	}

	function testGetIP()
	{
		$ip = '1.2.3.4';
		$_SERVER['HTTP_CLIENT_IP'] = $ip;
		$username = 'jdoe';
		ExternalModules::setUsername($username);

		$assertIp = function($expected, $param = null){
			$this->assertSame($expected, $this->callPrivateMethodForClass($this->getFramework(), 'getIP', $param));
		};

		$ipParameter = '2.3.4.5';
		$assertIp($ipParameter, $ipParameter);

		$assertIp($ip);

		$_SERVER['REQUEST_URI'] = APP_PATH_SURVEY;
		$assertIp(null);

		$_SERVER['REQUEST_URI'] = '';
		$assertIp($ip);

		ExternalModules::setUsername(null);
		$assertIp(null);

		ExternalModules::setUsername($username);
		$assertIp($ip);

		unset($_SERVER['HTTP_CLIENT_IP']);
		$assertIp(null);
	}

	function testLogAndQueryLog()
	{
		$m = $this->getInstance();
		$testingModuleId = $this->getUnitTestingModuleId();

		// Remove left over messages in case this test previously failed
		$m->query('delete from redcap_external_modules_log where external_module_id = ?', [$testingModuleId]);

		$message = TEST_LOG_MESSAGE;
		$paramName1 = 'testParam1';
		$paramValue1 = rand();
		$paramName2 = 'testParam2';
		$paramValue2 = rand();
		$paramName3 = 'testParam3';

		$query = function () use ($m, $testingModuleId, $message, $paramName1, $paramName2) {
			$results = $m->queryLogs("
				select log_id,timestamp,username,ip,external_module_id,record,message,$paramName1,$paramName2
				where
					message = ?
					and timestamp > ?
				order by log_id asc
			", [$message, date('Y-m-d', time()-10)]);

			$timestampThreshold = 5;

			$rows = [];
			while ($row = $results->fetch_assoc()) {
				$currentUTCTime = new \DateTime("now", new \DateTimeZone("UTC"));
				$timeSinceLog = $currentUTCTime->getTimestamp() - strtotime($row['timestamp']);

				$this->assertTrue(gettype($row['log_id']) === 'integer');
				$this->assertTrue($timeSinceLog < $timestampThreshold);
				$this->assertEquals($testingModuleId, $row['external_module_id']);
				$this->assertEquals($message, $row['message']);

				$rows[] = $row;
			}

			return $rows;
		};

		ExternalModules::setUsername(null);
		$_SERVER['HTTP_CLIENT_IP'] = null;
		$this->setRecordId(null);
		$m->log($message);

		$username = $this->getRandomUsername();

		ExternalModules::setUsername($username);
		$_SERVER['HTTP_CLIENT_IP'] = '1.2.3.4';
		$this->setRecordId('abc-' . rand()); // We prepend a string to make sure alphanumeric record ids work.
		$m->log($message, [
			$paramName1 => $paramValue1,
			$paramName2 => $paramValue2,
			$paramName3 => null
		]);

		$rows = $query();
		$this->assertEquals(2, count($rows));
		
		$row = $rows[0];
		$this->assertSame($message, $row['message']);
		$this->assertNull($row['username']);
		$this->assertNull($row['ip']);
		$this->assertNull($row['record']);
		$this->assertFalse(isset($row[$paramName1]));
		$this->assertFalse(isset($row[$paramName2]));

		$row = $rows[1];
		$this->assertEquals($username, $row['username']);
		$this->assertEquals($_SERVER['HTTP_CLIENT_IP'], $row['ip']);
		$this->assertEquals($m->getRecordId(), $row['record']);
		$this->assertEquals($paramValue1, $row[$paramName1]);
		$this->assertEquals($paramValue2, $row[$paramName2]);
		$this->assertNull($row[$paramName3]);

		$m->removeLogs("$paramName1 is null", []);
		$rows = $query();
		$this->assertEquals(1, count($rows));
		$this->assertEquals($paramValue1, $rows[0][$paramName1]);

		$m->removeLogs("message = '$message'", []);
		$rows = $query();
		$this->assertEquals(0, count($rows));
	}

	function testLogAndQueryLog_allowedCharacters()
	{
		$name = 'aA1 -_$';
		$value = (string) rand();
		
		$logId = $this->log('foo', [
			$name => $value,
			'goo' => 'doo'
		]);

		$whereClause = 'log_id = ?';
		$result = $this->queryLogs("select log_id, timestamp, goo, `$name` where $whereClause", $logId);
		$row = $result->fetch_assoc();
		$this->assertSame($value, $row[$name]);
		$this->removeLogs($whereClause, $logId);
	}

	function testLogAndQueryLog_disallowedCharacters()
	{
		$invalidParamName = 'sql injection ; example';
		
		$assertThrowsException = function($action) use ($invalidParamName){
			$this->assertThrowsException($action, ExternalModules::tt('em_errors_115', $invalidParamName));
		};

		$assertThrowsException(function() use ($invalidParamName){
			$this->log('foo', [
				$invalidParamName => rand()
			]);
		});
		$this->removeLogs('log_id = ?', db_insert_id());

		$assertThrowsException(function() use ($invalidParamName){
			$this->queryLogs("select 1 where `$invalidParamName` is null");
		});
	}

	function testLog_timestamp()
	{
		$m = $this->getInstance();

		$timestamp = ExternalModules::makeTimestamp(time()-ExternalModules::HOUR_IN_SECONDS);
		$logId = $m->log('test', [
			'timestamp' => $timestamp
		]);
		
		$this->assertLogValues($logId, [
			'timestamp' => $timestamp
		]);
	}

	function testLog_pid()
	{
		$m = $this->getInstance();
		$message = 'test';
		$whereClause = "message = ?";
		$expectedPid = (string) rand();

		$assertRowCount = function($expectedCount) use ($m, $message, $whereClause, $expectedPid){
			$result = $m->queryLogs('select pid where ' . $whereClause, $message);
			$rows = [];
			while($row = $result->fetch_assoc()){
				$rows[] = $row;

				$pid = @$_GET['pid'];
				if(!empty($pid)){
					$this->assertEquals($expectedPid, $pid);
				}
			}

			$this->assertEquals($expectedCount, count($rows));
		};

		$m->log($message);
		$_GET['pid'] = $expectedPid;
		$m->log($message);

		// A pid is still set, so only that row should be returned.
		$assertRowCount(1);

		// Unset the pid and make sure both rows are returned.
		$_GET['pid'] = null;
		$assertRowCount(2);

		// Re-set the pid and attempt to remove only the pid row
		$_GET['pid'] = $expectedPid;
		$m->removeLogs($whereClause, $message);

		// Unset the pid and make sure only the row without the pid is returned
		$_GET['pid'] = null;
		$assertRowCount(1);

		// Make sure removeLogs() now removes the row without the pid.
		$m->removeLogs($whereClause, $message);
		$assertRowCount(0);
	}

	function testLog_emptyMessage()
	{
		$m = $this->getInstance();

		foreach ([null, ''] as $value) {
			$this->assertThrowsException(function () use ($m, $value) {
				$m->log($value);
			}, 'A message is required for log entries.');
		}
	}

	function testLog_reservedParameterNames()
	{
		$m = $this->getInstance();

		$reservedParameterNames = AbstractExternalModule::$RESERVED_LOG_PARAMETER_NAMES;

		foreach ($reservedParameterNames as $name) {
			$this->assertThrowsException(function () use ($m, $name) {
				$m->log('test', [
					$name => 'test'
				]);
			}, 'parameter name is set automatically and cannot be overridden');
		}
	}

	function testLog_recordId()
	{
		$m = $this->getInstance();

		$this->setRecordId(null);
		$logId = $m->log('test');
		$this->assertLogValues($logId, [
			'record' => null
		]);

		$generateRecordId = function(){
			return 'some prefix to make sure string record ids work - ' . rand();
		};

		$message = TEST_LOG_MESSAGE;
		$recordId1 = $generateRecordId();
		$this->setRecordId($recordId1);

		$logId = $m->log($message);
		$this->assertLogValues($logId, ['record' => $recordId1]);

		// Make sure the detected record id can be overridden by developers
		$params = ['record' => $generateRecordId()];
		$logId = $m->log($message, $params);
		$this->assertLogValues($logId, $params);
	}

	// Verifies that the specified values are stored in the database under the given log id.
	private function assertLogValues($logId, $expectedValues = [])
	{
		$columnNamesSql = implode(',', array_keys($expectedValues));
		$selectSql = "select $columnNamesSql where log_id = ?";

		$m = $this->getInstance();
		$result = $m->queryLogs($selectSql, $logId);
		$log = $result->fetch_assoc();

		foreach($expectedValues as $name=>$expectedValue){
			$actualValue = $log[$name];
			$this->assertSame($expectedValue, $actualValue, "For the '$name' log parameter:");
		}
	}

	function testLog_escapedCharacters()
	{
		$m = $this->getInstance();
		$maliciousSql = "'; delete from everything";
		$m->log($maliciousSql, [
			"malicious_param" => $maliciousSql
		]);

		$selectSql = 'select message, malicious_param order by timestamp desc limit 1';
		$result = $m->queryLogs($selectSql, []);
		$row = $result->fetch_assoc();
		$this->assertSame($maliciousSql, $row['message']);
		$this->assertSame($maliciousSql, $row['malicious_param']);
	}

	function testLog_spacesInParameterNames()
	{
		$m = $this->getInstance();

		$paramName = "some param";
		$paramValue = "some value";

		$m->log('test', [
			$paramName => $paramValue
		]);

		$selectSql = "select `$paramName` where `$paramName` is not null order by `$paramName`";
		$result = $m->queryLogs($selectSql, []);
		$row = $result->fetch_assoc();
		$this->assertSame($paramValue, $row[$paramName]);

		$m->removeLogs("`$paramName` is not null", []);
		$result = $m->queryLogs($selectSql, []);
		$this->assertNull($result->fetch_assoc());
	}

	function testLog_unsupportedTypes()
	{
		$this->assertThrowsException(function(){
			$m = $this->getInstance();
			$m->log('foo', [
				'some-unsupported-type' => new \stdClass()
			]);
		}, "The type 'object' for the 'some-unsupported-type' parameter is not supported");
	}

	function testLog_overridableParameters()
	{
		$m = $this->getInstance();

		$testValues = [
			'timestamp' => date("Y-m-d H:i:s"),
			'username' => $this->getRandomUsername(),
			'project_id' => 1
		];

		foreach(AbstractExternalModule::$OVERRIDABLE_LOG_PARAMETERS_ON_MAIN_TABLE as $name){
			$value = $testValues[$name];
			if(empty($value)){
				$value = 'foo';
			}

			$params = [
				$name => $value
			];

			$logId = $m->log('foo', $params);
			$this->assertLogValues($logId, $params);

			// Make sure a parameter table entry was NOT made, since the value should only be stored in the main log table.
			$result = $m->query("select * from redcap_external_modules_log_parameters where log_id = ?", [$logId]);
			$row = $result->fetch_assoc();
			$this->assertNull($row);
		}
	}

	function testLog_emptyParamNames()
	{
		$this->assertThrowsException(function(){
			$this->log('foo', [
				'' => rand()
			]);
		}, ExternalModules::tt('em_errors_116'));

		$this->removeLogs('log_id = ?', db_insert_id());
	}

	private function getUnitTestingModuleId()
	{
		$id = ExternalModules::getIdForPrefix(TEST_MODULE_PREFIX);
		$this->assertTrue(ctype_digit($id));
		
		return $id;
	}

	function testGetPublicSurveyUrl(){
		$m = $this->getInstance();

		$result = $m->query("
			select *
			from (
				select s.project_id, h.hash, count(*)
				from redcap_surveys s
				join redcap_surveys_participants h
					on s.survey_id = h.survey_id
				join redcap_metadata m
					on m.project_id = s.project_id
					and m.form_name = s.form_name
					and field_order = 1 -- getting the first field is the easiest way to get the first form
				where participant_email is null
				group by s.form_name -- test a form name that exists on multiple projects
				order by count(*) desc
				limit 100
			) a
			order by rand() -- select a random row to make sure we often end up with a different project ID than getPublicSurveyUrl() would by default if it didn't specific a project ID in it's query
			limit 1
		", []);

		$row = $result->fetch_assoc();
		$projectId = $row['project_id'];
		$hash = $row['hash'];

		global $Proj;
		$Proj = new \Project($projectId);
		$_GET['pid'] = $projectId;
		
		$expected = APP_PATH_SURVEY_FULL . "?s=$hash";
		$actual = $m->getPublicSurveyUrl();

		$this->assertSame($expected, $actual);
	}

	function assertLogAjax($data)
	{
		$data['message'] = TEST_LOG_MESSAGE;

		$this->setRecordId(null);

		$logId = $this->logAjax($data);
		$this->assertLogValues($logId, [
			'record' => $data['parameters']['record']
		]);

		// TODO - At some point, it would be nice to test the survey hash parameters here.
	}

	function testLogAjax_overridableParameters()
	{
		foreach(AbstractExternalModule::$OVERRIDABLE_LOG_PARAMETERS_ON_MAIN_TABLE as $name){
			$this->assertThrowsException(function() use ($name){
				$this->assertLogAjax([
					'parameters' => [
						$name => 'foo'
					]
				]);
			}, "'$name' parameter cannot be overridden via AJAX log requests");
		}
	}

	function testLogAjax_record()
	{
		// Make sure these don't throw an exception
		$this->assertLogAjax([
			'noAuth' => true
		]);
		$this->assertLogAjax([
			'noAuth' => true,
			'parameters' => [
				'record' => ExternalModules::EXTERNAL_MODULES_TEMPORARY_RECORD_ID . '-123'
			]
		]);

		$this->assertThrowsException(function(){
			$this->assertLogAjax([
				'noAuth' => true,
				'parameters' => [
					'record' => '123'
				]
			]);
		}, "'record' parameter cannot be overridden via AJAX log requests");
	}

	function testResetSurveyAndGetCodes_partial(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		// Just make sure it runs without exception for now.  We can expand this test in the future.
		$m->resetSurveyAndGetCodes(TEST_SETTING_PID, 1);
		$this->expectNotToPerformAssertions();
	}

	function testCreatePassthruForm(){
		$m = $this->getInstance();
		ob_start();
		$m->createPassthruForm(TEST_SETTING_PID, 1);
		$form = ob_get_clean();
		$this->assertStringContainsString('document.passthruform.submit', $form);
	}

	function testGetValidFormEventId(){
		$pid = TEST_SETTING_PID;
		$formName = ExternalModules::getFormNames($pid)[0];
		$expected = $this->getValidFormEventId($formName, $pid);
		$actual = (string) $this->getFramework()->getEventId($pid);

		$this->assertSame($expected, $actual);
	}

	function testGetSurveyId(){
		list($surveyId, $formName) = $this->getSurveyId(TEST_SETTING_PID);
		$this->assertTrue(ctype_digit($surveyId));
		$this->assertTrue($surveyId > 0);
		$this->assertSame(ExternalModules::getFormNames(TEST_SETTING_PID)[0], $formName);
	}

	function testThrottle()
	{
		$message = 'test message';
		$logIds = [];

		$assert = function($expected, $maxOccurrences) use ($message){
			$actual = $this->throttle('message = ?', $message, 60, $maxOccurrences);
			$this->assertSame($expected, $actual);
		};

		$log = function() use ($message, &$logIds){
			$logIds[] = $this->log($message);
		};

		$setFirstLogTime = function($time) use (&$logIds){
			$timestamp = date('Y-m-d H:i:s', $time);
			$this->query('update redcap_external_modules_log set timestamp = ? where log_id = ?', [$timestamp, $logIds[0]]);
		};

		$assert(false, 1);
		$log($message);
		$assert(true, 1);
		$assert(false, 2);
		$log($message);
		$assert(true, 2);

		$setFirstLogTime(time()-58);
		$assert(true, 2);
		$setFirstLogTime(time()-61);
		$assert(false, 2);
	}
	
	function testTt(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$key = 'some_key';
		$value = rand();
		$this->spoofTranslation(TEST_MODULE_PREFIX, $key, $value);

		$key = 'some_key';
		$this->assertSame($value, $m->tt($key));
	}

	function testTt_transferToJavascriptModuleObject(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$key = 'some_key';
		$value = rand();
		$this->spoofTranslation(TEST_MODULE_PREFIX, $key, $value);
		
		ob_start();
		$m->tt_transferToJavascriptModuleObject($key, $value);
		$actual = ob_get_clean();

		$this->assertJSLanguageKeyAdded($key, $value, $actual);
	}

	function testTt_addToJavascriptModuleObject(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$key = 'some_key';
		$value = rand();

		ob_start();
		$m->tt_addToJavascriptModuleObject($key, $value);
		$actual = ob_get_clean();
		
		$this->assertJSLanguageKeyAdded($key, $value, $actual);
	}

	function assertJSLanguageKeyAdded($key, $value, $actual){
		$this->assertSame("<script>ExternalModules.\$lang.add(\"emlang_" . TEST_MODULE_PREFIX . "_$key\", $value)</script>", $actual);
	}

	function testIsSurveyPage(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$_SERVER['REQUEST_URI'] = $m->getUrl('foo.php');
		$this->assertFalse($m->isSurveyPage());

		$_SERVER['REQUEST_URI'] = APP_PATH_SURVEY;
		$this->assertTrue($m->isSurveyPage());

		$_SERVER['REQUEST_URI'] .= '?__passthru=DataEntry%2Fimage_view.php';
		$this->assertFalse($m->isSurveyPage());
	}

	function testGetPublicSurveyHash(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$result = $m->query("
			select p.hash 
			from redcap_surveys s
			join redcap_surveys_participants p
			on s.survey_id = p.survey_id
			join redcap_metadata  m
			on m.project_id = s.project_id and m.form_name = s.form_name
			where p.participant_email is null and m.field_order = 1 and s.project_id = ?
		", TEST_SETTING_PID);

		$row = $result->fetch_assoc();

		$this->assertSame($row['hash'], $m->getPublicSurveyHash(TEST_SETTING_PID));
	}

	function testSetRecordId(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$value = rand();
		$m->setRecordId($value);
		$this->assertSame($value, $m->getRecordId());
	}

	function testDetectParameter_sqlInjection(){
		$_GET['pid'] = 'delete * from an_important_table';
		$this->assertEquals(0, $this->callPrivateMethodForClass($this->getFramework(), 'detectParameter', 'pid'));
	}
	
	function testUserSettings(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$key = 'test_user_setting_key';
		
		$value = rand();
		$m->setUserSetting($key, $value);
		$this->assertSame($value, $m->getUserSetting($key));

		$m->removeUserSetting($key);
		$this->assertNull($m->getUserSetting($key));
	}

	function testGetFieldLabel(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$_GET['pid'] = TEST_SETTING_PID;
		$this->assertSame('Test Text Field', $m->getFieldLabel(TEST_TEXT_FIELD));
	}

	function testSendAdminEmail(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$subject = rand();
		$content = rand();
		$m->sendAdminEmail($subject, $content);

		$this->assertSame([$subject, $content, TEST_MODULE_PREFIX], ExternalModulesTest::$lastSendAdminEmailArgs);
	}

	function testGetConfig(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$config = $m->getConfig();
		$this->assertSame($this->getFrameworkVersion(), $config['framework-version']);
	}

	function testGetModuleDirectoryName(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$this->assertSame(TEST_MODULE_PREFIX . '_' . TEST_MODULE_VERSION, $m->getModuleDirectoryName());
	}

	function testGetSystemSettings(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$value = rand();
		$m->setSystemSetting(TEST_SETTING_KEY, $value);

		$expected = [
			TEST_SETTING_KEY => [
				'system_value' => $value,
				'value' => $value
			]
		];
		
		$this->assertSame($expected, $m->getSystemSettings());
	}

	function testDetectProjectId()
	{
		$detect = function($value){
			return $this->callPrivateMethodForClass($this->getFramework(), 'detectProjectId', $value);
		};

		$this->assertSame(null, $detect(null));

		$value = rand();
		$this->assertSame($value, $detect($value));

		$_GET['pid'] = $value;
		$this->assertSame($value, $detect(null));
	}

	function testGetModuleName(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$value = rand();
		$this->setConfig([
			'name' => $value
		]);

		$this->assertSame($value, $this->getModuleName());
	}

	function testGetMetadata(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();
		
		$metadata = $m->getMetadata(TEST_SETTING_PID);

		$this->assertSame('text', $metadata[TEST_TEXT_FIELD]['field_type']);
	}

	function testSaveData(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$recordId = 1;
		$eventId = $m->getFirstEventId(TEST_SETTING_PID);
		$value = (string) rand();
		$m->saveData(TEST_SETTING_PID, $recordId, $eventId, [
			TEST_TEXT_FIELD => $value
		]);

		$actual = json_decode(\REDCap::getData(TEST_SETTING_PID, 'json', $recordId, TEST_TEXT_FIELD), true)[0][TEST_TEXT_FIELD];

		$this->assertSame($value, $actual);
	}

	function testSaveInstanceData(){
		// Run against the module instance rather than the framework instance, even prior to v5.
		$m = $this->getInstance();

		$recordId = 1;
		$eventId = $m->getFirstEventId(TEST_SETTING_PID);
		$value = (string) rand();
		$m->saveInstanceData(TEST_SETTING_PID, $recordId, $eventId, TEST_REPEATING_FORM, [
			1 => [
				TEST_REPEATING_FIELD_1 => $value
			]
		]);

		/**
		 * The above doesn't actually save any data, I believe because the saveInstanceData() method is broken.
		 * For full backward compatibility, this test still ensures that the method call succeeds, even if it actually save any data.
		 */
		$this->expectNotToPerformAssertions();
	}
	
	function testGetData($methodName = 'getData'){
		$expected = [
			TEST_RECORD_ID => (string) rand(),
			TEST_TEXT_FIELD => (string) rand()
		];

		$this->saveData([$expected]);

		$actual = json_decode($this->getModuleInstance()->$methodName(TEST_SETTING_PID, $expected[TEST_RECORD_ID], '', 'json'), true)[0];

		$actual = [
			TEST_RECORD_ID => $actual[TEST_RECORD_ID],
			TEST_TEXT_FIELD => $actual[TEST_TEXT_FIELD]
		];

		$this->assertSame($expected, $actual);
	}

	private function assertQueryData($expected, $sql, $params = [], $message = 'assertQueryData() failed', $pid = TEST_SETTING_PID){
		$result = $this->getProject($pid)->queryData($sql, $params);

		$actual = [];
		while($row = $result->fetch_assoc()){
			$actual[] = $row;
		}

		$this->assertSame($expected, $actual, $message);
	}

	function testQueryData_parameters(){
		$recordId = (string) rand();
		
		$expected = [
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => ''		
			]
		];

		$this->saveData($expected);

		$sql = 'select ' . TEST_RECORD_ID . ' where ' . TEST_RECORD_ID . ' = ?';
		$this->assertQueryData($expected, $sql, $recordId);
	}

	function testQueryData_withOrWithoutBrackets(){
		$recordId = (string) rand();
		
		$expected = [
			[
				TEST_RECORD_ID => $recordId,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => ''		
			]
		];

		$this->saveData($expected);

		foreach([TEST_RECORD_ID, '[' . TEST_RECORD_ID . ']'] as $fieldName){
			$sql = "select $fieldName where $fieldName = ?";
			$this->assertQueryData($expected, $sql, $recordId);
		}
	}

	function testQueryData_or(){
		$recordId1 = (string) rand();
		$recordId2 = (string) $recordId1+1;
		
		$expected = [
			[
				TEST_RECORD_ID => $recordId1,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => ''		
			]
		];

		$this->saveData($expected, TEST_SETTING_PID);

		$this->saveData([
			[
				TEST_RECORD_ID => $recordId2,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => ''		
			]
		], TEST_SETTING_PID_2);

		$sql = 'select ' . TEST_RECORD_ID . ' where ' . TEST_RECORD_ID . ' = ? or ' . TEST_RECORD_ID . ' = ?';

		// Ensure only the data from TEST_SETTING_PID is returned.
		// We used to have a bug where 'OR' clauses weren't appropriately wrapped in parenthesis and always queried all projects.
		$this->assertQueryData($expected, $sql, [$recordId1, $recordId2]);
	}

	function testQueryData_project(){
		$assert = function($pid, $message){
			$expected = [];
			$sql = 'select ' . TEST_RECORD_ID . ', ' . TEST_TEXT_FIELD . ' where project_id = ?';
			$this->assertQueryData($expected, $sql, $pid, $message);
			
			$expected[] = [
				TEST_RECORD_ID => (string) rand(),
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => (string) rand()
			];
				
			$this->saveData($expected, $pid);

			$this->assertQueryData($expected, $sql, [$pid], $message, $pid);
		};

		$assert(TEST_SETTING_PID, "Make sure data for one project is returned.");

		$_GET['event_id'] = $this->getEventIds(TEST_SETTING_PID_2)[1];
		$assert(TEST_SETTING_PID_2, "Make sure ONLY data for the second project is returned.");	
	}
	
	function testQueryData_records(){
		$expected = [
			[
				TEST_RECORD_ID => (string) rand(),
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',		
				TEST_TEXT_FIELD => (string) rand()
			],
			[
				TEST_RECORD_ID => (string) rand(),
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => (string) rand()
			]
		];

		\REDCap::saveData(TEST_SETTING_PID, 'json', json_encode($expected));

		foreach($expected as $record){
			$sql = 'select ' . TEST_RECORD_ID . ', ' . TEST_TEXT_FIELD . ' where ' . TEST_RECORD_ID . ' = ?';
			$recordId = $record[TEST_RECORD_ID];
			$this->assertQueryData([$record], $sql, $recordId);
		}
	}

	function testQueryData_recordIdField(){
		$record = [
			TEST_RECORD_ID => (string) rand(),
			'redcap_repeat_instrument' => '',
			'redcap_repeat_instance' => '',
			TEST_TEXT_FIELD => (string) rand()
		];

		$this->saveData([$record]);

		$assert = function($fields, $expected){
			$sql = 'select ' . implode(',', $fields);
			$this->assertQueryData($expected, $sql, []);
		};

		$assert([TEST_RECORD_ID, TEST_TEXT_FIELD], [$record]);

		$assert([TEST_RECORD_ID], [
			[
				TEST_RECORD_ID => $record[TEST_RECORD_ID],
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
			]
		]);

		$assert([TEST_TEXT_FIELD], [[TEST_TEXT_FIELD => $record[TEST_TEXT_FIELD]]]);
	}

	function testQueryData_basicSupportedFieldTypes(){
		$getRandomFieldValues = function(){
			$value = rand();
			return [$value, $value+1];
		};

		$fields = [
			TEST_TEXT_FIELD => $getRandomFieldValues(),
			TEST_RADIO_FIELD => [1,2,3],
			TEST_YESNO_FIELD => [0,1],
			TEST_CALC_FIELD => $getRandomFieldValues(),
		];

		foreach($fields as $field=>$values){
			$this->assertTrue(shuffle($values));

			$recordId = rand();
			$expected = [];
			foreach($values as $value){
				$valueRecordId = (string) ($recordId++);
				$expected[] = [
					TEST_RECORD_ID => $valueRecordId,
					'redcap_repeat_instrument' => '',
					'redcap_repeat_instance' => '',
					$field => (string) $value
				];

				if($field === TEST_CALC_FIELD){
					// Manually insert because saveData() doesn't support calculated fields.
					// The saveData() call will ignore calculated field values if they match.
					$this->query("insert into redcap_data values(?, ?, ?, ?, ?, ?)", [
						TEST_SETTING_PID,
						$this->getEventId(TEST_SETTING_PID),
						$valueRecordId,
						$field,
						$value,
						null
					]);
				}
			}

			$this->saveData($expected);

			foreach($expected as $record){
				$sql = 'select ' . TEST_RECORD_ID . ', ' . $field . ' where ' . $field . ' = ?';
				$this->assertQueryData([$record], $sql, $record[$field]);
			}
		}
	}

	function testQueryData_contains(){
		$expectedValue = (string) rand();
		
		// Save a dummy record for the assertions to return
		\REDCap::saveData(TEST_SETTING_PID, 'json', json_encode([[
			TEST_RECORD_ID => rand(),
			'redcap_repeat_instrument' => '',
			'redcap_repeat_instance' => '',
			TEST_TEXT_FIELD => $expectedValue
		]]));

		$assert = function($sql, $expectedResult) use ($expectedValue){
			$columnName = 'some_column';
			$sql = "select 1 where $sql";

			$expected = [];

			if($expectedResult){
				$expected = [
					[
						1 => '1'
					]
				];
			}

			$this->assertQueryData($expected, $sql);
		};

		$assert("contains('abc', 'b')", true);
		$assert("contains('abc', 'z')", false);

		// Make sure "not'ed" contains() calls work as expected.
		$assert("!contains('abc', 'z')", true);
	}

	function testQueryData_orderBy(){
		$saveData = [
			[
				TEST_RECORD_ID => 1,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => 2
			],
			[
				TEST_RECORD_ID => 2,
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => 1
			]
		];

		$this->saveData($saveData);

		$expected = [
			[
				TEST_TEXT_FIELD => '2'
			],
			[
				TEST_TEXT_FIELD => '1'
			]
		];

		$sql = 'select ' . TEST_TEXT_FIELD;
		$this->assertQueryData($expected, $sql);

		$expected = array_reverse($expected);
		$this->assertQueryData($expected, $sql .' order by ' . TEST_TEXT_FIELD);
	}

	function testQueryData_exceptions(){
		$project = $this->getProject(TEST_SETTING_PID);

		$assert = function($selectFields, $whereClause, $expectedException) use ($project){
			$this->assertThrowsException(function() use ($selectFields, $whereClause, $project){
				$sql = 'select ' . implode(',', $selectFields) . ' where ' . $whereClause;
				$project->queryData($sql, []);
			}, $expectedException);
		};

		$assert([TEST_SQL_FIELD], '', ExternalModules::tt('em_errors_142', 'sql', TEST_SQL_FIELD));
		$assert([TEST_REPEATING_FIELD_1, TEST_REPEATING_FORM_2_FIELD_1], '1 = 2', ExternalModules::tt('em_errors_143'));
		$assert([], '1 = 2', ExternalModules::tt('em_errors_151'));

		$this->assertThrowsException(function() use ($project){
			$project->queryData('where 1 = 2', []);
		}, ExternalModules::tt('em_errors_151'));
	}

	function testQueryData_disallowedSQL(){
		$assert = function($sql, $expectedException){
			$this->assertThrowsException(function() use ($sql){
				$this->getProject(TEST_SETTING_PID)->queryData($sql, []);
			}, $expectedException);
		};


		$assert("select foo from foo", ExternalModules::tt('em_errors_145', 'FROM'));
		$assert("select foo limit foo", ExternalModules::tt('em_errors_146', 'foo'));
		$assert("select 1 where foo in (select 1)", ExternalModules::tt('em_errors_152', 'subquery', '(select 1)'));

		// Make sure subtrees are checked as well.
		$assert("select 1 where if(foo in (select 1), 1, 2)", ExternalModules::tt('em_errors_152', 'subquery', '(select 1)'));
	}
	
	function testQueryData_multipleCallContexts(){
		$this->saveData([
			[
				TEST_RECORD_ID => '1',
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => TEST_SETTING_PID
			]
		], TEST_SETTING_PID);

		$this->saveData([
			[
				TEST_RECORD_ID => '1',
				'redcap_repeat_instrument' => '',
				'redcap_repeat_instance' => '',
				TEST_TEXT_FIELD => TEST_SETTING_PID_2
			]
		], TEST_SETTING_PID_2);

		$assert = function($pid){
			$project = $this->getProject($pid);
			$result = $project->queryData('select [' . TEST_TEXT_FIELD . ']', []);
			$row = $result->fetch_assoc();
			$this->assertSame($row, [
				TEST_TEXT_FIELD => $pid
			]);

			$this->assertNull($result->fetch_assoc());
		};

		$assert(TEST_SETTING_PID);

		// Make sure the value from the second project is returned, event thought the get param is set to the first project.
		$_GET['pid'] = TEST_SETTING_PID;
		$_GET['event_id'] = $this->getEventIds(TEST_SETTING_PID_2)[0];
		$assert(TEST_SETTING_PID_2);
	}

	function testSetDAG(){
		$_GET['pid'] = TEST_SETTING_PID;
		$p = new \Project(TEST_SETTING_PID);
		
		$recordId = rand();
		$groupId = @array_keys($p->getGroups())[0];
		if($groupId === null){
			$groupId = $this->createDAG('Test DAG');
		}

		// Make sure the record exists.
		$this->saveData([[
			TEST_RECORD_ID => $recordId
		]]);

		$this->assertSame(null, $this->getDAG($recordId));
		$this->setDAG($recordId, $groupId);
		$this->assertSame((string)$groupId, $this->getDAG($recordId));
	}

	function testGetUser(){
		$this->assertThrowsException(function(){
			$this->getUser();
		}, ExternalModules::tt('em_errors_71'));

		$username = $this->getRandomUsername();
		$user = $this->getUser($username);
		$this->assertSame($username, $user->getUsername());

		ExternalModules::setUsername($username);
		$user = $this->getUser();
		$this->assertSame($username, $user->getUsername());
	}
	
	function testGetSurveyResponses(){
		$pid = (int) TEST_SETTING_PID;
		$event = $this->getEventId($pid);
		$recordId = '1';
		$instance = 1;

		$responses = $this->getSurveyResponses([
			'pid' => $pid,
			'event' => $this->getFirstEventId($pid),
			'form' => TEST_FORM,
			'record' => $recordId,
			'instance' => $instance,
		]);

		$row = $responses->fetch_assoc();
		$this->assertSame($pid, $row['project_id']);
		$this->assertSame($event, $row['event_id']);
		$this->assertSame(TEST_FORM, $row['form_name']);
		$this->assertSame($recordId, $row['record']);
		$this->assertSame($instance, $row['instance']);

		$this->assertNull($responses->fetch_assoc());

		$_GET['pid'] = (string) $pid;
		$responses = $this->getSurveyResponses([]);
		$row2 = $responses->fetch_assoc();

		/**
		 * Unit tests currently only add a single survey response row,
		 * so the same data should be returned.
		 */
		$this->assertSame($row, $row2);
	}
}