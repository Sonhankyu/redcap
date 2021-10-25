<?php
namespace ExternalModules;

abstract class ProjectChild
{
    function __construct($project, $name){
        $this->project = $project;
        $this->name = $name;
    }

    function getProject(){
        return $this->project;
    }

    function getName(){
        return $this->name;
    }
}