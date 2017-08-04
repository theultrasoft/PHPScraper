<?php

require_once '../vendor/autoload.php';
require_once '../src/Utils.php';
require_once '../src/ExtendedDOM.php';
require_once '../src/Engine.php';


$engine = new \PHPScraper\Engine();
$engine->get('https://www.yellowpages.in/', NULL, function( $headers, $body ) {
    $body->find('a', 1)->click(function ($headers, $body) {
        $body->find('form')->submit([], function( $headers, $body, $request ){
            var_dump( $request, $headers );
//            echo $body->find('.pageTitle')->plaintext . "\n";

        });
    });
});


/**
 *
 *
 * Classes and their structure ====
 *
 * Engine:{
 *   get
 *   post
 *   put
 *   delete
 *   ...
 *     params: {
 *       length 1: <url>
 *       length 2: <url>, <callback>
 *       length 3: <url>, <data>, <callback>
 *     }
 * }
 *
 *
 *
 *  ExtendedDOM: extends simple_html_dom / simple_html_dom_node{
 *    find
 *    filter
 *    ...
 *
 *    click(<callback(ExtendedDOM)>): works in a loop of single/multiple links only
 *    submit(<data>, <callback(ExtendedDOM)>) works only if last selection has form(s)
 *  }
 *
 *
 */