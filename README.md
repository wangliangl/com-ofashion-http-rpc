# com-ofashion
    This is a private warehouse service, feel free to use, problems need to be resolved
    
    
## Test code

```php
    $ser = array(
            'baseUri'         => 'http://domain.com.',
            'route'           => '/foo/bar',
            'format'          => 'json',     //option   def : json
            'timeout'         => 1,         //option    def : 5
            'connect_timeout' => 5,         //option    def : 5
            'debug'           => true,      //option    def : false
            'headers'         => array()    //option
        
        );
        
        $input = array('no_check' => 1);    //option  def : array  
        
        $options = array(
            'httpMethod' => 'get',          //option   def : post
        //    'requestId'  => 123,           //option    def : rand
        );
        
        $a = new \Ofashion\Http\OfashionHttp(array('userId' => 12312)); //option userId def ""
        $res = $a->call($ser, $input, $options);
        print_r($res);
```