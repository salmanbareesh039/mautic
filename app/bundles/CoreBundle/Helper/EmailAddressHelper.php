<?php

namespace Mautic\CoreBundle\Helper;

class EmailAddressHelper
{
    /**
     * Clean the email for comparison.
     *
     * @param string $email
     *
     * @return string
     */
    public function cleanEmail($email)
    {
        return strtolower(preg_replace("/[^a-z0-9\+\.@]/i", '', $email));
    }

    public function getVariations(string $email): array
    {
        $emails = [$email, $this->cleanEmail($email)];
        // email without suffix
        preg_match('#^(.*?)\+(.*?)@(.*?)$#', $email, $parts);
        if (!empty($parts)) {
            $emails[] = $parts[1].'@'.$parts[3];
        }

        return array_unique($emails);
    }
}
