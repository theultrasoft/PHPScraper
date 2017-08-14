# PHPScraper
PHPScraper is a **jQuery** like object oriented php scraper and web automation library.

If you are familiar with jQuery syntax, it will be a breeze for you to request a page, click on a link, process selected elements together and so on. If you have questions or problems with installation or usage [create an Issue](https://github.com/TheUltrasoft/PHPScraper/issues).


## Installation:
Currently PHPScraper in in _beta_. If you are using composer, you must set minimum-stability to `dev`. to get latest version.
It is still not ready to use in production.

If you have composer installed run command:
```
composer require phpscraper/phpscraper
```
    
Or if you prefer to add it manually using `composer.json` add this:
```
"phpscraper/phpscraper": "*"
```
    
This will install the latest version (currently: v0.1.5-beta) of PHPScraper into your project.

## Usage examples:
```php
$engine = new \PHPScraper\Engine();
$engine->get('https://www.example.com/', NULL, function( $headers, $body ) {
    $body->find('a.some-link')->click(function ($headers, $body) {
        echo $body;
        // Do whatever you want to do
    });
});
```

## Contribution
If you want to contribute, feel free to fork, update and ask for pull request. Thank you.

## Licence:
The source code is licensed under [GPLv3](https://www.gnu.org/licenses/gpl-3.0.en.html).