<!DOCTYPE HTML>
<head>
<meta charset="utf-8">
<title>Applications Monitor</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<!-- Le styles -->
<link href="/WebApps/bootstrap/css/bootstrap.css" rel="stylesheet">
<style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }

	td.late, td.logerror { background-color: #FC8B8B}
		
  	.sidebar-nav {
        padding: 9px 0;
      }
      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
</style>
<link href="/WebApps/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->
<!-- Fav and touch icons -->
<!-- <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
<link rel="shortcut icon" href="../assets/ico/favicon.png"> -->
</head>
<body>
	<div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="">Jeff's Application Monitor</a>
          <div class="nav-collapse collapse">
		   	<ul class="nav">
         		<li class="dropdown">
           			<a id="drop1" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">Applications <b class="caret"></b></a>
           			<ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
             			<li role="presentation"><a role="menuitem" tabindex="-1" href="#anotherAction">Purge Disconnected Sessions</a></li>
           			</ul>
         		</li>
         		<li class="dropdown">
           			<a id="drop1" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">Logs <b class="caret"></b></a>
           			<ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
             			<li role="presentation"><a role="menuitem" tabindex="-1">Purge All</a></li>
             			<li role="presentation"><a role="menuitem" tabindex="-1" href="#anotherAction">Another action</a></li>
             			<li role="presentation"><a role="menuitem" tabindex="-1" href="#">Something else here</a></li>
             			<li role="presentation" class="divider"></li>
             			<li role="presentation"><a role="menuitem" tabindex="-1" href="#">Separated link</a></li>
           			</ul>
         		</li>
	    	</ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

	<div class='container'>
		<div class='row-fluid'>
			<div class='span11'>
				<label class="checkbox">
				      <input type="checkbox" id='chkAutoRefresh' checked='checked'>Auto-Refresh
			    </label>
				<h3>Application Table</h3>
			</div>
		</div>
		<div class='row-fluid'>
			<div class='span11' style='margin-bottom:0px'>
				<input type='button' class='btn btn-small' id='btnRefreshHb' value='Refresh Now'/>
			</div>
		</div>
		<div class='row-fluid'>
			<div class='span11'>
				<table id='tblApps' class='table table-condensed table-bordered'></table>
				<h3>System Log</h3>
					<input type='button' class='btn btn-small' id='btnRefreshLog' value='Refresh Now' />
					<!-- <span class="help-block" style='display:inline'>Max rows:</span> -->
					<span class="help-inline">Max rows:</span>
					<input id='txtMaxRows' type='text' value='250' style='width:40px' />
					<select id='ddlMsgType' class="selectpicker" data-style="btn-small">
						<option value='ERROR'>Show Error Messages</option>
						<option value='INFO'>Show Info Messages</option>
						<option value='ALL'>Show All Messages</option>
					</select>
					<input type='button' style='float:right' class='btn btn-small btnWarn' id='btnPurgeLogs' value='Purge All Logs' />	
			</div>
		</div>
		<div class='row-fluid'>
			<div class='span11'>
				<table id='tblLog' class='table table-condensed table-bordered'></table>
				<div id='divErr'>
					<label></label>
				</div>
			</div>
		</div>
	</div>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
	<script src="/WebApps/bootstrap/js/bootstrap.min.js"></script>
	<script type='text/javascript' src='../js/jefftron.js'></script>
	<script type='text/javascript'>
		
	<?php
		require_once "./_appServices/utils.php";
		$s = $_COOKIE['jefftronsessionid'];
		if (!isset($s) || !isValidSession($s)) {
			$s = getSessionByAppAndPassword('Monitor Page', getSessionPw());
			echo "setCookie('jefftronsessionid', '$s', 90);";
		}

		echo "var jefftronsessionid = $s;";
		echo "\n";
	?>
	</script>
	<script type='text/javascript'>
		$(function() {	
			
			setCookieValueAndHandlerForDropdown($('#ddlMsgType'), 'MessageType');
						
			$('#chkAutoRefresh').change(function() {
				if ($(this).prop('checked')) {
					autoRefresh();
				}
			});

			$('#btnRefreshLog').click(refreshLogTable);
			$('#btnRefreshHb').click(refreshHeartbeatsTable);
			$('#btnPurgeLogs').click(purgeLogs);

	  		setInterval(autoRefresh, 5000);

			refreshHeartbeatsTable();
			refreshLogTable();
	
		});
		
		

		function setCookieValueAndHandlerForDropdown(ddl, identifier) {

			var cookieVal = getCookie(identifier);
			if (cookieVal !== undefined && cookieVal !== null && cookieVal !== '') {
				ddl.val(cookieVal);
			}
			ddl.change(function() { 
				setCookie(identifier, this.value, 2592000); 
				refreshLogTable();
			});
		}
		
		function purgeLogs() {
			$.getJSON("_appServices/DataService.php", {method:'DELETE', function:'log', sessionid:jefftronsessionid}, function() {
				$('#tblLog tr').first().nextAll().remove();
			});
		}

		function autoRefresh() {
			if ($('#chkAutoRefresh').prop("checked")) {
				refreshHeartbeatsTable();
				refreshLogTable();
			}
		}

		function refreshHeartbeatsTable() {
			$.getJSON("_appServices/DataService.php", {method:'GET', function:'heartbeat',sessionid:jefftronsessionid}, function( json ) {
				var tbl = $('#tblApps');
				tbl.html('');
				var headerRow = $('<tr><th></th><th>Application</th><th>SessionID</th><th>Session Timestamp</th><th>Seconds since heartbeat</th><th>Regular heartbeat interval</th></tr>')
				tbl.append(headerRow);
				for(i = 0; i < json.length; i++) {
					var row = $('<tr></tr>');
					var app = json[i].applicationname, 
						seconds = json[i].secondssinceheartbeat, 
						interval = json[i].updateinterval,
						ts = json[i].sessiontimestamp,
						s = json[i].sessionid,
						extraInfo = json[i].info;
					if (extraInfo === undefined || extraInfo === '') {
						extraInfo = 'No additional information available';
					}
					
					var cmdCell = $('<td class="cmdCell"></td>');
					row.append(cmdCell)
						.append('<td id="'+app+'">' + app + '</td>')
						.append('<td>' + s + '</td>')
						.append('<td>' + ts + '</td>')
						.append('<td>' + seconds + '</td>')
						.append('<td>' + interval + '</td>');			
					var appliedClass = ((seconds > 2*interval) || (seconds === 'DISCONNECTED')) ? 'error' : 'success';
					// row.children('td').addClass(appliedClass);
					row.addClass(appliedClass);
					tbl.append(row);
					
					var cmdButton = $('<div class="btn-group"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a></div>');
					var cmdButtonList = $('<ul class="dropdown-menu"></ul>');
					cmdButton.append(cmdButtonList);

					var btnDelete = $('<a href="#"><i class="icon-trash"></i> Delete Session</a>');
					var liDelete = $('<li></li>');
					liDelete.append(btnDelete);
					cmdButtonList.append(liDelete);
					(function(_s) {
						btnDelete.click(function(e) {
							deleteSession(_s);
						});				
					})(s);

					cmdCell.append(cmdButton);
				}
								
			 }).fail(function( jqxhr, textStatus, error ) { 
				var errBox = $('#divErr');
				errBox.show();
				errBox.children('label').html(error.message);
			});		
		}

		function refreshLogTable() {
			var maxRows = $('#txtMaxRows').val();
			var logType = $('#ddlMsgType').val();
			$.getJSON("_appServices/DataService.php", {method:'GET', function:'log', maxrows:maxRows, messagetype:logType, sessionid:jefftronsessionid}, function( json ) {
				var tbl = $('#tblLog');
				tbl.html('');
				var headerRow = $('<tr><th>Timestamp</th><th>Application</th><th>Message Type</th><th>Message</th></tr>')
				tbl.append(headerRow);
				for(i = 0; i < json.length; i++) {
					var row = $('<tr></tr>');
					var ts = json[i].timestamp, msgtype = json[i].messagetype, msg = json[i].message, app = json[i].appname;
					row.append('<td>' + ts + '</td>').append('<td>' + app + '</td>').append('<td>' + msgtype + '</td>').append('<td>' + msg + '</td>');
					var appliedClass = (msgtype == 'ERROR') ? 'error' : 'success';
					// row.children('td').addClass(appliedClass);
					row.addClass(appliedClass);
					tbl.append(row);
				}
			 }).fail(function( jqxhr, textStatus, error ) { 
				var errBox = $('#divErr');
				errBox.show();
				errBox.children('label').html(error.message); 
			});		
		}
		
		function deleteSession(id) {
			$.getJSON("_appServices/DataService.php", {method:'DELETE', function:'session', sessionid_to_del:id, sessionid:jefftronsessionid}, function( json ) {
				refreshLogTable();
				refreshHeartbeatsTable();
			 }).fail(function( jqxhr, textStatus, error ) { 
				var errBox = $('#divErr');
				errBox.show();
				errBox.children('label').html(error.message); 
			});
		}

		function getTdCellObject(content) {
			return $('<td>' + content + '</td>');
		}
		
		/* cookie code from here: http://www.w3schools.com/js/js_cookies.asp */
		function setCookie(c_name, value, exdays)	{
			var exdate=new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
			document.cookie=c_name + "=" + c_value;
		}
		

		
		function getCookie(c_name)
		{
			var c_value = document.cookie;
			var c_start = c_value.indexOf(" " + c_name + "=");
			if (c_start == -1) {
			  	c_start = c_value.indexOf(c_name + "=");
			}
			if (c_start == -1) {
			  	c_value = null;
			}
			else {
				c_start = c_value.indexOf("=", c_start) + 1;
			  	var c_end = c_value.indexOf(";", c_start);
			  	if (c_end == -1) {
					c_end = c_value.length;
				}
				c_value = unescape(c_value.substring(c_start,c_end));
			}
			return c_value;
		}
		
	</script>
</body>