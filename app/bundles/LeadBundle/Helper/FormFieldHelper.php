<?php


namespace Mautic\LeadBundle\Helper;

use Mautic\CoreBundle\Helper\AbstractFormFieldHelper;
use Symfony\Component\Intl\Intl;

class FormFieldHelper extends AbstractFormFieldHelper
{
    /**
     * @var array
     */
    private static $types = [
        'text' => [
            'properties' => [],
        ],
        'textarea' => [
            'properties' => [
                'allowHtml' => [],
            ],
        ],
        'multiselect' => [
            'properties' => [
                'list' => [
                    'required'  => true,
                    'error_msg' => 'mautic.lead.field.select.listmissing',
                ],
            ],
        ],
        'select' => [
            'properties' => [
                'list' => [
                    'required'  => true,
                    'error_msg' => 'mautic.lead.field.select.listmissing',
                ],
            ],
        ],
        'boolean' => [
            'properties' => [
                'yes' => [
                    'required'  => true,
                    'error_msg' => 'mautic.lead.field.boolean.yesmissing',
                ],
                'no' => [
                    'required'  => true,
                    'error_msg' => 'mautic.lead.field.boolean.nomissing',
                ],
            ],
        ],
        'lookup' => [
            'properties' => [
                'list' => [],
            ],
        ],
        'date' => [
            'properties' => [
                'format' => [],
            ],
        ],
        'datetime' => [
            'properties' => [
                'format' => [],
            ],
        ],
        'time' => [
            'properties' => [],
        ],
        'timezone' => [
            'properties' => [],
        ],
        'email' => [
            'properties' => [],
        ],
        'number' => [
            'properties' => [
                'roundmode' => [],
                'scale'     => [],
            ],
        ],
        'tel' => [
            'properties' => [],
        ],
        'url' => [
            'properties' => [],
        ],
        'country' => [
            'properties' => [],
        ],
        'region' => [
            'properties' => [],
        ],
        'locale' => [
            'properties' => [],
        ],
    ];

    /**
     * Set the translation key prefix.
     */
    public function setTranslationKeyPrefix()
    {
        $this->translationKeyPrefix = 'mautic.lead.field.type.';
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return self::$types;
    }

    /**
     * @return array
     */
    public static function getListTypes()
    {
        return ['select', 'multiselect', 'boolean', 'lookup', 'country', 'region', 'timezone', 'locale'];
    }

    /**
     * @param $type
     * @param $properties
     *
     * @return bool
     */
    public static function validateProperties($type, &$properties)
    {
        if (!array_key_exists($type, self::$types)) {
            //ensure the field type is supported
            return [false, 'mautic.lead.field.typenotrecognized'];
        }

        $fieldType = self::$types[$type];
        foreach ($properties as $key => $value) {
            if (!array_key_exists($key, $fieldType['properties'])) {
                unset($properties[$key]);
            }

            if (!empty($fieldType['properties'][$key]['required']) && empty($value)) {
                //ensure requirements are met
                return [false, $fieldType['properties'][$key]['error_msg']];
            }
        }

        return [true, ''];
    }

    /**
     * @return array
     */
    public static function getCountryChoices()
    {
        $countryJson = file_get_contents(__DIR__.'/../../CoreBundle/Assets/json/countries.json');
        $countries   = json_decode($countryJson);

        return array_combine($countries, $countries);
    }

    /**
     * @return array
     */
    public static function getRegionChoices()
    {
        $regionJson = file_get_contents(__DIR__.'/../../CoreBundle/Assets/json/regions.json');
        $regions    = json_decode($regionJson);
        $choices    = [];

        foreach ($regions as $country => &$regionGroup) {
            $choices[$country] = array_combine($regionGroup, $regionGroup);
        }

        return $choices;
    }

    /**
     * Symfony deprecated and changed Symfony\Component\Form\Extension\Core\Type\TimezoneType::getTimezones to private
     * in 3.0 - so duplicated code here.
     *
     * @return array
     */
    public static function getTimezonesChoices()
    {
        static $timezones;

        if (null === $timezones) {
            $timezones = [];

            foreach (\DateTimeZone::listIdentifiers() as $timezone) {
                $parts = explode('/', $timezone);

                if (count($parts) > 2) {
                    $region = $parts[0];
                    $name   = $parts[1].' - '.$parts[2];
                } elseif (count($parts) > 1) {
                    $region = $parts[0];
                    $name   = $parts[1];
                } else {
                    $region = 'Other';
                    $name   = $parts[0];
                }

                $timezones[$region][str_replace('_', ' ', $name)] = $timezone;
            }
        }

        return $timezones;
    }

    /**
     * Get locale choices.
     *
     * @return array
     */
    public static function getLocaleChoices()
    {
        return array_flip(Intl::getLocaleBundle()->getLocaleNames());
    }

    /**
     * Get date field choices.
     *
     * @return array
     */
    public function getDateChoices()
    {
        return [
            $this->translator->trans('mautic.campaign.event.timed.choice.anniversary') => 'anniversary',
            $this->translator->trans('mautic.campaign.event.timed.choice.today')       => '+P0D',
            $this->translator->trans('mautic.campaign.event.timed.choice.yesterday')   => '-P1D',
            $this->translator->trans('mautic.campaign.event.timed.choice.tomorrow')    => '+P1D',
        ];
    }
}
