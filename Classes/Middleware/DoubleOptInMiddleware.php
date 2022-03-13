<?php
namespace ACME\UserAccountExt\Middleware;

/**
 * This file is part of the "User Account Handler" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 Anu Bhuvanendran Nair <anu93nair@gmail.com>
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use Symfony\Component\HttpFoundation\Cookie;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This middleware class checkes whether the current url is a verification URL and proceeds
 */
class DoubleOptInMiddleware implements MiddlewareInterface
{
    /**
     * Main middleware method
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        // proceed only if the url params contain eID, hash elements passed from the mail data
        if (is_array($queryParams)) {
            if (array_key_exists('keyval', $queryParams)) {
                if ($queryParams['hash'] && is_string($queryParams['hash'])) {
                    $hash = $queryParams['hash'];

                    $response = $handler->handle($request);
                    if ($response instanceof NullResponse) {
                        return $response;
                    }
                    // update depuble opt record as verified
                    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                        ->getConnectionForTable('form_double_opt_in')->createQueryBuilder();
                    $queryBuilder
                        ->update('form_double_opt_in')
                        ->where(
                            $queryBuilder->expr()->eq('verified', 0),
                            $queryBuilder->expr()->eq('hash', $queryBuilder->createNamedParameter($hash))
                        )
                        ->set('verified', 1)
                        ->set('deleted', 0)
                        ->execute();

                    // enable fe user record and activate user
                    $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                        ->getConnectionForTable('fe_users')->createQueryBuilder();
                    $queryBuilder
                        ->update('fe_users')
                        ->where(
                           $queryBuilder->expr()->eq('uid', $queryParams['id'])
                        )
                        ->set('disable', '0')
                        ->execute();

                    $feUserRepo = GeneralUtility::makeInstance(FrontendUserRepository::class);
                    $user = $feUserRepo->findByUid($queryParams['id']);

                    // this method will authenticate the current user with TYPO3
                    // and returns the redirect url
                    $url = $this->userLogin($user);

                    // compile a html view and report activation status
                    $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
                    $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:user_account_ext/Resources/Private/Template/Forms/Email/') . 'DoubleOptInVerified.html');
                    $standaloneView->assignMultiple([
                        'loginUrl' => $url
                    ]);
                    // prepare the body of success message and redirect to the login page
                    $body = new Stream('php://temp', 'rw');
                    $body->write($standaloneView->render());
                    return (new Response())
                        ->withHeader('Refresh', '2; url='.$url )
                        ->withBody($body)
                        ->withStatus(200);
                } else {
                    // proceed with excception if no hash is present
                    $errorResponse = GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
                        $GLOBALS['TYPO3_REQUEST'],
                        'Not Found',
                        ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
                    );
                    throw new ImmediateResponseException($errorResponse);
                }
            }
        }

        return $handler->handle($request);
    }

    /**
     * Login with the current user
     * 
     * @param TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user
     */
    public function userLogin($user)
    {
        // accessing typoscript settings
        $configurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
        $configurationManager->getDefaultBackendStoragePid(); 
        $extbaseFrameworkConfiguration = $configurationManager->getTypoScriptSetup();
        // this data is the pid of the login page. this could be changed from be constant editor
        $loginPage = $extbaseFrameworkConfiguration['plugin.']['tx_user_account_ext.']['settings.']['loginPageId'];

        // we need user data array for setting user session
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $result = $queryBuilder
           ->select('*')
           ->from('fe_users')
           ->where(
              $queryBuilder->expr()->eq('uid', $user->getUid())
           )
           ->execute()->fetchAll();

        $userDataArray = $result[0];

        // Authenticate the user and start user session
        $feAuth = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $feAuth->user = $user;
        $feAuth->id = md5($user->getUid());
        $feAuth->createUserSession($userDataArray);
        //Set session cookie
        $cookie = new Cookie(
            'fe_typo_user',
            $feAuth->id,
            $GLOBALS['EXEC_TIME'] + 86400,
            '/',
            $_SERVER['SERVER_NAME'],
            false,
            true,
            false,
            Cookie::SAMESITE_LAX
        );
        header('Set-Cookie: ' . $cookie->__toString(), false);

        // create the redirect link of login page from the given page uid
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $uri = $cObj->typolink_URL([
            'parameter' => (int) $loginPage
        ]);

        return $uri;
    }
}
