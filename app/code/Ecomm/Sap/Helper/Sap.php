<?php
namespace Ecomm\Sap\Helper;

/**
 * Provides list of modes.
 */
class Sap
{
    /**
     * Determines whether an Sap mode code is valid.
     *
     * @param string $modeInQuestion
     * @return bool
     */
    public function isValidMode($modeInQuestion)
    {
        foreach ($this->getModes() as $currentMode) {
            if ($currentMode['value'] == $modeInQuestion) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getModes()
    {
        return [
            [
                'value' => 'sandbox',
                'label' => 'Sand-box',
            ],
            [
                'value' => 'live',
                'label' => 'Live',
            ],
        ];
    }
}
