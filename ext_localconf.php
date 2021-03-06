<?php
defined('TYPO3_MODE') || die();

if (!defined('TYPO3_COMPOSER_MODE') || !TYPO3_COMPOSER_MODE) {
    require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('auth0') . 'Libraries/vendor/autoload.php';
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Bitmotion.' . $_EXTKEY,
    'LoginForm',
    [
        'Login' => 'form, login, logout',
    ],
    // non-cacheable actions
    [
        'Login' => 'form, login, logout',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['auth0_loginform']['auth0'] =
    \Bitmotion\Auth0\Hooks\PageLayoutViewHook::class . '->getSummary';



$configuration = new \Bitmotion\Auth0\Domain\Model\Dto\EmAuth0Configuration();
if ($configuration->getEnableBackendLogin() === true) {
    $subtypes = 'authUserFE,getUserFE,getUserBE,authUserBE';
    if (TYPO3_MODE === 'BE') {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['backend']['loginProviders'][1526966635] = [
            'provider' => \Bitmotion\Auth0\LoginProvider\Auth0Provider::class,
            'sorting' => 25,
            'icon-class' => 'fa-sign-in',
            'label' => 'LLL:EXT:auth0/Resources/Private/Language/locallang.xlf:backendLogin.switch.label'
        ];
    }
} else {
    $subtypes = 'authUserFE,getUserFE';
}

$highestPriority = 0;

if (is_array($GLOBALS['T3_SERVICES']['auth'])) {
    foreach ($GLOBALS['T3_SERVICES']['auth'] as $service) {
        if ($service['priority'] > $highestPriority) {
            $highestPriority = $service['priority'];
        }
    }
}

$overrulingPriority = $highestPriority + 10;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'auth0',
    'auth',
    \Bitmotion\Auth0\Service\AuthenticationService::class,
    [
        'title' => 'Auth0 Authorization',
        'description' => 'Authorizes the auth0 user to access protected pages.',
        'subtype' => $subtypes,
        'available' => true,
        'priority' => $overrulingPriority,
        'quality' => $overrulingPriority,
        'os' => '',
        'exec' => '',
        'className' => \Bitmotion\Auth0\Service\AuthenticationService::class
    ]
);


// Add CommandController
if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][$_EXTKEY] =
        \Bitmotion\Auth0\Command\CleanUpCommandController::class;
}