<?php
spl_autoload_register(
    function($class){
        static $prefixes = NULL;
        static $root = NULL;
        
        if (is_null($root)){
            $root = realpath(__DIR__);
        }
        
        if(is_null($prefixes)){
            $prefixes = array('Tiny\\DbUnit\\UnitTests\\' => $root.DIRECTORY_SEPARATOR,
                              'Tiny\\DbUnit\\' => realpath($root.'/../src/').DIRECTORY_SEPARATOR,
                              );
        }
        
        foreach($prefixes as $prefix => $baseDir){
            $prefix_length = strlen($prefix);
            if (strncmp($prefix, $class, $prefix_length) === 0){
                $remainder = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $prefix_length));
                $fullPath = $baseDir.$remainder.".php";
                if (file_exists($fullPath)){
                    require_once($fullPath);
                    return true;
                }
                else{
                    return false;
                }
            }
        }
    }
);
?>