<?php

namespace App;
use FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;


use Illuminate\Database\Eloquent\Model;

class FCMNotify extends Model
{
    public function sendNotification($title, $message, $fcm_tokens)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($message)
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // You must change it to get your tokens
        // $tokens = AdminDetails::where('email', "sanket.admin@airavatcs.in")->pluck('fcm_token')->toArray();

        $tokens = $fcm_tokens;

        $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        // return Array - you must remove all this tokens in your database
        // $downstreamResponse->tokensToDelete();

        // // return Array (key : oldToken, value : new token - you must change the token in your database)
        // $downstreamResponse->tokensToModify();

        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

        // return Array (key:token, value:error) - in production you should remove from your database the tokens present in this array
        $downstreamResponse->tokensWithError();
        return 'success';
        // return 'SUCCESS: ' . $downstreamResponse->numberSuccess() . ' || RETRY: ' . $downstreamResponse->tokensToRetry() . ' || ERROR: ' . $downstreamResponse->tokensWithError();
    }
}
