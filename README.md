# UfoneBusinessSMSAPI

A **wrapper API** to use the [Ufone Business SMS](http://www.ufone.com/vas/Business-Solutions/Business-SMS/) since there is none provided by Ufone at the moment

## Usage

Instantiate the class like the following where the first parameter is the MSISDN number, second parameter the SMS-MASKING and third parameter the API-password

    require_once "UfoneBusinessSMS.php";
    $sms = new UfoneBusinessSMS("03351111111", "247NE", "QQ89L1");

In order to send a single SMS use the `sendSMS(MOBILE-NUMBER, MESSAGE)` method as explained below

    $result = $sms->sendSMS("923334444555", "Hello there! - Sent using https://github.com/asimzeeshan/UfoneBusinessSMSAPI");
    // If it returns something like "Successful:16340543", the SMS was sent successfully
    
In order to send a group SMS, create the group in UfoneBusinessSMS control panel first and note the GROUP NAME. Then you can use the `sendGroupSMS(GROUPNAME, MESSAGE)` method to send the sms to entire group you just created

    $result = $sms->sendGroupSMS("NOC", "Hello there! - Sent using https://github.com/asimzeeshan/UfoneBusinessSMSAPI");
    // If it returns something like "Successful:16340543", the SMS was sent successfully

### License

This is licensed under [GNU GPL v2.0](http://choosealicense.com/licenses/gpl-2.0/)


# Credits
* [Asim Zeeshan](http://asim.pk)

#### Special thanks to [phpStorm](https://www.jetbrains.com/phpstorm/) for supporting me in these open-source projects