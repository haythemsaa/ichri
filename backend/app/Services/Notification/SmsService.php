<?php

namespace App\Services\Notification;

use Twilio\Rest\Client;

class SmsService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        if (config('services.twilio.enabled')) {
            $this->client = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
            $this->fromNumber = config('services.twilio.from');
        }
    }

    /**
     * Send SMS
     */
    public function send($to, $message)
    {
        if (!config('services.twilio.enabled')) {
            // Log instead of sending in development
            \Log::info("SMS to {$to}: {$message}");
            return true;
        }

        try {
            $this->client->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP SMS
     */
    public function sendOTP($phone, $otp)
    {
        $message = "Votre code de vérification ichri.tn est: {$otp}. Valide pendant 5 minutes.";
        return $this->send($phone, $message);
    }

    /**
     * Send order confirmation SMS
     */
    public function sendOrderConfirmation($phone, $orderNumber)
    {
        $message = "Votre commande #{$orderNumber} a été confirmée. Elle sera livrée sous 24h. Merci ichri.tn";
        return $this->send($phone, $message);
    }

    /**
     * Send order shipped SMS
     */
    public function sendOrderShipped($phone, $orderNumber, $driverName)
    {
        $message = "{$driverName} part livrer votre commande #{$orderNumber}. ichri.tn";
        return $this->send($phone, $message);
    }

    /**
     * Send order delivered SMS
     */
    public function sendOrderDelivered($phone, $orderNumber)
    {
        $message = "Votre commande #{$orderNumber} a été livrée. Merci de votre confiance! ichri.tn";
        return $this->send($phone, $message);
    }
}
