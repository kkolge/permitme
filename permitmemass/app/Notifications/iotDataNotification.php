<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Discord\DiscordChannel;
use App\Channels\ClickSendChannel;
use ClickSend;
use ClickSend\Model\SmsMessage;
use GuzzleHttp;



class iotDataNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $txtMsg;
    private $pno;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($txtMsg,$pno)
    {
        //
        $this->txtMsg = $txtMsg;
        $this->pno = $pno;
        Log::debug('in constructor');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        //return ['mail'];
        Log::debug('in via method');
        return [ClickSendChannel::class];
    }

    public function toCsc($notifiable)
    {
        Log::debug('in to Csc');
        // Configure HTTP basic authorization: BasicAuth
        $config = ClickSend\Configuration::getDefaultConfiguration()
        ->setUsername(env('CS_USERNAME'))
        ->setPassword(env('CS_PASSWORD'));

        $apiInstance = new ClickSend\Api\SMSApi(
        // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
        // This is optional, `GuzzleHttp\Client` will be used as default.
        new GuzzleHttp\Client(),
        $config
        );
        $msg = new \ClickSend\Model\SmsMessage();
        $msg->setBody($this->txtMsg); 
        $msg->setTo($this->pno);
        $msg->setSource("sdk");

        $sms_messages = new \ClickSend\Model\SmsMessageCollection(); 
        $sms_messages->setMessages([$msg]);

        try {
            $result = $apiInstance->smsSendPost($sms_messages);
            Log::debug($result);
        } catch (Exception $e) {
            Log::debug( 'Exception when calling AccountApi->accountGet: ', $e->getMessage());
        }
    }

    /**
     * Method to send the message using Nexmo Channel
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content('Your SMS message content');
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
 * Determine which queues should be used for each notification channel.
 *
 * @return array
 */
public function viaQueues()
{
    return [
        'mail' => 'mail-queue',
        'sms' => 'sms-queue',
    ];
}
}
