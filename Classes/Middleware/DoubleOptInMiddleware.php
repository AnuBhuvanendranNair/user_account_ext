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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

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
            if (array_key_exists('eID', $queryParams)) {
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
                    // compile a html view and report activation status
                    $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
                    $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName('EXT:user_account_ext/Resources/Private/Template/Forms/Email/') . 'DoubleOptInVerified.html');
                    $response->getBody()->write($standaloneView->render());
                    return $response;
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
}
