<?php
declare(strict_types=1);

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;

/** @var Router $router */

// Public API routes (no auth required)
$router->group('/api', function (Router $router) {

    // Auth routes
    $router->post('/auth/register', 'App\Controllers\AuthController@register');
    $router->post('/auth/login', 'App\Controllers\AuthController@login');

    // Health check
    $router->get('/health', 'App\Controllers\HealthController@check');

}, [RateLimitMiddleware::class]);

// Bot API routes - Telegram mirror
// All methods support both POST and GET (Telegram clients use either)
$router->group('/bot', function (Router $router) {

    // ==================== GET ME / BOT SETTINGS ====================
    $router->match(['GET', 'POST'], '/{token}/getMe', 'App\Controllers\BotApi\BotSettingsController@getMe');
    $router->match(['GET', 'POST'], '/{token}/logOut', 'App\Controllers\BotApi\BotSettingsController@logOut');
    $router->match(['GET', 'POST'], '/{token}/close', 'App\Controllers\BotApi\BotSettingsController@close');

    // ==================== BOT NAME / DESCRIPTION ====================
    $router->match(['GET', 'POST'], '/{token}/getMyName', 'App\Controllers\BotApi\BotSettingsController@getMyName');
    $router->match(['GET', 'POST'], '/{token}/setMyName', 'App\Controllers\BotApi\BotSettingsController@setMyName');
    $router->match(['GET', 'POST'], '/{token}/getMyDescription', 'App\Controllers\BotApi\BotSettingsController@getMyDescription');
    $router->match(['GET', 'POST'], '/{token}/setMyDescription', 'App\Controllers\BotApi\BotSettingsController@setMyDescription');
    $router->match(['GET', 'POST'], '/{token}/getMyShortDescription', 'App\Controllers\BotApi\BotSettingsController@getMyShortDescription');
    $router->match(['GET', 'POST'], '/{token}/setMyShortDescription', 'App\Controllers\BotApi\BotSettingsController@setMyShortDescription');

    // ==================== BOT COMMANDS ====================
    $router->match(['GET', 'POST'], '/{token}/getMyCommands', 'App\Controllers\BotApi\BotSettingsController@getMyCommands');
    $router->match(['GET', 'POST'], '/{token}/setMyCommands', 'App\Controllers\BotApi\BotSettingsController@setMyCommands');
    $router->match(['GET', 'POST'], '/{token}/deleteMyCommands', 'App\Controllers\BotApi\BotSettingsController@deleteMyCommands');

    // ==================== BOT ADMIN RIGHTS ====================
    $router->match(['GET', 'POST'], '/{token}/getMyDefaultAdministratorRights', 'App\Controllers\BotApi\BotSettingsController@getMyDefaultAdministratorRights');
    $router->match(['GET', 'POST'], '/{token}/setMyDefaultAdministratorRights', 'App\Controllers\BotApi\BotSettingsController@setMyDefaultAdministratorRights');

    // ==================== UPDATES ====================
    $router->match(['GET', 'POST'], '/{token}/getUpdates', 'App\Controllers\BotApi\UpdateController@getUpdates');
    $router->match(['GET', 'POST'], '/{token}/setWebhook', 'App\Controllers\BotApi\UpdateController@setWebhook');
    $router->match(['GET', 'POST'], '/{token}/deleteWebhook', 'App\Controllers\BotApi\UpdateController@deleteWebhook');
    $router->match(['GET', 'POST'], '/{token}/getWebhookInfo', 'App\Controllers\BotApi\UpdateController@getWebhookInfo');

    // ==================== SEND MESSAGES ====================
    $router->match(['GET', 'POST'], '/{token}/sendMessage', 'App\Controllers\BotApi\MessagingController@sendMessage');
    $router->match(['GET', 'POST'], '/{token}/sendPhoto', 'App\Controllers\BotApi\MessagingController@sendPhoto');
    $router->match(['GET', 'POST'], '/{token}/sendAudio', 'App\Controllers\BotApi\MessagingController@sendAudio');
    $router->match(['GET', 'POST'], '/{token}/sendDocument', 'App\Controllers\BotApi\MessagingController@sendDocument');
    $router->match(['GET', 'POST'], '/{token}/sendVideo', 'App\Controllers\BotApi\MessagingController@sendVideo');
    $router->match(['GET', 'POST'], '/{token}/sendAnimation', 'App\Controllers\BotApi\MessagingController@sendAnimation');
    $router->match(['GET', 'POST'], '/{token}/sendVoice', 'App\Controllers\BotApi\MessagingController@sendVoice');
    $router->match(['GET', 'POST'], '/{token}/sendVideoNote', 'App\Controllers\BotApi\MessagingController@sendVideoNote');
    $router->match(['GET', 'POST'], '/{token}/sendLocation', 'App\Controllers\BotApi\MessagingController@sendLocation');
    $router->match(['GET', 'POST'], '/{token}/sendVenue', 'App\Controllers\BotApi\MessagingController@sendVenue');
    $router->match(['GET', 'POST'], '/{token}/sendContact', 'App\Controllers\BotApi\MessagingController@sendContact');
    $router->match(['GET', 'POST'], '/{token}/sendDice', 'App\Controllers\BotApi\MessagingController@sendDice');
    $router->match(['GET', 'POST'], '/{token}/sendPoll', 'App\Controllers\BotApi\MessagingController@sendPoll');
    $router->match(['GET', 'POST'], '/{token}/sendChecklist', 'App\Controllers\BotApi\MessagingController@sendChecklist');
    $router->match(['GET', 'POST'], '/{token}/sendChatAction', 'App\Controllers\BotApi\MessagingController@sendChatAction');
    $router->match(['GET', 'POST'], '/{token}/sendMediaGroup', 'App\Controllers\BotApi\MessagingController@sendMediaGroup');
    $router->match(['GET', 'POST'], '/{token}/sendMessageDraft', 'App\Controllers\BotApi\MessagingController@sendMessageDraft');
    $router->match(['GET', 'POST'], '/{token}/sendRichMessage', 'App\Controllers\BotApi\MessagingController@sendRichMessage');
    $router->match(['GET', 'POST'], '/{token}/sendSticker', 'App\Controllers\BotApi\MessagingController@sendSticker');
    $router->match(['GET', 'POST'], '/{token}/sendLivePhoto', 'App\Controllers\BotApi\MessagingController@sendLivePhoto');
    $router->match(['GET', 'POST'], '/{token}/sendPaidMedia', 'App\Controllers\BotApi\MessagingController@sendPaidMedia');
    $router->match(['GET', 'POST'], '/{token}/sendRichMessageDraft', 'App\Controllers\BotApi\MessagingController@sendRichMessageDraft');

    // ==================== FORWARD & COPY ====================
    $router->match(['GET', 'POST'], '/{token}/forwardMessage', 'App\Controllers\BotApi\MessagingController@forwardMessage');
    $router->match(['GET', 'POST'], '/{token}/forwardMessages', 'App\Controllers\BotApi\MessagingController@forwardMessages');
    $router->match(['GET', 'POST'], '/{token}/copyMessage', 'App\Controllers\BotApi\MessagingController@copyMessage');
    $router->match(['GET', 'POST'], '/{token}/copyMessages', 'App\Controllers\BotApi\MessagingController@copyMessages');

    // ==================== EDIT MESSAGES ====================
    $router->match(['GET', 'POST'], '/{token}/editMessageText', 'App\Controllers\BotApi\MessagingController@editMessageText');
    $router->match(['GET', 'POST'], '/{token}/editMessageCaption', 'App\Controllers\BotApi\MessagingController@editMessageCaption');
    $router->match(['GET', 'POST'], '/{token}/editMessageMedia', 'App\Controllers\BotApi\MessagingController@editMessageMedia');
    $router->match(['GET', 'POST'], '/{token}/editMessageReplyMarkup', 'App\Controllers\BotApi\MessagingController@editMessageReplyMarkup');
    $router->match(['GET', 'POST'], '/{token}/editMessageLiveLocation', 'App\Controllers\BotApi\MessagingController@editMessageLiveLocation');
    $router->match(['GET', 'POST'], '/{token}/stopMessageLiveLocation', 'App\Controllers\BotApi\MessagingController@stopMessageLiveLocation');
    $router->match(['GET', 'POST'], '/{token}/editMessageChecklist', 'App\Controllers\BotApi\MessagingController@editMessageChecklist');
    $router->match(['GET', 'POST'], '/{token}/stopPoll', 'App\Controllers\BotApi\MessagingController@stopPoll');

    // ==================== DELETE MESSAGES ====================
    $router->match(['GET', 'POST'], '/{token}/deleteMessage', 'App\Controllers\BotApi\MessagingController@deleteMessage');
    $router->match(['GET', 'POST'], '/{token}/deleteMessages', 'App\Controllers\BotApi\MessagingController@deleteMessages');

    // ==================== REACTIONS ====================
    $router->match(['GET', 'POST'], '/{token}/setMessageReaction', 'App\Controllers\BotApi\MessagingController@setMessageReaction');
    $router->match(['GET', 'POST'], '/{token}/deleteMessageReaction', 'App\Controllers\BotApi\MessagingController@deleteMessageReaction');
    $router->match(['GET', 'POST'], '/{token}/deleteAllMessageReactions', 'App\Controllers\BotApi\MessagingController@deleteAllMessageReactions');

    // ==================== CHAT METHODS ====================
    $router->match(['GET', 'POST'], '/{token}/getChat', 'App\Controllers\BotApi\ChatController@getChat');
    $router->match(['GET', 'POST'], '/{token}/getChatAdministrators', 'App\Controllers\BotApi\ChatController@getChatAdministrators');
    $router->match(['GET', 'POST'], '/{token}/getChatMember', 'App\Controllers\BotApi\ChatController@getChatMember');
    $router->match(['GET', 'POST'], '/{token}/getChatMemberCount', 'App\Controllers\BotApi\ChatController@getChatMemberCount');
    $router->match(['GET', 'POST'], '/{token}/setChatTitle', 'App\Controllers\BotApi\ChatController@setChatTitle');
    $router->match(['GET', 'POST'], '/{token}/setChatDescription', 'App\Controllers\BotApi\ChatController@setChatDescription');
    $router->match(['GET', 'POST'], '/{token}/setChatPhoto', 'App\Controllers\BotApi\ChatController@setChatPhoto');
    $router->match(['GET', 'POST'], '/{token}/deleteChatPhoto', 'App\Controllers\BotApi\ChatController@deleteChatPhoto');
    $router->match(['GET', 'POST'], '/{token}/setChatPermissions', 'App\Controllers\BotApi\ChatController@setChatPermissions');
    $router->match(['GET', 'POST'], '/{token}/setChatAdministratorCustomTitle', 'App\Controllers\BotApi\ChatController@setChatAdministratorCustomTitle');
    $router->match(['GET', 'POST'], '/{token}/setChatMenuButton', 'App\Controllers\BotApi\ChatController@setChatMenuButton');
    $router->match(['GET', 'POST'], '/{token}/getChatMenuButton', 'App\Controllers\BotApi\ChatController@getChatMenuButton');
    $router->match(['GET', 'POST'], '/{token}/exportChatInviteLink', 'App\Controllers\BotApi\ChatController@exportChatInviteLink');
    $router->match(['GET', 'POST'], '/{token}/createChatInviteLink', 'App\Controllers\BotApi\ChatController@createChatInviteLink');
    $router->match(['GET', 'POST'], '/{token}/editChatInviteLink', 'App\Controllers\BotApi\ChatController@editChatInviteLink');
    $router->match(['GET', 'POST'], '/{token}/revokeChatInviteLink', 'App\Controllers\BotApi\ChatController@revokeChatInviteLink');
    $router->match(['GET', 'POST'], '/{token}/approveChatJoinRequest', 'App\Controllers\BotApi\ChatController@approveChatJoinRequest');
    $router->match(['GET', 'POST'], '/{token}/declineChatJoinRequest', 'App\Controllers\BotApi\ChatController@declineChatJoinRequest');
    $router->match(['GET', 'POST'], '/{token}/leaveChat', 'App\Controllers\BotApi\ChatController@leaveChat');
    $router->match(['GET', 'POST'], '/{token}/setChatStickerSet', 'App\Controllers\BotApi\ChatController@setChatStickerSet');
    $router->match(['GET', 'POST'], '/{token}/deleteChatStickerSet', 'App\Controllers\BotApi\ChatController@deleteChatStickerSet');

    // ==================== BAN / RESTRICT ====================
    $router->match(['GET', 'POST'], '/{token}/banChatMember', 'App\Controllers\BotApi\ChatController@banChatMember');
    $router->match(['GET', 'POST'], '/{token}/unbanChatMember', 'App\Controllers\BotApi\ChatController@unbanChatMember');
    $router->match(['GET', 'POST'], '/{token}/restrictChatMember', 'App\Controllers\BotApi\ChatController@restrictChatMember');
    $router->match(['GET', 'POST'], '/{token}/promoteChatMember', 'App\Controllers\BotApi\ChatController@promoteChatMember');
    $router->match(['GET', 'POST'], '/{token}/banChatSenderChat', 'App\Controllers\BotApi\ChatController@banChatSenderChat');
    $router->match(['GET', 'POST'], '/{token}/unbanChatSenderChat', 'App\Controllers\BotApi\ChatController@unbanChatSenderChat');

    // ==================== PIN ====================
    $router->match(['GET', 'POST'], '/{token}/pinChatMessage', 'App\Controllers\BotApi\ChatController@pinChatMessage');
    $router->match(['GET', 'POST'], '/{token}/unpinChatMessage', 'App\Controllers\BotApi\ChatController@unpinChatMessage');
    $router->match(['GET', 'POST'], '/{token}/unpinAllChatMessages', 'App\Controllers\BotApi\ChatController@unpinAllChatMessages');

    // ==================== USER METHODS ====================
    $router->match(['GET', 'POST'], '/{token}/getUserProfilePhotos', 'App\Controllers\BotApi\UserController@getUserProfilePhotos');
    $router->match(['GET', 'POST'], '/{token}/getUserProfileAudios', 'App\Controllers\BotApi\UserController@getUserProfileAudios');
    $router->match(['GET', 'POST'], '/{token}/setUserEmojiStatus', 'App\Controllers\BotApi\UserController@setUserEmojiStatus');
    $router->match(['GET', 'POST'], '/{token}/removeUserVerification', 'App\Controllers\BotApi\UserController@removeUserVerification');
    $router->match(['GET', 'POST'], '/{token}/removeChatVerification', 'App\Controllers\BotApi\UserController@removeChatVerification');
    $router->match(['GET', 'POST'], '/{token}/verifyUser', 'App\Controllers\BotApi\UserController@verifyUser');
    $router->match(['GET', 'POST'], '/{token}/verifyChat', 'App\Controllers\BotApi\UserController@verifyChat');
    $router->match(['GET', 'POST'], '/{token}/setMyProfilePhoto', 'App\Controllers\BotApi\UserController@setMyProfilePhoto');
    $router->match(['GET', 'POST'], '/{token}/removeMyProfilePhoto', 'App\Controllers\BotApi\UserController@removeMyProfilePhoto');

    // ==================== CALLBACK ====================
    $router->match(['GET', 'POST'], '/{token}/answerCallbackQuery', 'App\Controllers\BotApi\CallbackController@answerCallbackQuery');

    // ==================== INLINE MODE ====================
    $router->match(['GET', 'POST'], '/{token}/answerInlineQuery', 'App\Controllers\BotApi\InlineController@answerInlineQuery');
    $router->match(['GET', 'POST'], '/{token}/savePreparedInlineMessage', 'App\Controllers\BotApi\InlineController@savePreparedInlineMessage');
    $router->match(['GET', 'POST'], '/{token}/savePreparedKeyboardButton', 'App\Controllers\BotApi\InlineController@savePreparedKeyboardButton');
    $router->match(['GET', 'POST'], '/{token}/answerWebAppQuery', 'App\Controllers\BotApi\InlineController@answerWebAppQuery');

    // ==================== FILES ====================
    $router->match(['GET', 'POST'], '/{token}/getFile', 'App\Controllers\BotApi\MediaController@getFile');

    // ==================== FORUM ====================
    $router->match(['GET', 'POST'], '/{token}/createForumTopic', 'App\Controllers\BotApi\ForumController@createForumTopic');
    $router->match(['GET', 'POST'], '/{token}/editForumTopic', 'App\Controllers\BotApi\ForumController@editForumTopic');
    $router->match(['GET', 'POST'], '/{token}/closeForumTopic', 'App\Controllers\BotApi\ForumController@closeForumTopic');
    $router->match(['GET', 'POST'], '/{token}/reopenForumTopic', 'App\Controllers\BotApi\ForumController@reopenForumTopic');
    $router->match(['GET', 'POST'], '/{token}/deleteForumTopic', 'App\Controllers\BotApi\ForumController@deleteForumTopic');
    $router->match(['GET', 'POST'], '/{token}/unpinAllForumTopicMessages', 'App\Controllers\BotApi\ForumController@unpinAllForumTopicMessages');
    $router->match(['GET', 'POST'], '/{token}/getForumTopicIconStickers', 'App\Controllers\BotApi\ForumController@getForumTopicIconStickers');
    $router->match(['GET', 'POST'], '/{token}/hideGeneralForumTopic', 'App\Controllers\BotApi\ForumController@hideGeneralForumTopic');
    $router->match(['GET', 'POST'], '/{token}/unhideGeneralForumTopic', 'App\Controllers\BotApi\ForumController@unhideGeneralForumTopic');
    $router->match(['GET', 'POST'], '/{token}/editGeneralForumTopic', 'App\Controllers\BotApi\ForumController@editGeneralForumTopic');
    $router->match(['GET', 'POST'], '/{token}/closeGeneralForumTopic', 'App\Controllers\BotApi\ForumController@closeGeneralForumTopic');
    $router->match(['GET', 'POST'], '/{token}/reopenGeneralForumTopic', 'App\Controllers\BotApi\ForumController@reopenGeneralForumTopic');
    $router->match(['GET', 'POST'], '/{token}/unpinAllGeneralForumTopicMessages', 'App\Controllers\BotApi\ForumController@unpinAllGeneralForumTopicMessages');

    // ==================== STICKERS ====================
    $router->match(['GET', 'POST'], '/{token}/getStickerSet', 'App\Controllers\BotApi\StickerController@getStickerSet');
    $router->match(['GET', 'POST'], '/{token}/getCustomEmojiStickers', 'App\Controllers\BotApi\StickerController@getCustomEmojiStickers');
    $router->match(['GET', 'POST'], '/{token}/uploadStickerFile', 'App\Controllers\BotApi\StickerController@uploadStickerFile');
    $router->match(['GET', 'POST'], '/{token}/createNewStickerSet', 'App\Controllers\BotApi\StickerController@createNewStickerSet');
    $router->match(['GET', 'POST'], '/{token}/addStickerToSet', 'App\Controllers\BotApi\StickerController@addStickerToSet');
    $router->match(['GET', 'POST'], '/{token}/setStickerPositionInSet', 'App\Controllers\BotApi\StickerController@setStickerPositionInSet');
    $router->match(['GET', 'POST'], '/{token}/deleteStickerFromSet', 'App\Controllers\BotApi\StickerController@deleteStickerFromSet');
    $router->match(['GET', 'POST'], '/{token}/replaceStickerInSet', 'App\Controllers\BotApi\StickerController@replaceStickerInSet');
    $router->match(['GET', 'POST'], '/{token}/setStickerSetTitle', 'App\Controllers\BotApi\StickerController@setStickerSetTitle');
    $router->match(['GET', 'POST'], '/{token}/setStickerSetThumbnail', 'App\Controllers\BotApi\StickerController@setStickerSetThumbnail');
    $router->match(['GET', 'POST'], '/{token}/setCustomEmojiStickerSetThumbnail', 'App\Controllers\BotApi\StickerController@setCustomEmojiStickerSetThumbnail');
    $router->match(['GET', 'POST'], '/{token}/setStickerEmojiList', 'App\Controllers\BotApi\StickerController@setStickerEmojiList');
    $router->match(['GET', 'POST'], '/{token}/setStickerKeywords', 'App\Controllers\BotApi\StickerController@setStickerKeywords');
    $router->match(['GET', 'POST'], '/{token}/setStickerMaskPosition', 'App\Controllers\BotApi\StickerController@setStickerMaskPosition');

    // ==================== PAYMENTS ====================
    $router->match(['GET', 'POST'], '/{token}/sendInvoice', 'App\Controllers\BotApi\PaymentController@sendInvoice');
    $router->match(['GET', 'POST'], '/{token}/createInvoiceLink', 'App\Controllers\BotApi\PaymentController@createInvoiceLink');
    $router->match(['GET', 'POST'], '/{token}/answerShippingQuery', 'App\Controllers\BotApi\PaymentController@answerShippingQuery');
    $router->match(['GET', 'POST'], '/{token}/answerPreCheckoutQuery', 'App\Controllers\BotApi\PaymentController@answerPreCheckoutQuery');
    $router->match(['GET', 'POST'], '/{token}/getStarTransactions', 'App\Controllers\BotApi\PaymentController@getStarTransactions');
    $router->match(['GET', 'POST'], '/{token}/refundStarPayment', 'App\Controllers\BotApi\PaymentController@refundStarPayment');

    // ==================== GAMES ====================
    $router->match(['GET', 'POST'], '/{token}/sendGame', 'App\Controllers\BotApi\GameController@sendGame');
    $router->match(['GET', 'POST'], '/{token}/setGameScore', 'App\Controllers\BotApi\GameController@setGameScore');
    $router->match(['GET', 'POST'], '/{token}/getGameHighScores', 'App\Controllers\BotApi\GameController@getGameHighScores');

    // ==================== PASSPORT ====================
    $router->match(['GET', 'POST'], '/{token}/setPassportDataErrors', 'App\Controllers\BotApi\PassportController@setPassportDataErrors');

    // ==================== STARS & GIFT ====================
    $router->match(['GET', 'POST'], '/{token}/getMyStarBalance', 'App\Controllers\BotApi\StarsController@getMyStarBalance');
    $router->match(['GET', 'POST'], '/{token}/sendGift', 'App\Controllers\BotApi\StarsController@sendGift');
    $router->match(['GET', 'POST'], '/{token}/getAvailableGifts', 'App\Controllers\BotApi\StarsController@getAvailableGifts');
    $router->match(['GET', 'POST'], '/{token}/getChatGifts', 'App\Controllers\BotApi\StarsController@getChatGifts');
    $router->match(['GET', 'POST'], '/{token}/getUserGifts', 'App\Controllers\BotApi\StarsController@getUserGifts');
    $router->match(['GET', 'POST'], '/{token}/convertGiftToStars', 'App\Controllers\BotApi\StarsController@convertGiftToStars');
    $router->match(['GET', 'POST'], '/{token}/transferGift', 'App\Controllers\BotApi\StarsController@transferGift');
    $router->match(['GET', 'POST'], '/{token}/upgradeGift', 'App\Controllers\BotApi\StarsController@upgradeGift');
    $router->match(['GET', 'POST'], '/{token}/giftPremiumSubscription', 'App\Controllers\BotApi\StarsController@giftPremiumSubscription');
    $router->match(['GET', 'POST'], '/{token}/editUserStarSubscription', 'App\Controllers\BotApi\StarsController@editUserStarSubscription');

    // ==================== BUSINESS ====================
    $router->match(['GET', 'POST'], '/{token}/setBusinessAccountName', 'App\Controllers\BotApi\BusinessController@setBusinessAccountName');
    $router->match(['GET', 'POST'], '/{token}/setBusinessAccountUsername', 'App\Controllers\BotApi\BusinessController@setBusinessAccountUsername');
    $router->match(['GET', 'POST'], '/{token}/setBusinessAccountBio', 'App\Controllers\BotApi\BusinessController@setBusinessAccountBio');
    $router->match(['GET', 'POST'], '/{token}/setBusinessAccountProfilePhoto', 'App\Controllers\BotApi\BusinessController@setBusinessAccountProfilePhoto');
    $router->match(['GET', 'POST'], '/{token}/removeBusinessAccountProfilePhoto', 'App\Controllers\BotApi\BusinessController@removeBusinessAccountProfilePhoto');
    $router->match(['GET', 'POST'], '/{token}/setBusinessAccountGiftSettings', 'App\Controllers\BotApi\BusinessController@setBusinessAccountGiftSettings');
    $router->match(['GET', 'POST'], '/{token}/getBusinessConnection', 'App\Controllers\BotApi\BusinessController@getBusinessConnection');
    $router->match(['GET', 'POST'], '/{token}/readBusinessMessage', 'App\Controllers\BotApi\BusinessController@readBusinessMessage');
    $router->match(['GET', 'POST'], '/{token}/deleteBusinessMessages', 'App\Controllers\BotApi\BusinessController@deleteBusinessMessages');
    $router->match(['GET', 'POST'], '/{token}/getBusinessAccountStarBalance', 'App\Controllers\BotApi\BusinessController@getBusinessAccountStarBalance');
    $router->match(['GET', 'POST'], '/{token}/transferBusinessAccountStars', 'App\Controllers\BotApi\BusinessController@transferBusinessAccountStars');
    $router->match(['GET', 'POST'], '/{token}/getBusinessAccountGifts', 'App\Controllers\BotApi\BusinessController@getBusinessAccountGifts');

    // ==================== STORIES ====================
    $router->match(['GET', 'POST'], '/{token}/postStory', 'App\Controllers\BotApi\StoriesController@postStory');
    $router->match(['GET', 'POST'], '/{token}/editStory', 'App\Controllers\BotApi\StoriesController@editStory');
    $router->match(['GET', 'POST'], '/{token}/deleteStory', 'App\Controllers\BotApi\StoriesController@deleteStory');
    $router->match(['GET', 'POST'], '/{token}/repostStory', 'App\Controllers\BotApi\StoriesController@repostStory');
    $router->match(['GET', 'POST'], '/{token}/getStoryStatistics', 'App\Controllers\BotApi\StoriesController@getStoryStatistics');
    $router->match(['GET', 'POST'], '/{token}/getUserStories', 'App\Controllers\BotApi\StoriesController@getUserStories');

    // ==================== MISCELLANEOUS ====================
    $router->match(['GET', 'POST'], '/{token}/answerChatJoinRequestQuery', 'App\Controllers\BotApi\MiscellaneousController@answerChatJoinRequestQuery');
    $router->match(['GET', 'POST'], '/{token}/answerGuestQuery', 'App\Controllers\BotApi\MiscellaneousController@answerGuestQuery');
    $router->match(['GET', 'POST'], '/{token}/approveSuggestedPost', 'App\Controllers\BotApi\MiscellaneousController@approveSuggestedPost');
    $router->match(['GET', 'POST'], '/{token}/declineSuggestedPost', 'App\Controllers\BotApi\MiscellaneousController@declineSuggestedPost');
    $router->match(['GET', 'POST'], '/{token}/createChatSubscriptionInviteLink', 'App\Controllers\BotApi\MiscellaneousController@createChatSubscriptionInviteLink');
    $router->match(['GET', 'POST'], '/{token}/editChatSubscriptionInviteLink', 'App\Controllers\BotApi\MiscellaneousController@editChatSubscriptionInviteLink');
    $router->match(['GET', 'POST'], '/{token}/deleteStickerSet', 'App\Controllers\BotApi\MiscellaneousController@deleteStickerSet');
    $router->match(['GET', 'POST'], '/{token}/getManagedBotAccessSettings', 'App\Controllers\BotApi\MiscellaneousController@getManagedBotAccessSettings');
    $router->match(['GET', 'POST'], '/{token}/setManagedBotAccessSettings', 'App\Controllers\BotApi\MiscellaneousController@setManagedBotAccessSettings');
    $router->match(['GET', 'POST'], '/{token}/getManagedBotToken', 'App\Controllers\BotApi\MiscellaneousController@getManagedBotToken');
    $router->match(['GET', 'POST'], '/{token}/replaceManagedBotToken', 'App\Controllers\BotApi\MiscellaneousController@replaceManagedBotToken');
    $router->match(['GET', 'POST'], '/{token}/setChatMemberTag', 'App\Controllers\BotApi\MiscellaneousController@setChatMemberTag');
    $router->match(['GET', 'POST'], '/{token}/getUserPersonalChatMessages', 'App\Controllers\BotApi\MiscellaneousController@getUserPersonalChatMessages');
    $router->match(['GET', 'POST'], '/{token}/getUserChatBoosts', 'App\Controllers\BotApi\MiscellaneousController@getUserChatBoosts');
    $router->match(['GET', 'POST'], '/{token}/sendChatJoinRequestWebApp', 'App\Controllers\BotApi\MiscellaneousController@sendChatJoinRequestWebApp');

}, [RateLimitMiddleware::class, AuthMiddleware::class]);
