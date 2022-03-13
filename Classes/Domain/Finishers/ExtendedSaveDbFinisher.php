<?php
namespace ACME\UserAccountExt\Domain\Finishers;

/**
 * This file is part of the "User Account Handler" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Anu Bhuvanendran Nair <anu93nair@gmail.com>
 */

use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\SaveToDatabaseFinisher as FormSaveToDatabase;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * This finisher saves the data from a submitted form into
 * a database table.
 * Extended from original finisher from EXT:form
 * for tweaking the md5 hashing for password.
 * Only for fe_users.
 * This can also be extended to tweak the storage PID from typoscript settings
 */
class ExtendedSaveDbFinisher extends FormSaveToDatabase
{
    /**
     * Prepare data for saving to database
     *
     * @param array $elementsConfiguration
     * @param array $databaseData
     * @return mixed
     */
    protected function prepareData(array $elementsConfiguration, array $databaseData)
    {
        foreach ($this->getFormValues() as $elementIdentifier => $elementValue) {
            if (
                ($elementValue === null || $elementValue === '')
                && isset($elementsConfiguration[$elementIdentifier])
                && isset($elementsConfiguration[$elementIdentifier]['skipIfValueIsEmpty'])
                && $elementsConfiguration[$elementIdentifier]['skipIfValueIsEmpty'] === true
            ) {
                continue;
            }

            $element = $this->getElementByIdentifier($elementIdentifier);
            if (
                !$element instanceof FormElementInterface
                || !isset($elementsConfiguration[$elementIdentifier])
                || !isset($elementsConfiguration[$elementIdentifier]['mapOnDatabaseColumn'])
            ) {
                continue;
            }

            if ($elementValue instanceof FileReference) {
                if (isset($elementsConfiguration[$elementIdentifier]['saveFileIdentifierInsteadOfUid'])) {
                    $saveFileIdentifierInsteadOfUid = (bool)$elementsConfiguration[$elementIdentifier]['saveFileIdentifierInsteadOfUid'];
                } else {
                    $saveFileIdentifierInsteadOfUid = false;
                }

                if ($saveFileIdentifierInsteadOfUid) {
                    $elementValue = $elementValue->getOriginalResource()->getCombinedIdentifier();
                } else {
                    $elementValue = $elementValue->getOriginalResource()->getProperty('uid_local');
                }
            } elseif (is_array($elementValue)) {
                $elementValue = implode(',', $elementValue);
            } elseif ($elementValue instanceof \DateTimeInterface) {
                $format = $elementsConfiguration[$elementIdentifier]['dateFormat'] ?? 'U';
                $elementValue = $elementValue->format($format);
            } elseif ($elementIdentifier == 'dateofbirth') {
                $elementValue = strtotime($elementValue);
            }

            $databaseData[$elementsConfiguration[$elementIdentifier]['mapOnDatabaseColumn']] = $elementValue;
        }
        // checking current FE password hashing mechanism and encrypting entered password before saving to database
        if (array_key_exists('password', $databaseData)) {
            $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');
            $databaseData['password'] = $hashInstance->getHashedPassword($databaseData['password']);
        }
        return $databaseData;
    }
}
