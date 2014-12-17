<?php
/**
 * UfoneBusinessSMS is a wrapper API to send sms using Ufone (http://ufone.com) network
 * You have to have a valid & active UfoneBusinessSMS account in order for it to work
 *
 *
 * Created by PhpStorm.
 * User: Asim Zeeshan
 * Date: 12/17/14
 * Time: 10:28 AM
 *
 */

class UfoneBusinessSMS {
    private $msisdn         = "";
    private $shortcode      = "";
    private $APIsecret      = "";
    private $lang           = "";
    private $debug_level    = false;
    private $groupSMS       = false;
    private $recipient      = "";
    private $groupname      = "";


    public function __construct($msisdn, $shortcode, $password, $lang="English", $debug_level=false) {
        $this->shortcode    = $this->_cleanInput($shortcode); // I don't have specifics on how to verify shortcodes
        $this->lang         = $this->_cleanInput($lang);
        $this->APIsecret    = (int)$this->_cleanInput($password);

        // if there is a debug level, sure, lets roll with it
        if ($debug_level!=false) {
            $this->debug_level  = $this->_cleanInput($debug_level);
        }

        // verify MSISDN & SHORTCODE
        if (empty($msisdn) || empty($shortcode) || empty($password)) {
            $this->throwError("You forgot to instantiate the class properly");
        } else if (!empty($msisdn) && $this->_verifyMSISDN($msisdn)) {
            $this->msisdn       = $this->_cleanInput($msisdn);
        }
    }

    /*
     * Utility function to clean the Input
     */
    private function _cleanInput($i) {
        return trim($i);
    }

    /*
     * verify MSISDN number, it should start with 0 and exactly 11 characters
     */
    private function _verifyMSISDN($msisdn) {
        if (!empty($msisdn) && strlen($msisdn) != 11) { // if its NOT 11 digits AND empty, throw it in fire
            $this->throwWarning("MSISDN length should be 11 digits, '$msisdn' does not look right!");
            return false;
        } else if (is_numeric($msisdn[0]) && $msisdn[0]=="0") { // Yes, its a valid number starting with 0
            return true;
        } else { // anything else? lets call it a day and close execution
            $this->throwError("No, it starts from '{$msisdn[0]}''");
            return false;
        }
    }

    /*
     * Verify recipients
     */
    private function _verifyRecipient($recipient) {
        if (!empty($recipient)) {
            if (is_numeric($recipient) && strlen($recipient)==12 && $recipient[0]=="9" && $recipient[1]=="2") {
                return true;
            } else { // anything else? lets call it a day and close execution
                $this->throwError("'$recipient' is not a valid recipient, it should be 12 digits in length and start with 92");
                return false;
            }
        }
    }

    /*
     * write everything to a log file IF debug_level=log
     */
    private function _generateLogFile($string) {
        $path = dirname(__FILE__);
        $date = date('Y-m-d h:i:s a T');
        $content_to_write = "[".$date."] ".$string;
        file_put_contents("$path/ufone_api.log", $content_to_write, FILE_APPEND | LOCK_EX);
    }

    /*
     * Internal method to return errors
     */
    private function throwError($error) {
        if ($this->debug_level!=false && $this->debug_level=="log") {
            $this->_generateLogFile("ERROR: ".$error."\n");
        } else if ($this->debug_level!=false && $this->debug_level=="output") {
            echo "ERROR: ".$error."\n";
            exit;
        } else {
            echo "What is this debug_level? '".$this->debug_level."'";
        }
    }

    /*
     * Internal method to return warnings
     */
    private function throwWarning($warning) {
        if ($this->debug_level!=false && $this->debug_level=="log") {
            $this->_generateLogFile("WARNING: ".$warning."\n");
        } else if ($this->debug_level!=false && $this->debug_level=="output") {
            echo "WARNING: ".$warning."\n";
        } else {
            echo "What is this debug_level? '".$this->debug_level."'";
        }
    }

    /*
     * Send a single SMS
     */
    public function sendSMS($recipient, $message) {
        $recipient  = $this->_cleanInput($recipient);
        if ($this->_verifyRecipient($recipient)===true) {
            $this->groupSMS  = false;
            $this->recipient = $recipient;
            return $this->doQuery($message);
        } else {
            $this->throwError("Recipient '$recipient' cannot be verified, im out!");
        }
    }

    /*
     * Send a group sms to the entire group
     */
    public function sendGroupSMS($groupname, $message) {
        if (!empty($groupname)) {
            $this->groupSMS  = true;
            $this->recipient = "";
            $this->groupname = $this->_cleanInput($groupname);
            return $this->doQuery($message);
        } else {
            $this->throwError("Group name cannot be empty, im out!");
        }

    }

    /*
     * DRUM ROLL!!
     *
     * Here comes the core method which calls the API and gets the required data
     */
    private function doQuery($message) {
        $api_call  = "";
        $api_call .= "?id=".$this->msisdn;
        $api_call .= "&message=".urlencode(trim($message));
        $api_call .= "&shortcode=".$this->shortcode;
        $api_call .= "&lang=".$this->lang;
        $api_call .= "&password=".$this->APIsecret;

        if ($this->groupSMS==false)
            $api_call .= "&mobilenum=".$this->recipient;
        else
            $api_call .= "&groupname=".$this->groupname;

        $ch = curl_init("http://bsms.ufone.com/bsms_app5/sendapi-0.3.jsp".$api_call);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        // we got XML, lets process it
        $xml = new SimpleXMLElement($response);

        // from documentation
        // 0 = Text message successfully sent
        // 1 = Text message could not sent successfully
        if ($xml->response_id=="0") {
            return (string)$xml->response_text;
        } else {
            return (string)$xml->response_text;
        }
    }
} 