Connected TV Shows
==================

PHP 5 application which retrieves TV show data from IMDb.com and creates a graph,
connecting the TV shows which have common actors.


Usage Warnings
--------------
1. Meant to run as a local script; it is not safe to put this on a webserver
   without some form of password protection (user input not properly sanitized
   everwhere)
2. Only supports TV shows (no movies or similar)
3. Status: Unfinished & not user-friendly _yet_


Requirements
------------

- PHP 5
- SQL database engine (e.g. MySQL, InnoDB)
- [GraphViz](http://www.graphviz.org/Download..php) <sup>[1]</sup>

[1] Or any similar graph framework that can be run from command line.


Connections
-----------

You can set criteria to define whether an actor should act as a connection
between two TV shows, e.g. by setting a minimum threshold of episodes the actor
has had to play in. Otherwise, actors with trivial one-episode background roles 
pretty much connect every show with _all_ the others, creating a huge, unusable
graph.


Todo
----

- Create shows.php page
- config base URL for SVG
- use config commands for different formats
- save config for specific graphs
- connections.php: put shows to top that have a connection with the selected ones
- custom linewidth/color functions? (+ predefined examples)
- export function? (for graphs & navigable pages)
- support movies & similar?
 + or: ensure that entering movies is detected & stop
- DB integrity tests