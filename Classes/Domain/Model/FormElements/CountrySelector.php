<?php
namespace ACME\UserAccountExt\Domain\Model\FormElements;

/**
 * This file is part of the "User Account Handler" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Anu Bhuvanendran Nair <anu93nair@gmail.com>
 */

use Symfony\Component\Intl\Countries;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

/**
 * This class renders a list of countries as obtained from symfony intl package
 */
class CountrySelector extends GenericFormElement
{
    /**
     * Render form element, accumulate required value - labels into the option
     */
    public function initializeFormElement(): void
    {
        //This language value can be further extended by reading from current page lanugage and
        // can be used to render names dynamically based on selected language
        $countries = Countries::getNames('en');
        $this->setProperty(
            'options',
            $countries
        );
    }
}
