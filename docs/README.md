About
======================

This PHP library allows you to easily integrate dropbox with PHP.

The following PHP extension is required:

* json

The library makes use of OAuth. At the moment you can use either of these libraries:

* [PHP OAuth extension](http://pecl.php.net/package/oauth)
* [PEAR’s HTTP_OAUTH package](http://pear.php.net/package/http_oauth)

The extension is recommended, but if you can’t install php extensions you should go for the pear package.

## Installing ##

    pear channel-discover pear.dropbox-php.com
    pear install dropbox-php/Dropbox-alpha

## Documentation ##

* Check out the [documentation](http://dropbox-php.github.io/dropbox-php/docs).
* Have a look at the [unit test](https://github.com/Dropbox-PHP/dropbox-php/tree/master/tests).

## Questions? ##

* [Dropbox-php Mailing list](http://groups.google.com/group/dropbox-php)
* [Official Dropbox developer forum](http://forums.dropbox.com/forum.php?id=5)

## License ##
Copyright (c) 2010 Rooftop Solutions

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
