<?php
class Export_xml
{
	public function __construct()
	{
		
	}
	
	public function array_to_xml($arr, &$return) 
	{
	    foreach($arr as $key => $value) 
	    {
	        if(is_array($value)) 
	        {
	            if(!is_numeric($key))
	            {
	                $subnode = $return->addChild("$key");
	                $this->array_to_xml($value, $subnode);
	            }
	            else 
	            {
	                $this->array_to_xml($value, $return);
	            }
	        }
	        else 
	        {
	            $return->addChild("$key","$value");
	        }
	    }
	}	
	
}