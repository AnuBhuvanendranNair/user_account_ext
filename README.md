# EXT:user_account_ext
A TYPO3 extension which facilitates user registration and associated functionalities incorporating TYPO3's core fe_login and forms extensions. 

Please find the working demo under 
- Login : http://typo3test.webofficeit.com/
- Registration: http://typo3test.webofficeit.com/registration

### Requirements

- TYPO3 v10.0.0 - v10.4.26
- PHP v7.4
- typo3/cms-felogin
- typo3/cms-form

Introduction
-------------

### What does it do?
This extension enables a typo3 form for registration of fe-users. This registered users can be used to login to the website.
There is a registration form shipped with the extension which makes use of EXT:Forms. This prebuilt form includes SaveToDatabase finisher, a ConfirmationMessage finisher and a custom double opt in finisher.

Once a user registers with the form, a double opt in is sent to the mail address for verification of the email. Once verified, the uer will be logged in to the website and a report mail is sent to the administrator.

Once the preocess is completed, users can login to the website using fe-login plugin added in a desired page.

### Features
The extension comes with a custom double-opt-in finisher and an extended save to database finisher which handles the hashing/salting of password while account creation.
A middleware is included which take care of the authorisation and verification process.

Installation
-------------
- Extension can be downloaded and added to `typo3conf/ext` folder for non-composer installations. 
- For comnposer installations, add the extension into a folder in root such as `packages/` and run `composer req acme/user-account-ext:@dev` or add github as repo urls in composer.json file and install like any other repository packages

Configuration
-------------
