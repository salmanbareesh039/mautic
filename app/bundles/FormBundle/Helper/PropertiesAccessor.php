<?php

declare(strict_types=1);

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Helper;

use Mautic\FormBundle\Model\FormModel;

class PropertiesAccessor
{
    /**
     * @var FormModel
     */
    private $formModel;

    public function __construct(FormModel $formModel)
    {
        $this->formModel = $formModel;
    }

    /**
     * @return array|mixed
     */
    public function getProperties(array $field)
    {
        if ('country' === $field['type'] || (!empty($field['leadField']) && !empty($field['properties']['syncList']))) {
            return $this->formModel->getContactFieldPropertiesList($field['leadField']);
        } elseif (!empty($field['properties'])) {
            return $this->getOptionsListFromProperties($field['properties']);
        }

        return [];
    }

    /**
     * @return array
     */
    public function getChoices($options)
    {
        $choices = [];

        if (is_array($options) && !isset($options[0]['value'])) {
            return array_flip($options);
        }

        if (!is_array($options)) {
            $options = explode('|', $options);
        }

        foreach ($options as $option) {
            if (is_array($option)) {
                if (isset($option['label']) && isset($option['alias'])) {
                    $choices[$option['label']] = $option['alias'];
                } elseif (isset($option['label']) && isset($option['value'])) {
                    $choices[$option['label']] = $option['value'];
                } else {
                    foreach ($option as $opt) {
                        $choices[$opt] = $opt;
                    }
                }
            } else {
                $choices[$option] = $option;
            }
        }

        return $choices;
    }

    /**
     * @return array|mixed
     */
    private function getOptionsListFromProperties(array $properties)
    {
        if (!empty($properties['list']['list'])) {
            return $properties['list']['list'];
        } elseif (!empty($properties['optionlist']['list'])) {
            return $properties['optionlist']['list'];
        }

        return [];
    }
}
