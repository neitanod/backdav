backdav
=======

Light and simple WebDAV backdoor to your PHP server

Goals
-----

It would be nice to see this single tiny file become a clean subset 
of the WebDAV standard, so I hope somebody wants to collaborate, 
but meanwhile, it allows PHP developers to mount their servers as 
network drives (without pretending to be clean in any way).

It's already functional, but bear in mind that I coded this in a 
few hours, and it still does not implement any kind of security,
and if you really upload this to your server you will be introducing
a vulnerability, so use it at your own risk.

Usage
-----

1. Upload the file index.php into a password protected folder in your 
PHP server.
2. Create a network drive in your computer pointing to: 
   http://youruser:yourpass@yourserver/path/index.php/
3. Open and browse your network drive.
