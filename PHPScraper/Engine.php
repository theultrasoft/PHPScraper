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

    public function __construct(){

        parent::__construct();

        $this->setOpt(CURLOPT_AUTOREFERER, true);
        $this->setOpt(CURLOPT_FOLLOWLOCATION, true);

    }

    public function absoluteURL( $url = '' ){
        return Utils::rel2absURL( $url, $this->baseURL );
    }

    /**
     * @param $method
     * @param string $url
     * @param null|array $data
     * @param null|callable $callback
     * @return Engine
     * @throws \Exception
     */

    public function request( $method, $url, $data = NULL, $callback = NULL ){

        if( empty( $this->baseURL ) ) $this->baseURL = $url;

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
        parent::$method($absoluteURL, (array) $data);

        if(is_callable( $callback )){

            $newEngine = clone($this);
            $newEngine->baseURL = $absoluteURL;

            $newEngine->error = false;
            $newEngine->error_code = 0;
            $newEngine->error_message = null;
            $newEngine->curl_error = false;
            $newEngine->curl_error_code = 0;
            $newEngine->curl_error_message = null;
            $newEngine->http_error = false;
            $newEngine->http_error_message = null;
            $newEngine->http_status_code = 0;
            $newEngine->request_headers = null;
            $newEngine->response_headers = null;
            $newEngine->response = null;

            $dom = new ExtendedDOM($this->response, $newEngine);
            call_user_func( $callback, $this->response_headers, $dom, $this->request_headers );
        }

        return $this;
    }


    /**
     * @param string $url
     * @param null|array $data
     * @param null|callable $callback
     * @return Engine
     */
    public function get($url, $data = NULL, $callback = NULL){
        return $this->request( self::METHOD_GET, $url, $data, $callback );
    }

    /**
     * @param string $url
     * @param null|array $data
     * @param null|callable $callback
     * @return Engine
     */
    public function post($url, $data = NULL, $callback = NULL){
        return $this->request( self::METHOD_GET, $url, $data, $callback );
    }

    /**
     * @param string $url
     * @param null|array $data
     * @param null|callable $callback
     * @return Engine
     */
    public function put($url, $data = NULL, $callback = NULL){
        return $this->request( self::METHOD_PUT, $url, $data, $callback );
    }

    /**
     * @param string $url
     * @param null|array $data
     * @param null|callable $callback
     * @return Engine
     */
    public function patch($url, $data = NULL, $callback = NULL){
        return $this->request( self::METHOD_PATCH, $url, $data, $callback );
    }

    /**
     * @param string $url
     * @param null|array $data
     * @param null|callable $callback
     * @return Engine
     */
    public function delete($url, $data = NULL, $callback = NULL){
        return $this->request( self::METHOD_DELETE, $url, $data, $callback );
    }


}