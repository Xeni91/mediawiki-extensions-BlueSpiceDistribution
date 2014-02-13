<?php
/**
 * MediaWiki Extension: Echo
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * This program is distributed WITHOUT ANY WARRANTY.
 */

/**
 *
 * @file
 * @ingroup Extensions
 * @author Andrew Garrett
 */

# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/Echo/Echo.php" );
EOT;
	exit( 1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Echo',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Echo',
	'author' => array( 'Andrew Garrett', 'Ryan Kaldari', 'Benny Situ', 'Luke Welling' ),
	'descriptionmsg' => 'echo-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgExtensionMessagesFiles['Echo'] = $dir . 'Echo.i18n.php';
$wgExtensionMessagesFiles['EchoAliases'] = $dir . 'Echo.alias.php';

$wgAutoloadClasses['EchoHooks'] = $dir . 'Hooks.php';
$wgAutoloadClasses['EchoSubscription'] = $dir . 'model/Subscription.php';
$wgAutoloadClasses['EchoEvent'] = $dir . 'model/Event.php';
$wgAutoloadClasses['EchoNotification'] = $dir . 'model/Notification.php';
$wgAutoloadClasses['MWEchoEmailBatch'] = $dir . 'includes/EmailBatch.php';
$wgAutoloadClasses['MWDbEchoEmailBatch'] = $dir . 'includes/DbEmailBatch.php';

// Formatters
$wgAutoloadClasses['EchoNotificationFormatter'] = $dir . 'formatters/NotificationFormatter.php';
$wgAutoloadClasses['EchoBasicFormatter'] = $dir . 'formatters/BasicFormatter.php';
$wgAutoloadClasses['EchoEditFormatter'] = $dir . 'formatters/EditFormatter.php';
$wgAutoloadClasses['EchoCommentFormatter'] = $dir . 'formatters/CommentFormatter.php';
$wgAutoloadClasses['EchoArticleLinkedFormatter'] = $dir . 'formatters/ArticleLinkedFormatter.php';

// Internal stuff
$wgAutoloadClasses['EchoNotifier'] = $dir . 'Notifier.php';
$wgAutoloadClasses['EchoNotificationController'] = $dir . 'controller/NotificationController.php';
$wgAutoloadClasses['EchoDiscussionParser'] = $dir . 'includes/DiscussionParser.php';

// Job queue
$wgAutoloadClasses['EchoNotificationJob'] = $dir . 'jobs/NotificationJob.php';
$wgJobClasses['EchoNotificationJob'] = 'EchoNotificationJob';

// API
$wgAutoloadClasses['ApiEchoNotifications'] =  $dir . 'api/ApiEchoNotifications.php';
$wgAPIMetaModules['notifications'] = 'ApiEchoNotifications';

// Special page
$wgAutoloadClasses['SpecialNotifications'] = $dir . 'special/SpecialNotifications.php';
$wgSpecialPages['Notifications'] = 'SpecialNotifications';
$wgSpecialPageGroups['Notifications'] = 'users';

// Backend support
$wgAutoloadClasses['MWEchoBackend'] = $dir . 'includes/EchoBackend.php';
$wgAutoloadClasses['MWDbEchoBackend'] = $dir . 'includes/DbEchoBackend.php';

// Housekeeping hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'EchoHooks::getSchemaUpdates';
$wgHooks['GetPreferences'][] = 'EchoHooks::getPreferences';
$wgHooks['PersonalUrls'][] = 'EchoHooks::onPersonalUrls';
$wgHooks['BeforePageDisplay'][] = 'EchoHooks::beforePageDisplay';
$wgHooks['MakeGlobalVariablesScript'][] = 'EchoHooks::makeGlobalVariablesScript';
$wgHooks['UnitTestsList'][] = 'EchoHooks::getUnitTests';
$wgHooks['ResourceLoaderRegisterModules'][] = 'EchoHooks::onResourceLoaderRegisterModules';

// Extension initialization
$wgExtensionFunctions[] = 'EchoHooks::initEchoExtension';

$echoResourceTemplate = array(
	
	'localBasePath' => $dir . 'modules',
	'remoteExtPath' => 'BlueSpiceDistribution/Echo/modules',
	'group' => 'ext.echo',
);

$wgResourceModules += array(
	'ext.echo.base' => $echoResourceTemplate + array(
		'styles' => 'base/ext.echo.base.css',
		'scripts' => 'base/ext.echo.base.js',
		'dependencies' => array(
			'jquery.ui.button',
		),
		'messages' => array(
			'cancel',
			'echo-dismiss-button',
			'echo-error-preference',
			'echo-error-token',
		),
	),
	'ext.echo.overlay' => $echoResourceTemplate + array(
		'scripts' => array(
			'overlay/ext.echo.overlay.js',
		),
		'styles' => 'overlay/ext.echo.overlay.css',
		'dependencies' => array(
			'ext.echo.base',
			'mediawiki.api',
			'mediawiki.jqueryMsg',
			'jquery.badge',
			'ext.echo.icons',
		),
		'messages' => array(
			'echo-link-new',
			'echo-link',
			'echo-overlay-title',
			'echo-overlay-title-overflow',
			'echo-overlay-link',
			'echo-none',
		),
	),
	'ext.echo.special' => $echoResourceTemplate + array(
		'scripts' => array(
			'special/ext.echo.special.js',
		),
		'styles' => 'special/ext.echo.special.css',
		'dependencies' => array(
			'ext.echo.base',
			'mediawiki.api',
			'mediawiki.jqueryMsg',
			'ext.echo.icons',
		),
		'messages' => array(
			'echo-load-more-error',
			'echo-more-info',
		),
	),
	'ext.echo.icons' => $echoResourceTemplate + array(
		'styles' => 'icons/icons.css',
	),
);

/**
 * This Echo hook can be used to define users who are by default interested in
 * certain events.
 * For example, it can be used to say that users are by default interested in
 * their own user talk page being edited. In fact, that is what it is used for
 * internally.
 */
$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'EchoHooks::getDefaultNotifiedUsers';
$wgHooks['EchoGetNotificationTypes'][] = 'EchoHooks::getNotificationTypes';

// Hook appropriate events
$wgHooks['ArticleSaveComplete'][] = 'EchoHooks::onArticleSaved';
$wgHooks['AddNewAccount'][] = 'EchoHooks::onAccountCreated';
$wgHooks['ArticleRollbackComplete'][] = 'EchoHooks::onRollbackComplete';

// Disable ordinary user talk page email notifications
$wgHooks['AbortEmailNotification'][] = 'EchoHooks::disableStandUserTalkEnotif';
$wgHooks['UpdateUserMailerFormattedPageStatus'][] = 'EchoHooks::disableStandUserTalkEnotif';
// Disable the yellow bar of death
$wgHooks['ArticleEditUpdateNewTalk'][] = 'EchoHooks::abortNewTalkNotification';
$wgHooks['LinksUpdateAfterInsert'][] = 'EchoHooks::onLinksUpdateAfterInsert';

// Configuration

// The name of the backend to use for Echo, eg, Db, Redis, Zeromq
$wgEchoBackendName = 'Db';

/**
 * The backend object
 * @var MWEchoBackend
 */
$wgEchoBackend = null;

// Whether to turn on email batch function
$wgEchoEnableEmailBatch = true;

// Show a 'Notifications' link with badge in the user toolbar at the top of the page.
// Otherwise, only show a badge next to the username.
$wgEchoShowFullNotificationsLink = false;

// URL for more information about the Echo notification system
$wgEchoHelpPage = '//www.mediawiki.org/wiki/Special:MyLanguage/Help:Extension:Echo';

// Whether to use job queue to process web and email notifications, bypass the queue for now
// since it's taking more than an hour to run in mediawiki.org, this is not acceptable for the
// purpose of testing notification.
// Todo - Abstract this into classes like: JobQueueNone, JobQueueMySQL, JobQueueRedis
$wgEchoUseJobQueue = false;

// The organization address, the value should be defined in LocalSettings.php
$wgEchoEmailFooterAddress = '';

// The max notification count showed in badge
$wgEchoMaxNotificationCount = 99;

// Define which output formats are available for each notification category
$wgEchoDefaultNotificationTypes = array(
	'all' => array(
		'web' => true,
		'email' => true,
	),
);

// Definitions of the different types of notification delivery that are possible.
// Each definition consists of a class name and a function name.
// See also: EchoNotificationController class.
$wgEchoNotifiers = array(
	'web' => array( 'EchoNotifier', 'notifyWithNotification' ), // web-based notification
	'email' => array( 'EchoNotifier', 'notifyWithEmail' ),
);

// Define the categories that notifications can belong to. Categories can be
// assigned the following parameters: priority, nodismiss, and usergroups. All
// parameters are optional.
// If a notifications type doesn't have a category parameter, it is
// automatically assigned to the 'other' category which is lowest priority and
// has no preferences or dismissibility.
// The priority parameter controls the order in which notifications are
// displayed in preferences and batch emails. Priority ranges from 1 to 10. If
// the priority is not specified, it defaults to 10, which is the lowest.
// The usergroups param specifies an array of usergroups eligible to recieve the
// notifications in the category. If no usergroups parameter is specified, all
// groups are eligible.
// The nodismiss parameter disables the dismissability of notifications in the
// category. It can either be set to an array of output formats (see
// $wgEchoNotifiers) or an array containing 'all'.
$wgEchoNotificationCategories = array(
	'system' => array(
		'priority' => 9,
		'no-dismiss' => array( 'all' ),
	),
	'other' => array(
		'no-dismiss' => array( 'all' ),
	),
	'edit-user-talk' => array(
		'priority' => 1,
		'no-dismiss' => array( 'web' ),
	),
	'reverted' => array(
		'priority' => 9,
	),
	'article-linked' => array(
		'priority' => 5,
	),
	'mention' => array(
		'priority' => 4,
	),
);

// Definitions of the notification event types built into Echo.
// If formatter-class isn't specified, defaults to EchoBasicFormatter.
$wgEchoNotifications = array(
	'welcome' => array(
		'category' => 'system',
		'group' => 'positive',
		'title-message' => 'notification-new-user',
		'title-params' => array( 'agent' ),
		'payload' => array( 'welcome' ),
		'icon' => 'w',
	),
	'edit-user-talk' => array(
		'category' => 'edit-user-talk',
		'group' => 'interactive',
		'formatter-class' => 'EchoEditFormatter',
		'title-message' => 'notification-edit-talk-page2',
		'title-params' => array( 'agent', 'user' ),
		'payload' => array( 'summary' ),
		'flyout-message' => 'notification-edit-talk-page-flyout2',
		'flyout-params' => array( 'agent', 'user' ),
		'email-subject-message' => 'notification-edit-talk-page-email-subject2',
		'email-body-message' => 'notification-edit-talk-page-email-body2',
		'email-body-params' => array( 'agent', 'titlelink', 'summary', 'email-footer' ),
		'email-body-batch-message' => 'notification-edit-talk-page-email-batch-body2',
		'email-body-batch-params' => array( 'agent', 'difflink', 'summary' ),
		'icon' => 'chat',
	),
	'reverted' => array(
		'category' => 'reverted',
		'group' => 'negative',
		'formatter-class' => 'EchoEditFormatter',
		'title-message' => 'notification-reverted2',
		'title-params' => array( 'agent', 'title', 'difflink', 'number' ),
		'payload' => array( 'summary' ),
		'flyout-message' => 'notification-reverted-flyout2',
		'flyout-params' => array( 'agent', 'title', 'difflink', 'number' ),
		'email-subject-message' => 'notification-reverted-email-subject2',
		'email-subject-params' => array( 'agent', 'title', 'number' ),
		'email-body-message' => 'notification-reverted-email-body2',
		'email-body-params' => array( 'agent', 'title', 'difflink', 'user', 'summary', 'email-footer', 'number' ),
		'email-body-batch-message' => 'notification-reverted-email-batch-body2',
		'email-body-batch-params' => array( 'agent', 'title', 'number' ),
		'icon' => 'revert',
	),
	'article-linked' => array(
		'category' => 'article-linked',
		'group' => 'positive',
		'formatter-class' => 'EchoArticleLinkedFormatter',
		'title-message' => 'notification-article-linked2',
		'title-params' => array( 'agent', 'title',  'title-linked' ),
		'payload' => array( 'summary' ),
		'flyout-message' => 'notification-article-linked-flyout2',
		'flyout-params' => array( 'agent', 'title',  'title-linked' ),
		'email-subject-message' => 'notification-article-linked-email-subject2',
		'email-subject-params' => array( 'title-linked' ),
		'email-body-message' => 'notification-article-linked-email-body2',
		'email-body-params' => array( 'agent', 'title', 'titlelink', 'title-linked', 'email-footer' ),
		'email-body-batch-message' => 'notification-article-linked-email-batch-body2',
		'email-body-batch-params' => array( 'agent', 'title-linked' ),
		'icon' => 'linked',
	),
	'mention' => array(
		'category' => 'mention',
		'group' => 'interactive',
		'formatter-class' => 'EchoCommentFormatter',
		'title-message' => 'notification-mention',
		'title-params' => array( 'agent', 'subject', 'title' ),
		'payload' => array( 'summary' ),
		'flyout-message' => 'notification-mention-flyout',
		'flyout-params' => array( 'agent', 'subject',  'title' ),
		'email-subject-message' => 'notification-mention-email-subject',
		'email-subject-params' => array( 'agent' ),
		'email-body-message' => 'notification-mention-email-body',
		'email-body-params' => array( 'agent', 'title', 'summary', 'subject-link', 'email-footer' ),
		'email-body-batch-message' => 'notification-mention-email-batch-body',
		'email-body-batch-params' => array( 'agent', 'title' ),
		'content-message' => 'notification-talkpage-content',
		'content-params' => array( 'commentText' ),
		'icon' => 'chat',
	)
);

// Enable notifications for all logged in users by default
$wgDefaultUserOptions['echo-notify-link'] = 'true';

// By default, send emails for each notification as they come in
$wgDefaultUserOptions['echo-email-frequency'] = 0; // HW EchoHooks::EMAIL_IMMEDIATELY;

// Set all of the events to notify by web and email by default (won't affect events that don't email)
foreach ( $wgEchoNotificationCategories as $category => $categoryData ) {
	foreach ( $wgEchoNotifiers as $notifierType => $notifierData ) {
		$wgDefaultUserOptions["echo-subscriptions-{$notifierType}-{$category}"] = true;
	}
}
// unset default email for reverted, article-linked (change them to opt-in)
$wgDefaultUserOptions['echo-subscriptions-email-reverted'] = false;
$wgDefaultUserOptions['echo-subscriptions-email-article-linked'] = false;

// Echo Configuration for EventLogging
$wgEchoConfig = array(
	'version' => '1.0',
	'eventlogging' => array (
		'Echo' => array (
			'enabled' => false,
			'revision' => 5285750
		)
	)
);
