<?php

namespace BlaubandEmail\Services;

class ConfigService
{
    private $data;

    /**
     * ConfigService constructor.
     * @param $path
     */
    public function __construct($path)
    {
        if(strpos($path, '/config.php') !== false){
            $this->data = include $path;
        }else{
            $configData = file_get_contents($path);
            $this->data = json_decode(json_encode((array)simplexml_load_string($configData)),1);
        }
    }

    /**
     * @param $string
     * @param bool $asArray
     * @return array|mixed|null
     */
    public function get($string, $asArray = false){
        try{
            $result = $this->data;
            $steps = explode('.', $string);
            foreach ($steps as $step){
                $result = $result[$step];
            }

            if($asArray && !empty($result) && !isset($result[0])){
                $result = [$result];
            }

            return $result;
        }catch (\Exception $e){
            return null;
        }
    }
}