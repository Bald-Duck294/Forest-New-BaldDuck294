<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SMS extends Model
{



    public function SendSms($msg,$mobile){

        $authkey = "335514AgMLkQgS5f0c14a5P1";
        $url='https://control.msg91.com/api/sendhttp.php?authkey='.$authkey.'&mobiles='.$mobile.'&message='.$msg.'&sender=PGMARG&route=4&country=91';


        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        curl_close($curl);


    }


    public function SendSms2($msg,$mobile){

        // $dlt_te_id = 1207162513908517808;
        $dlt_te_id = 1207162513952739379;

        $msg=urlencode($msg);
        $authkey = "335514AgMLkQgS5f0c14a5P1";
        $url='https://control.msg91.com/api/sendhttp.php?authkey='.$authkey.'&mobiles='.$mobile.'&message='.$msg.'&sender=GDKONN&route=4&country=91&DLT_TE_ID='.$dlt_te_id;


        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        curl_close($curl);

        return $curl_response;
    }


    function sendCommand($parent_mobile,$command) {


        //Your message to send, Add URL encoding here.
        $message = urlencode($command);
        $response_type = 'json';
        //Define route
        $route = "4";
        $authkey = "335514AgMLkQgS5f0c14a5P1";
        $sender = "611332";
        $url='https://control.msg91.com/api/sendhttp.php?authkey='.$authkey.'&mobiles='.$parent_mobile.'&message='.$message.'&sender=PGMARG&route=4&country=91';

        // CURL Commands
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        curl_close($curl);
        return $curl_response;
    }


    function cal_call_checkSome($data1){

        $strlen=strlen($data1);
        $nmea =$data1;//$data1;
        $checksum = 0;

        for($i = 0; $i < strlen($nmea); $i++)
        {
            $c = substr($nmea,$i,1);
            $n = ord($c);
            if($c == '$')
            {
                $checksum = 0;
            }
            else if($c == '*')
            {
                break;
            }
            else
            {
                $checksum ^= $n;
            }
        }

        $calculated = strtoupper(dechex($checksum));
        return $calculated;
    }


    function sendSmstomotherfather($father_mobile,$mother_mobile, $child_name, $date, $type) {



        $otp_prefix = ':';

        //Your message to send, Add URL encoding here.
        $message = urlencode("Hello! Welcome to PUGMARG. your child $otp_prefix '$child_name' is $type up by her parent ON the Date: $date.");

        $response_type = 'json';
        $route = "4"; //Define route
        $authkey = "335514AgMLkQgS5f0c14a5P1";
        $sender = "611332";
        $url='https://control.msg91.com/api/sendhttp.php?authkey='.$authkey.'&mobiles='.$father_mobile.','.$mother_mobile.'&message='.$message.'&sender=PGMARG&route=4&country=91';



        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        curl_close($curl);
    }
}
