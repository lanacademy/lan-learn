LAN-LMS
=======
LAN-LMS is an open source learning platform developed by LAN Academy Inc.

The eventual goals for this software are:
 - It can import and serve up existing OCW and other open educational resources
 - It is designed to be deployed in the developing world (works "offline" over LAN, etc)
 - It has an intuitive user experience that makes learning easy


Current Features
--------
- Flat file model (XML database + Markdown content)
- Extensible and customizable via plugins, twig templating, and arbitrary metadata
- 'Offline' functionality; works without a connection to the internet
- + more

Requirements
------------
Apache web server with PHP (Tested/Developed on Apache 2.4 + PHP 5.5)
- mod_rewrite enabled

Installing
----------
- Copy into web directory, make a copy of backup.'config.php' and name it 'config.php'
- Read/edit 'config.php' values
- You're done!

Advanced
--------
- TBA

Browser Support
---------------
Tested on modern Chrome


Supporting Projects
-------------------
LAN-LMS is based upon several open source pieces
- https://github.com/gilbitron/Pico
- https://github.com/euyuil/php-quiz-generator
- https://github.com/flowplayer/flowplayer
- https://github.com/ajaxorg/ace
- https://github.com/jquery/jquery
- https://github.com/Modernizr/Modernizr


License
-------
`LAN-LMS` is licensed under the MIT license.

Copyright (C) 2014 LAN Academy
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
