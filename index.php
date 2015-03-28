<?php
/**
 * Post Extractor (v. 1.0)
 * Philip A. Thompsen, Ph.D.
 * pthompsen@wcupa.edu
 *
 * This application extracts all posts from a D2L discussion topic
 * Make sure to enter your specific values in the config.php file
 * 
 * This code is based in part on the "Getting Started Sample" of the 
 * Valence PHP SDK (v. 1.6.0)
 * Copyright (c) 2012 Desire2Learn Inc.
 * 
 */

require_once 'config.php';
require_once $config['libpath'] . '/D2LAppContextFactory.php';

session_start();

// read values from the config.php file

$appId = $config['appId'];
$appKey = $config['appKey'];
$host = $config['host'];
$port = $config['port'];
$scheme = $config['scheme'];
$ouId = $config['ouId'];
$forumId = $config['forumId'];

$authContextFactory = new D2LAppContextFactory();
$authContext = $authContextFactory->createSecurityContext($appId, $appKey);
$hostSpec = new D2LHostSpec($host, $port, $scheme);
$opContext = $authContext->createUserContextFromHostSpec($hostSpec, null, null, $_SERVER["REQUEST_URI"]);

if ($opContext!=null) {
    $userId = $opContext->getUserId();
    $userKey = $opContext->getUserKey();
    $_SESSION['userId'] = $userId;
    $_SESSION['userKey'] = $userKey;
} elseif (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];
    if (isset($_SESSION['userKey'])) {
        $userKey = $_SESSION['userKey'];
    } else {
        $userKey = '';
    }
} else {
    $userId = '';
    $userKey = '';
}


session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Post Extractor</title>
    <style type = "text/css">
        table.plain
        {
          border-color: transparent;
          border-collapse: collapse;
        }

        table td.plain
        {
          padding: 5px;
          border-color: transparent;
        }

        table th.plain
        {
          padding: 6px 5px;
          text-align: left;
          border-color: transparent;
        }

        tr:hover
        {
            background-color: transparent !important;
        }

        .error
        {
            color: #FF0000;
        }
    </style>
    <script src="sample.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type = "text/javascript"></script>
</head>
<body>
<h1>Post Extractor</h1>
<p>This application extracts all of the posts from a forum topic.  Enter the OrgUnit ID, Forum ID and Topic ID into the spaces provided and click the "Submit" button. (For convenience, the OrgUnit and Forum IDs can be entered with default values from config.php.) The posts will be displayed in the form of a JSON array, which can then be downloaded as a CSV file.</p>

    <span id="errorField1" class="error" hidden="true">Error: </span><span id="errorField2"></span>
    <form method="get" action="authenticateUser.php" id="configForm">
    <input type="submit" name="authBtn" value="Reaunthenticate" id="resetButton" hidden=true />
    <hr />
   
    <div id="userDiv">
        <br />
        
        <table>
            <tr>
	            <td>
                    <input type="text" name="userIDField" id="userIDField" style="width:20em" value="<?php echo $userId; ?>" hidden=true />
                </td>
            
                <td>
                    <input type="text" name="userKeyField" id="userKeyField" style="width:20em" value="<?php echo $userKey; ?>" hidden=true />
                </td>
            </tr>
        </table>
