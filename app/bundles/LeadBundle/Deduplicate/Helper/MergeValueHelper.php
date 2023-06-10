<?php

namespace Mautic\LeadBundle\Deduplicate\Helper;

use Mautic\LeadBundle\Deduplicate\Exception\ValueNotMergeableException;

class MergeValueHelper
{
    /**
     * @param bool $newIsAnonymous
     *
     * @return mixed
     *
     * @throws ValueNotMergeableException
     */
    public static function getMergeValue(mixed $newerValue, mixed $olderValue, mixed $currentValue = null, mixed $defaultValue = null, $newIsAnonymous = false)
    {
        if ($newerValue === $olderValue) {
            throw new ValueNotMergeableException($newerValue, $olderValue);
        }

        if (null !== $currentValue && $newerValue === $currentValue) {
            throw new ValueNotMergeableException($newerValue, $olderValue);
        }

        $isDefaultValue = null !== $defaultValue && $newerValue === $defaultValue;

        if (self::isNotEmpty($newerValue) && !($newIsAnonymous && $isDefaultValue)) {
            return $newerValue;
        }

        if (self::isNotEmpty($olderValue)) {
            return $olderValue;
        }

        throw new ValueNotMergeableException($newerValue, $olderValue);
    }

    /**
     * @return bool
     */
    public static function isNotEmpty($value)
    {
        return null !== $value && '' !== $value;
    }
}
