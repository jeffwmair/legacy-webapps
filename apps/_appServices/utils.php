<?php

    /*
    * database connections
    */

    function getSessionPw() {
        return 'ag00dt3stpa$$word';
    }

    function isDev() {
        return true;
    }

    function getHost() {
        if (isDev())
        {
            return "127.0.0.1";
        }
        else
        {
            // return "localhost";
            // return "jefftrondata.db.9942823.hostedresource.com";
            return "localhost";
        }
    }

    function connect() {

        if (isDev())
        {
            $username = "internetUsage";
            $password = "internetUsage";
            $DB = "internetUsage";
        }
        else
        {
            $username = "jefftron_apps";
            $password = "";
            $DB = "jefftron_apps";
        }
        $host = getHost();

        if (!($conn = @ mysql_connect($host, $username, $password)))
        {
            $err = "Unable to connect to MySQL at $host with the username $username.  Check your password and that the username has access to MySql at this host.";
            die($err);
        }

        if (!(mysql_select_db($DB, $conn)))
        {
            $err = "Couldn't select $DB";
            die($err);
        }

        /*
        * setting the timezone here!!
        */
        mysql_query("SET time_zone = '-4:00';", $conn);

        return $conn;
    }

    /* helper to execute sql and deal with errors */
    function getSqlResult($sql) {
        $conn = connect();
        $res = mysql_query($sql, $conn);
        $err = mysql_error($conn);
        if ($err) {
            //logMsg('ERROR', $err);
            die('Sql Error:' . $err);
        }
        return $res;
    }

    /*
    *
    * logging
    *
    */

    function logMsg($msgType, $message, $app) {
        $sql = "INSERT INTO log (timestamp, app_name, message_type, message) values (now(), '$app', '$msgType', substring('$message' from 1 for 2000));";
        getSqlResult($sql);
    }


    /*
    *
    * session management
    *
    */

    function isValidSession($sessionid) {

        /* first, clean up the session table */
        $sql = "delete from application_session where date_add(timestamp, INTERVAL 120 MINUTE) < NOW();";
        getSqlResult($sql);
        $sql = "delete from application_heartbeats where not sessionid in (select sessionid from application_session);";
        getSqlResult($sql);

        /* check for this session */
        $sql = "select * from application_session where sessionid = $sessionid;";
        $rows = getSqlResult($sql);
        if ($rows) {
            $row = @ mysql_fetch_array($rows);
            return $row['sessionid'] == $sessionid;
        }
        else {
            return false;
        }
    }
    function validateSession() {
        $sessionid = getRequestArgument(ARG_SESSION, true);
        if (!isValidSession($sessionid)) {
            httpMsg(CODE_BAD_SESSION, 'INFO', 'Invalid or expired session id: '.$sessionid);
        }
    }
    function getSession() {

        $keyFromClient = getRequestArgument(ARG_PASS, true);
        $appname = getRequestArgument(ARG_APP, true);

        $newSessionId = getSessionByAppAndPassword($appname, $keyFromClient);

        $json = "[{\"sessionid\":$newSessionId}]";
        echo $json;
    }

    function deleteSession() {
        validateSession();
        $sessionid_to_del = getRequestArgument('sessionid_to_del', true);
        $sql = "delete from application_session where sessionid = $sessionid_to_del;";
        $res = getSqlResult($sql);
        returnJson(convertSqlRowsToJson($res));
    }

    function getSessionByAppAndPassword($appname, $keyFromClient) {

        if ($keyFromClient != getSessionPw()) {
            httpMsg(CODE_BAD_SESSION_PASS, 'ERROR', 'getSession(): Bad password');
        }
        elseif (!isset($appname) || $appname == '')
        {
            httpMsg(CODE_INVALID_ARGS, 'ERROR', 'getSession(): Missing applicationName argument');
        }

        $newSessionId = rand();
        // $sql = "DELETE FROM application_session WHERE application_name = '$appname';";
        // getSqlResult($sql);

        $sql = "INSERT INTO application_session values ('$appname', now(), $newSessionId, null);";
        getSqlResult($sql);

        return $newSessionId;
    }

    function setSessionStatus() {
        validateSession();
        $sessionid = getRequestArgument(ARG_SESSION, true);
        $status = getRequestArgument(ARG_APP_STATUS, true);
        $sql = "UPDATE application_session SET info = '$status' where sessionid = $sessionid;";
        getSqlResult($sql);
        returnJson(getSuccessResponse());
    }

    function getRequestArgument($argName, $cannotBeBlank) {
        $val = $_GET[$argName];
        if ($cannotBeBlank == true && ($val == '' || !isset($val)))
        {
            httpMsg(CODE_INVALID_ARGS, 'ERROR', 'Missing argument: ' . $argName);
        }
        return $val;
    }

    function httpMsg($code, $msgType, $msg) {
        logMsg($msgType, $msg, 'DataService');
        $jsonErr = "[{\"session_expired\":\"$msg\"}]";
        die($jsonErr);
    }

    /*
    *
    * data formatting
    *
    */
    function returnJson($data) {
        header('Content-Type: application/json');
        echo $data;
    }

    function cleanJson($json) {
        $json = str_replace("\n", "\\n", $json);
        $json = str_replace("\r", "\\r", $json);
        // $json = str_replace('"', '\"', $json);
        return $json;
    }

    function convertSqlRowsToJson($rows) {
        $json = '[';
        while ($row = @ mysql_fetch_array($rows, MYSQL_ASSOC))
        {
            if ($json != '[') $json .= ',';
            $json .= json_encode($row);
        }
        $json .= ']';
        return $json;
    }

    function getSuccessResponse($msg = '') {
        return "[{\"success\":\"$msg\"}]";
    }

    function getFailureResponse($msg = '') {
        return "[{\"failure\":\"$msg\"}]";
    }

    function getRequestInfoResponse() {
        return "[{\"inforequest\":\"\"}]";
    }

    /*
    *
    * encryption
    *
    */

    function encrypt($sValue, $sSecretKey)
    {
        return rtrim(
            base64_encode(
                mcrypt_encrypt(
                    MCRYPT_RIJNDAEL_256,
                    $sSecretKey, $sValue,
                    MCRYPT_MODE_ECB,
                    mcrypt_create_iv(
                        mcrypt_get_iv_size(
                            MCRYPT_RIJNDAEL_256,
                            MCRYPT_MODE_ECB
                        ),
                        MCRYPT_RAND)
                    )
                ), "\0"
            );
    }

    function decrypt($sValue, $sSecretKey)
    {
        return rtrim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                $sSecretKey,
                base64_decode($sValue),
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    ),
                    MCRYPT_RAND
                )
            ), "\0"
        );
    }

    function getUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $buffer = curl_exec($ch);
        curl_close($ch);
        return $buffer;
    }
?>
