<?php


namespace PHPScraper;

class ExtendedDOM {

    /**
     * @var \simple_html_dom_node $dom
     * @var \PHPScraper\PHPScraperEngine $engine
     */
    protected $dom;
    public $engine;

    /**
     * ExtendedDOM constructor.
     * @param $dom
     * @param null|PHPScraperEngine $engine
     */
    public function __construct($dom, &$engine = NULL ){

        $domArray = [];
        if( is_array( $dom ) ){
            foreach( $dom as $item ){
                if( $item ){
                    $domArray[] = $this->parseDom( $item );
                }
            }

        }else{
            $domArray = [ $this->parseDom( $dom ) ];
        }

        $this->dom = $domArray;

        if( !$engine ){
            $this->engine = new PHPScraperEngine();
        }
        else $this->engine = $engine;

    }

    protected function parseDom( $dom ){

        if( is_string( $dom ) ){
            $dom = new \simple_html_dom( $dom );
            return $this->parseDom( $dom );
        }else if( is_a( $dom, 'simple_html_dom' ) ){
            return $dom->root;
        }else if( is_a( $dom, 'simple_html_dom_node' ) ){
            return $dom;
        }
        return NULL;
    }

    public function __get($name){
        return $this->simpleDom()->{$name};
    }

    public function __set($name, $value){
        return $this->simpleDom()->{$name} = $value;
    }

    /**
     * Returns the first occurrence of dom element or all if
     * $array is set to true
     * @param bool $array
     * @return mixed
     */
    public function simpleDom($array = false ){
        if( $array ){
            return $this->dom;
        }

        return count( $this->dom ) ? $this->dom[0] : NULL;
    }

    /**
     * Process through each elements that are currently selected
     * @param $callback
     * @return $this
     */
    public function each( $callback ){
        foreach( $this->dom as $index => $element ){
            call_user_func( $callback, $index, $element );
        }

        return $this;
    }


    /**
     * Converts DOM object into ExtendedDOM object or
     * array of DOM objects into array of ExtendedDOM objects
     * @param $dom
     * @return null|ExtendedDOM
     */
    public function convert( &$dom ){
        if( is_array( $dom ) ){
            foreach( $dom as &$element ){
                $element = $this->convert( $element );
            }
        }else if( is_a( $dom, 'simple_html_dom_node' )){
            return new self( $dom, $this->engine );
        }else if( is_a( $dom, self::class )){
            return $dom;
        }

        return NULL;
    }

    public function length(){
        return count( $this->dom );
    }

    public function find( $selector, $index = false ){
        $elements = [];
        $this->each( function( $i, $el ) use ($selector, &$elements){
            $elements = array_merge( $elements, $el->find($selector) );
        } );

        if( $index !== false ) return new self( $elements[ $index ], $this->engine );

        return new self( $elements, $this->engine );

    }

    public function eq( $index ){
        // TODO: Get element by index
    }

    public function filter($selector, $index = false){
        $this->each( function( $i, $el ){
            /** @var \simple_html_dom_node $el */
            // TODO: Filter by selector from currently selected elements
        } );
    }

    public function click($callback = NULL){

        $this->each(function ($i, $el) use ($callback){
            if( 'a' == $el->tag ){
                if( !empty( $el->href ) ){
                    $this->engine->get($el->href, NULL, $callback);
                }
            }
        });

        return $this;
    }


    public function fields(){

        $inputs = [];
        $this->find('input')->each(function($i, $input) use ( &$inputs ) {
            if( !empty( $input->name ) ){
                $inputs[ $input->name ] = $input->value;
            }
        });

        return $inputs;
    }


    /**
     * @param array $data
     * @param null|callable $callback
     * @return $this
     */
    public function submit( $data = [], $callback = NULL ){

        $data = array_merge( $this->fields(), $data );

        $this->each( function ( $i, $el ) use( $data, $callback ) {
            if( 'form' == $el->tag ){
                $method = $el->method ? $el->method : PHPScraperEngine::METHOD_GET;
                $url    = $el->action ? $el->action : '';
                $this->engine->request( $method, $url, $data, $callback);
            }
        } );

        return $this;
    }

}