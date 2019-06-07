<h1>Home automation system</h1>
<h3>Distributed system w/ two ESP8266 modules and a Raspberry Pi</h3>

<h2>Installation instructions</h2>
<h3>Setting up Raspberry Pi</h3>
<p>First we need to setup environment at Raspberry Pi. Installed Raspbian Linux is a pre-requisite.</p>
<div>
<code>
<ul>
<li>$sudo apt-get upgrade</li>
<li>$sudo apt-get update</li>
<li>$sudo apt-get install apache2</li>
<li>$sudo apt-get install mysql-server</li>
<li>$sudo chown -R $YOUR_USERNAME$ /var/www/html</li>
</ul>
</code>
</div>
<p>After running these commands, deploy repository by cloning from Github to <code>/var/www/html</code>. Files need to be in this directory.
If git creates a subdirectory, move them to correct one.
Then we need to install <code>pip</code> and enable services for <code>php</code> workers.</p>

<div>
<code>
<ul>
<li>$sudo apt-get install php</li>
<li>$systemctl enable /var/www/html/ligtbulb1.service</li>
<li>$systemctl enable /var/www/html/door1.service</li>
<li>$systemctl enable /var/www/html/temperature1.service</li>
<li>$sudo apt-get install sendmail</li> ------------- this is optional if you want email alerts when temperature is high
</ul>
</code>
</div>
<p>Restart your operating system.
Test first ESP door algorithm mqtt flow by publishing a message to mqtt topic "door31071993" using some mqtt
client (e.g. chrome addon MQTT box).
Repeat for temperature flow topic "temperature31071993".
Access control panel by going to "localhost" on the Pi itself or by visiting Pi's IP
address from another device on the same local network. If you can see the changes 
in door and temperature display tables, all is good. If not, you need to troubleshoot
the problem.</p>

<h3>Setting up the database</h3>
<p>For this task you can use some DBMS client (e.g. phpmyadmin) or terminal.
Create user with username 'jovan' and password 'password' 
and grant him all privileges on all databases.
Create database "home" on mysql server and import the provided sql schema dump <code>home.sql</code> included in the repository.</p>

<p>This concludes installation for Raspberry Pi server.</p>

<h3>Configuring ESPs</h3>
<p>All you need to do is flash appropriate files to their respective ESP8266 devices and resolve their dependencies.
Dependencies are listed in each of the respective source files and can be obtained from SuperHouse repository (https://github.com/SuperHouse/esp-open-rtos).
You also need to modify global variables in each of the files with WiFi SSID and password that you plan to use.
When all dependencies have been resolved and changes made, you can flash files to their respective ESPs. This concludes
ESP configuration.
Makefile is provided as-is and is not guaranteed to work on your system. It might be required for you to configure makefile 
yourself in order to be able to flash ESPs properly.
As far as physical configuration, you need to connect light bulb switch to second ESP's GPIO 4. This is where toggle signal is
sent. First ESP should be placed on the door, with its top side facing <i>away</i> from door opening direction.</p>

<p>This concludes system configuration. If all was done as instructed, you should now have a fully functioning home automation system.</p>

ATTENTION: after deploying the system, give each of the ESPs one hard restart via hardware button.
This is needed for system to function as expected.
