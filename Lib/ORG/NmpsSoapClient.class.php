<?php
class NmpsSoapClient
{
    public $client = null;
    
    function __construct()
    { 
        $this->client = new SoapClient(C('SOAP_URL'));
    }
    
    function __call($method, $var)
    {
        $return = $this->client->__soapCall($method, $var);
        $data = $return->result;
        
        if(count($data) == 1){
            $tmpData[0] = $data;
            $data = $tmpData;
        }
        return $data;
    }
    
    function getClient()
    {
		return $this->client;
    }
}
?>