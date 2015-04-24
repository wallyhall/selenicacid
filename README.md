# selenicacid  [![Build Status](https://travis-ci.org/wallyhall/selenicacid.svg?branch=master)](https://travis-ci.org/wallyhall/selenicacid)  

Making Selenium even more awesome.

selenicacid is a tiny backend service intended to provide automated web testing tools (like Selenium) even better, by providing a web-based interface to your application and infrastructure's hidden internals.

One of the main issues we've experienced with tools like Selenium is automatically triggering cronjobs, asserting processes are running, rotating server log files, triggering DB events ad-hoc, etc.

selenicacid brings makes these tasks available to your web orientated testing tool and is designed in a way which makes extending it for your own private needs both admissable (under the MIT license) and easy (through user orientated design).
