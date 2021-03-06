<?php

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DisallowedFunctionSniff implements Sniff
{
    const EXPECTED_REFERENCES = [
        'db_query' => 1, // All other calls should use ExternalModules::query() or $module->query() to encourage parameter use
        'EDOC_PATH' => 1, // All other calls should use ExternalModules::getEdocPath() to ensure that getSafePath() is used.
        'USERID' => 1, // All other calls should use ExternalModules::getUsername() to ensure impersonation is used when appropriate.
        'error_log' => 1, // All other calls should use ExternalModules::errorLog() to ensure that long logs are chunked.
        'getModuleInstance' => 32, // There's a good chance new calls should be referencing the framework instance instead.
        'die' => 0, // Please call exit() instead for consistency.
        'exit' => 14, // Exit calls are unsafe within hooks in the framework.  Make sure any new exit() calls are appropriate before incrementing this.
    ];

    private $referenceCounts = [];
    private $errorsByFunction = [];
    
    function __construct(){
        $this->addErrors(
            [
                '_query',
                '_multi_query',
                '_multi_query_rc'
            ],
            'does not support query parameters.  Please use ExternalModules::query() or $module->query() instead.'
        );

        $this->addErrors(
            [
                '_fetch_row',
                '_fetch_assoc',
                '_fetch_array',
                '_free_result',
                '_fetch_field_direct',
                '_fetch_fields',
                '_num_fields',
                '_fetch_object',
                '_result',
                '_transaction_active',
            ],
            'will not work with our custom StatementResult object.  Please use object oriented syntax instead (ex: $result->some_method()).'
        );

        $this->addErrors(
            [
                '_affected_rows'
            ],
            'will not work with prepared statements.  Please see the External Module query documentation for an alternative.'
        );
    }

    private function addErrors($suffixes, $error){
        foreach(['db', 'mysql', 'mysqli'] as $prefix){
            foreach($suffixes as $suffix){
                $this->errorsByFunction[$prefix.$suffix] = $error;
            }
        }
    }

    function register()
    {
        return [T_STRING, T_EXIT];
    }

    function process(File $file, $position)
    {
        $string = $file->getTokens()[$position]['content'];

        $referenceLimit = @self::EXPECTED_REFERENCES[$string];
        if($referenceLimit !== null){
            @$this->referenceCounts[$string]++;
        }
        else{
            $error = @$this->errorsByFunction[$string];
            if($error){
                $file->addError("The '$string' function is not allowed since it $error", $position, self::class);
            }
        }
    }

    public function __destruct()
    {
        foreach(self::EXPECTED_REFERENCES as $name=>$limit){
            $count = @$this->referenceCounts[$name];
            if($count === null){
                $count = 0;
            }

            if($count !== $limit){
                throw new \Exception("Expected $limit reference(s) to the '$name' function/constant, but found $count.  Please review any recently added/removed references.  The counts at the top of this file should be updated only if the changes respect the comment for the line with each count.");
            }
        }
    }
}