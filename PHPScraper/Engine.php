<?php


namespace PHPScraper;

use Curl\Curl;

class Engine extends Curl {


    const METHOD_GET    = 'get';
    const METHOD_POST   = 'post';
    const METHOD_PUT    = 'put';
    const METHOD_PATCH  = 'patch';
    const METHOD_DELETE = 'delete';


    public $baseURL = '';
    protected $config, $proxies = [];


    /**
     * Creates a new PHPScraper Engine.
     * @param array $config Add configurations during initialization.
     */
    public function __construct( $config = [] ){

        parent::__construct();

        $this->config = [
            'options' => [
                CURLOPT_AUTOREFERER => true,
                CURLOPT_FOLLOWLOCATION => true
            ]
        ];

        $this->configure( $config );
    }


    public function configure( $config = [] ){

        $this->config = array_replace_recursive( $this->config, $config );

        // === Forced Options: These options couldn't be changed.
        $this->config['options'][CURLOPT_RETURNTRANSFER] = true;
        $this->config['options'][CURLOPT_HEADER] = true;
        $this->config['options'][CURLINFO_HEADER_OUT] = true;

        if( isset( $this->config['options'] ) ){
            foreach( $this->config['options'] as $option => $value ){
                $this->setOpt( $option, $value );
            }
        }
    }


    /**
     * Get the absolute url relative to $url parameter. Giving empty
     * value to $url will return last requested url.
     * @param string $url
     * @return bool
     */
    public function absoluteURL( $url = '' ){
        return Utils::rel2absURL( $url, $this->baseURL );
    }


    /**
     * Add a proxy
     * @param $proxyName
     * @param $ip
     * @param $port
     * @param string $user
     * @param string $pass
     * @param int $type
     */
    public function addProxy($proxyName, $ip, $port, $user = '', $pass = '', $type = CURLPROXY_HTTP ){

        if( empty( $proxyName ) ) $proxyName = "$ip:$port";

        $this->proxies[ $proxyName ] = [
            'ip' => $ip,
            'port' => $port,
            'user' => $user,
            'pass' => $pass,
            'type' => $type
        ];

    }


    /**
     * Clone is a re-initialization of Engine with same configuration.
     * This helps in re creating internal variables rather than pointing
     * to same object variables.
     * @return Engine
     */
    public function __clone(){
        return new self( $this->config );
    }

    /**
     * @param $method
     * @param string $url
     * @param null|array $data
     * @param null|callable $callback
     * @param array $settings
     * @return Engine
     * @throws \Exception
     */

    public function request( $method, $url, $data = NULL, $callback = NULL, $settings = [] ){

        if( empty( $this->baseURL ) ) $this->baseURL = $url;

        $method = strtolower( $method );

        if( !in_array( $method, [
            self::METHOD_GET,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_PATCH,
            self::METHOD_DELETE,
        ] ) ){
            throw new \Exception('Invalid Request Method');
        }

        $absoluteURL = $this->absoluteURL( $url );


        // Debug Settings ===========================
        if( isset( $settings['debug'] ) AND $settings['debug'] === true ){
            echo "\n\n======= DEBUG ======\n";
            echo 'Request: ' . $absoluteURL . "\n";
            echo 'Method: ' . $method . "\n";
            echo 'Data: ' . "\n";
            var_dump( $data );
            echo "\n======= END ======\n\n";
        }

        // Cookies Settings ===========================
        if( isset( $settings['cookies'] ) ){
            foreach( $settings['cookies'] as $key => $val ){
                $this->setCookie( $key, $val );
            }
        }


        // Headers Settings ===========================
        if( isset( $settings['headers'] ) ){
            foreach( $settings['headers'] as $key => $val ){
                $this->setHeader( $key, $val );
            }
        }

        parent::$method($absoluteURL, (array) $data);


        if(is_callable( $callback )){

            // Creates a new sub-engine for new ExtendedDOM
            $newEngine = clone($this);
            $newEngine->baseURL = $absoluteURL;

            $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
            $body = substr( $this->response, $headerSize );

            // if dom is false, it will not generate ExtendedDOM element for response.
            if( !isset( $settings['dom'] ) OR $settings['dom'] == true ){
                $body = new ExtendedDOM($this->response, $newEngine);
            }

            call_user_func( $callback, $this->response_headers, $body, $this->request_headers );
        }
        return $this;
    }


    /**
     * Do a GET Request.
     * @param string $url
     * @param null|array $data Send GET parameters
     * @param null|callable $callback This callback will be invoked once the request is complete
     * @param array $settings Add some additional configuration during this request.
     * @return Engine
     */
    public function get($url, $data = NULL, $callback = NULL, $settings = []){
        return $this->request( self::METHOD_GET, $url, $data, $callback, $settings );
    }

    /**
     * Do a POST Request
     * @param string $url
     * @param null|array $data Send POST parameters
     * @param null|callable $callback This callback will be invoked once the request is complete
     * @param array $settings Add some additional configuration during this request.
     * @return Engine
     */
    public function post($url, $data = NULL, $callback = NULL, $settings = []){
        return $this->request( self::METHOD_POST, $url, $data, $callback, $settings );
    }

    /**
     * Do a PUT request
     * @param string $url
     * @param null|array $data Send PUT parameters
     * @param null|callable $callback This callback will be invoked once the request is complete
     * @param array $settings Add some additional configuration during this request.
     * @return Engine
     */
    public function put($url, $data = NULL, $callback = NULL, $settings = []){
        return $this->request( self::METHOD_PUT, $url, $data, $callback, $settings );
    }

    /**
     * Do a PATCH request
     * @param string $url
     * @param null|array $data Send PATCH parameters
     * @param null|callable $callback This callback will be invoked once the request is complete
     * @param array $settings Add some additional configuration during this request.
     * @return Engine
     */
    public function patch($url, $data = NULL, $callback = NULL, $settings = []){
        return $this->request( self::METHOD_PATCH, $url, $data, $callback, $settings );
    }

    /**
     * Do a DELETE request
     * @param string $url
     * @param null|array $data Send DELETE parameters
     * @param null|callable $callback This callback will be invoked once the request is complete
     * @param array $settings Add some additional configuration during this request.
     * @return Engine
     */
    public function delete($url, $data = NULL, $callback = NULL, $settings = []){
        return $this->request( self::METHOD_DELETE, $url, $data, $callback, $settings );
    }


}