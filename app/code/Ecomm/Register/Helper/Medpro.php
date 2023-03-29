<?php
namespace Ecomm\Register\Helper;

/**
 * Provides list of modes.
 */
class Medpro
{
    /**
     * Determines whether an Medpro status is valid.
     *
     * @param string $statusInQuestion
     * @return bool
     */
    public function isValidStatus($statusInQuestion)
    {
        foreach ($this->getStatuses() as $currentMode) {
            if ($currentMode['value'] == $statusInQuestion) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return [
            [
                'value' => '1',
                'label' => 'Enabled',
            ],
            [
                'value' => '0',
                'label' => 'Disabled',
            ],
        ];
    }
}
