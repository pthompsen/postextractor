# postextractor
This PHP application uses a Valence API call to extract all of the posts from a D2L discussion forum topic to a JSON array, and javascript to convert the JSON array to a CSV file. 

Much of the PHP code is based on the "Getting Started Sample" of the Valence PHP SDK (v. 1.6.0), which is Copyright (c) 2012 Desire2Learn Inc. and licensed under the Apache License Version 2.0.  It requires the included lib folder from that SDK.  Because I've essentially revised the Getting Started Sample, some of the code is superfluous to this application;  I'll try to clean it up eventually.  The javascript code that converts the JSON array to CSV format is based on the code at http://jsfiddle.net/sturtevant/vUnF9/. 

To use this application, enter the configuration variables in config.php.  This includes your Valence AppId and Key pair, which you can obtain at the Valence KeyTool at http://keytool.valence.desire2learn.com.  Other configuration variables that should be set include the path to the lib folder (included in the Valence PHP SDK mentioned above), the url of the D2L instance your app is accessing, the scheme (http or https), and the port (most likely 443 if https or 80 if http).  You may optionally include default values for the OrgUnitID and the ForumID, which is useful if only one person is using this for a single OU and/or a single forum.  

After you have entered in the appropriate configuration variables and saved config.php, upload these files (and the lib folder) to a web server.  Then access index.php from a web browser, and enter in the values as needed on the web form.

I would like to thank Jacob Parker at D2L who helped me improve my original code at the Extensibility Lab at the 2014 D2L Fusion conference in Nashville.

Good luck, and let me know if you find this useful.

Philip A. Thompsen, Ph.D.
pthompsen@wcupa.edu 

P.S. For some reason, I've had better results with Chrome than with Safari when downloading CSV files.