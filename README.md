# EXT:user_account_ext
A TYPO3 extension which facilitates user registration and associated functionalities incorporating TYPO3's core fe_login and forms extensions. 

Please find the working demo under 
- Login : https://typo3test.webofficeit.com/
- Registration: https://typo3test.webofficeit.com/registration

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
Once the extension is installed corresponding typoscript settings will be loaded automatically. So as the EXT:Form custom configurations.
Before starting working around of extension, we need to place the login plugin in the desired page.

EXT:felogin provides a simple login plugin which is shipped with TYPO3 core. 

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/9.png" width="300">

Once the login plugin is added goto **Admin Tools > Settings** and select Extension configuration to add the custom extension settings. We have 3 options here,
1. Login page ID > The page ID where login plugin is added

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/6.png" width="300">

2. Admin user email ID > The email where user creation have to be reported

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/5.png" width="300">

3. Admin mail subject > The report email subject

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/4.png" width="300">

Once the settings are added. Corresponding registration form can be added into the desired page with the default Form content element

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/3.png" width="300">

The extension does have the boostrap styling included. So the forms have the basic styling with it but it can be tweaked as per the site.

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/7.png" width="300">

Once the data is filled and submitted, double opt-in finisher will be in play and a mail will be sent to the user for verifying the email ID.

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/8.png" width="300">

Before approval, the user will be disabled in the BE and a double opt record is created in BE for keeping track of the verification procedure.

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/2.png" width="300">

In the mail, when the user approves the email he/she will be taken back to the site with a pre-logged in state

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/10.png" width="300">

By default the user is saved to the root page, which should be specified inside the login plugin

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/1.png" width="300">

On verification, the admin will receive an email about the account creation.(as configured in settings)

<img src="https://typo3test.webofficeit.com/fileadmin/user_upload/11.png" width="300">

On a extended time period, the page where fe_user records could be saved can be made customizable for flexibility.

Form features
-------------
The date is validated for 18+ aged users and also the country list for place of birth field is custom made. We dont have a default country selector within EXT:Forms.
Right now, the extension uses symfony/intl package for fetching the country list and renders with a custom field within the form.

Thanks. Please update in case of support regarding installation or test cases. Can also be tested with the link provided in the beginning.