If you wish to deauthenticate this application, click the Deauthenticate button.<br />
        <input type="submit" name="authBtn" value="Deauthenticate" id="deauthBtn">
    </div>
    <span id="authNotice">Note: You need to authenticate this application.  Click the Authenticate button and log in with your D2L username and password.
    </span><br />
    <input type="submit" name="authBtn" value="Authenticate" id="authenticateBtn" /><br>
    <input type="button" id="manualBtn" value="Manually set credentials" onclick="setCredentials()" hidden=true />
    <input type="submit" name="authBtn" value="Save" id="manualAuthBtn" hidden=true />
    </form>

    <hr />
  
    <br />
    <b>Org Unit ID:</b>&nbsp;<input name="orgUnitID" id="orgUnitID" style="width=200px;" value="<?php echo $ouId; ?>" /><br />
    <b>Forum ID:</b>&nbsp;<input name="forumID" id="forumID" style="width=200px;" value="<?php echo $forumId; ?>" /><br />
    <b>Topic ID:</b>&nbsp;<input name="topicID" id="topicID" style="width=200px;"/><br />
    <input type="button" name="submitButton" value="Submit" id="populateValues" onclick="populateValues()"/>
	
    <input value="GET" name="method" type="radio" id="GETField" checked="checked" onclick="hideData()" hidden=true />
    <input value="POST" name="method" type="radio" id="POSTField" onclick="showData()" hidden=true />
    <input value="PUT" name="method" type="radio" id="PUTField" onclick="showData()" hidden=true />
    <input value="DELETE" name="method" type="radio" id="DELETEField" onclick="hideData()" hidden=true />
    <input name="actionField" type="text" id="actionField" style="width:600px;"  hidden=true /><br />
    <b id="dataFieldLabel"></b>
    <textarea name="dataField" rows="2" cols="20" id="dataField" style="height:400px;width:600px;" >
	</textarea>
	<b id="workingLabel" hidden=false>Working...</b><br />
    <b id="responseFieldLabel" hidden=true>JSON array of posts below.  </b><input type="button" name="download" value="Download as CSV" id="download" onclick="download()" hidden=true /><br />
    <textarea name="responseField" hidden=true rows="2" cols="20" id="responseField" style="height:400px;width:600px;">
	</textarea><br />
    <input type="button" name="submitButton" value="Submit" id="submitButton" onclick="doAPIRequest()" hidden=true />

</body>
<script type="text/javascript">
    function showData() {
        document.getElementById("dataFieldLabel").hidden = false;
        document.getElementById("dataField").hidden=false;
    }

    function hideData() {
        document.getElementById("dataFieldLabel").hidden = true;
        document.getElementById("dataField").hidden=true;
    }

    function populateValues() {
		document.getElementById("workingLabel").hidden = false;
        var orgUnitID = document.getElementById("orgUnitID").value;
        var forumID = document.getElementById("forumID").value;
        var topicID = document.getElementById("topicID").value;
        document.getElementById("actionField").value="/d2l/api/le/1.3/" + orgUnitID + "/discussions/forums/" + forumID + "/topics/" + topicID + "/posts/";
        doAPIRequest();
    }

function JSON2CSV(objArray) {
    var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;

    var str = '';
    var line = '';

    
        var head = array[0];
        
            for (var index in array[0]) {
                var value = index + "";
                line += '"' + value.replace(/"/g, '""') + '",';
            }
        

        line = line.slice(0, -1);
        str += line + '\r\n';
 

    for (var i = 0; i < array.length; i++) {
        var line = '';

       
            for (var index in array[i]) {
                var value = array[i][index] + "";
                line += '"' + value.replace(/"/g, '""') + '",';
            }
        
        

        line = line.slice(0, -1);
        str += line + '\r\n';
    }
    return str;
    
}
        
function fixJSON(json) {
	if (json == null) {
		return;
	}
	for (var i = 0; i < json.length; i++) {
		var date = new Date(json[i].DatePosted);
		json[i].DatePosted = date.toLocaleString();
		json[i].Message = JSON.stringify(json[i].Message.Html);
	}
}
    
$("#download").click(function() {
    var json = $.parseJSON($("#responseField").val());
	fixJSON(json);
    var csv = JSON2CSV(json);
    window.open("data:text/csv;charset=utf-8," + escape(csv));
});


    function setCredentials() {
        document.getElementById("manualAuthBtn").hidden = false;
        document.getElementById("deauthBtn").hidden = true;
        document.getElementById("userDiv").hidden = false;
        document.getElementById("userIDField").hidden = false;
        document.getElementById("userKeyField").hidden = false;
        document.getElementById("manualBtn").hidden = true;
        document.getElementById("authenticateBtn").hidden = true;
        document.getElementById("authNotice").hidden = true;
    }

    hideData();

    if(document.getElementById("userIDField").value != "") {
        document.getElementById("userIDField").disabled = true;
        document.getElementById("userKeyField").disabled = true;
        document.getElementById("manualBtn").hidden = true;
        document.getElementById("authenticateBtn").hidden = true;
        document.getElementById("authNotice").hidden = true;
    } else {
        document.getElementById("userIDField").hidden = true;
        document.getElementById("userKeyField").hidden = true;
        document.getElementById("userDiv").hidden = true;
    }

    $("body").ajaxError(function(e, request) {
        console.log("AJAX error!");
    });
</script>
</html>
