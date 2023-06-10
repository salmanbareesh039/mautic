<?php

namespace Mautic\EmailBundle\Swiftmailer\Transport;

use Mautic\EmailBundle\MonitoredEmail\Message;
use Mautic\EmailBundle\Swiftmailer\Amazon\AmazonCallback;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AmazonTransport.
 */
class AmazonTransport extends \Swift_SmtpTransport implements CallbackTransportInterface, BounceProcessorInterface, UnsubscriptionProcessorInterface
{
    /**
     * AmazonTransport constructor.
     *
     * @param string $host
     * @param string $otherHost
     * @param int    $port
     */
    public function __construct($region, $otherRegion, $port, private AmazonCallback $amazonCallback)
    {
        $port                 = $port ?: 2587;
        $host                 = $this->buildHost($region, $otherRegion);

        parent::__construct($host, $port, 'tls');

        $this->setAuthMode('login');
    }

    /**
     * Switch statement used to avoid breaking change.
     *
     * @param string $region
     * @param string $otherRegion
     *
     * @return string
     */
    public function buildHost($region, $otherRegion)
    {
        $sesRegion = ('other' === $region) ? $otherRegion : $region;

        return match ($sesRegion) {
            'email-smtp.eu-west-1.amazonaws.com', 'email-smtp.us-east-1.amazonaws.com', 'email-smtp.us-west-2.amazonaws.com' => $sesRegion,
            default => 'email-smtp.'.$sesRegion.'.amazonaws.com',
        };
    }

    /**
     * Returns a "transport" string to match the URL path /mailer/{transport}/callback.
     *
     * @return string
     */
    public function getCallbackPath()
    {
        return 'amazon';
    }

    /**
     * Handle bounces & complaints from Amazon.
     */
    public function processCallbackRequest(Request $request)
    {
        $this->amazonCallback->processCallbackRequest($request);
    }

    public function processBounce(Message $message)
    {
        $this->amazonCallback->processBounce($message);
    }

    public function processUnsubscription(Message $message)
    {
        $this->amazonCallback->processUnsubscription($message);
    }
}
