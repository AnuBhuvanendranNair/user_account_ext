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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

/**
 * Finisher class for handling Double-Opt-In
 */
class DoubleOptInFinisher extends AbstractFinisher
{
    /**
     * The main finisher method
     */
    protected function executeInternal()
    {
        $values = $this->finisherContext->getFormValues();
        $pages = $this->finisherContext->getFormRuntime()->getFormDefinition()->getPages();
        $firstnameIdentifier = $this->parseOption('firstnameIdentifier');
        $lastnameIdentifier = $this->parseOption('lastnameIdentifier');
        $email = '';
        // Search for the email field in the form values
        foreach ($pages as $page) {
            foreach ($page->getElements() as $element) {
                if ($element->getType() === 'Fieldset') {
                    foreach ($element->getElements() as $field) {
                        if ($field->getType() === 'Email') {
                            if (!empty($values[$field->getIdentifier()])) {
                                $email = $values[$field->getIdentifier()];
                            }
                        }
                    }
                }
            }
        }

        if (!$email) {
            // If no email field is present, the functioanlity should not be executed
            return;
        }

        // This should get the last inserted user ID
        $insertedUser = $this->finisherContext->getFinisherVariableProvider()->get(
            'SaveToDatabase',
            'insertedUids'
        );

        // a standalone double opt record for corresponding user is created to make sure
        // the particular user is verified or not
        // To the mail template a hash and current user id is passed which can be used in the verification link
        $hash = bin2hex(random_bytes(30));
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('form_double_opt_in')->createQueryBuilder();
        $queryBuilder->insert('form_double_opt_in')
            ->values([
                'pid' => $GLOBALS['TSFE']->id,
                'email' => $email,
                'firstname' => $values[$firstnameIdentifier] ?? '',
                'lastname' => $values[$lastnameIdentifier] ?? '',
                'hash' => $hash,
                'verified' => 0,
                'deleted' => 1
            ])
            ->execute();
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:user_account_ext/Resources/Private/Template/Forms/Email/') . 'DoubleOptIn.html');
        $standaloneView->assignMultiple([
            'hash' => $hash,
            'baseUrl' => 'http://' . $_SERVER['SERVER_NAME'] . '/',
            'user' => $insertedUser[0],
        ]);
        $message = $standaloneView->render();
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        /**
         * @var $mail MailMessage
         */
        $mail->setFrom([$this->parseOption('senderEmail') => $this->parseOption('senderName')]);
        $mail->setTo([$email => ($values[$firstnameIdentifier] ?? ' ') . ' ' . $values[$lastnameIdentifier] ?? '']);
        $mail->setSubject($this->parseOption('subject'));
        $mail->setBody()->html($message);
        $mail->send();
        return null;
    }
}
