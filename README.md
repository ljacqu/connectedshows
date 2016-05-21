Connected TV Shows
==================

PHP 5 application which retrieves TV show data from IMDb.com and creates a graph,
connecting the TV shows which have common actors.


Connections
-----------

You can set criteria to define whether an actor should act as a connection
between two TV shows, e.g. by setting a minimum threshold of episodes the actor
has had to play in. Otherwise, actors with trivial one-episode background roles 
pretty much connect every show with _all_ the others, creating a huge, unusable
graph.


Requirements
------------

- PHP 5
- SQL database engine (e.g. MySQL)
- [GraphViz](http://www.graphviz.org/Download..php) <sup>[1]</sup>

[1] Or any similar graph framework that can be run from command line.


Usage Warnings
--------------
1. Meant to run locally; it is (probably) not safe to be run on a public webserver
   without some form of password protection as user input not properly sanitized
   everwhere
2. Only supports TV shows (movies cannot be processed)
