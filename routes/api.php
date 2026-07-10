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

// Bot API routes (mirror of Telegram Bot API)
// Pattern: /api/bot{token}/{method}
$router->group('/api/bot', function (Router $router) {

    // === Core Bot Methods ===
    $router->post('/{token}/getMe', 'App\Controllers\BotApi\BotSettingsController@getMe');
    $router->post('/{token}/logOut', 'App\Controllers\BotApi\BotSettingsController@logOut');
    $router->post('/{token}/close', 'App\Controllers\BotApi\BotSettingsController@close');
    $router->post('/{token}/getMyName', 'App\Controllers\BotApi\BotSettingsController@getMyName');
    $router->post('/{token}/setMyName', 'App\Controllers\BotApi\BotSettingsController@setMyName');
    $router->post('/{token}/getMyDescription', 'App\Controllers\BotApi\BotSettingsController@getMyDescription');
    $router->post('/{token}/setMyDescription', 'App\Controllers\BotApi\BotSettingsController@setMyDescription');
    $router->post('/{token}/getMyShortDescription', 'App\Controllers\BotApi\BotSettingsController@getMyShortDescription');
    $router->post('/{token}/setMyShortDescription', 'App\Controllers\BotApi\BotSettingsController@setMyShortDescription');
    $router->post('/{token}/getMyCommands', 'App\Controllers\BotApi\BotSettingsController@getMyCommands');
    $router->post('/{token}/setMyCommands', 'App\Controllers\BotApi\BotSettingsController@setMyCommands');
    $router->post('/{token}/deleteMyCommands', 'App\Controllers\BotApi\BotSettingsController@deleteMyCommands');
    $router->post('/{token}/getMyDefaultAdministratorRights', 'App\Controllers\BotApi\BotSettingsController@getMyDefaultAdministratorRights');
    $router->post('/{token}/setMyDefaultAdministratorRights', 'App\Controllers\BotApi\BotSettingsController@setMyDefaultAdministratorRights');

    // === Update Methods ===
    $router->post('/{token}/getUpdates', 'App\Controllers\BotApi\UpdateController@getUpdates');
    $router->post('/{token}/setWebhook', 'App\Controllers\BotApi\UpdateController@setWebhook');
    $router->post('/{token}/deleteWebhook', 'App\Controllers\BotApi\UpdateController@deleteWebhook');
    $router->post('/{token}/getWebhookInfo', 'App\Controllers\BotApi\UpdateController@getWebhookInfo');

    // === Messaging Methods ===
    $router->post('/{token}/sendMessage', 'App\Controllers\BotApi\MessagingController@sendMessage');
    $router->post('/{token}/forwardMessage', 'App\Controllers\BotApi\MessagingController@forwardMessage');
    $router->post('/{token}/forwardMessages', 'App\Controllers\BotApi\MessagingController@forwardMessages');
    $router->post('/{token}/copyMessage', 'App\Controllers\BotApi\MessagingController@copyMessage');
    $router->post('/{token}/copyMessages', 'App\Controllers\BotApi\MessagingController@copyMessages');
    $router->post('/{token}/sendPhoto', 'App\Controllers\BotApi\MessagingController@sendPhoto');
    $router->post('/{token}/sendAudio', 'App\Controllers\BotApi\MessagingController@sendAudio');
    $router->post('/{token}/sendDocument', 'App\Controllers\BotApi\MessagingController@sendDocument');
    $router->post('/{token}/sendVideo', 'App\Controllers\BotApi\MessagingController@sendVideo');
    $router->post('/{token}/sendAnimation', 'App\Controllers\BotApi\MessagingController@sendAnimation');
    $router->post('/{token}/sendVoice', 'App\Controllers\BotApi\MessagingController@sendVoice');
    $router->post('/{token}/sendVideoNote', 'App\Controllers\BotApi\MessagingController@sendVideoNote');
    $router->post('/{token}/sendLocation', 'App\Controllers\BotApi\MessagingController@sendLocation');
    $router->post('/{token}/sendVenue', 'App\Controllers\BotApi\MessagingController@sendVenue');
    $router->post('/{token}/sendContact', 'App\Controllers\BotApi\MessagingController@sendContact');
    $router->post('/{token}/sendDice', 'App\Controllers\BotApi\MessagingController@sendDice');
    $router->post('/{token}/sendPoll', 'App\Controllers\BotApi\MessagingController@sendPoll');
    $router->post('/{token}/sendChecklist', 'App\Controllers\BotApi\MessagingController@sendChecklist');
    $router->post('/{token}/sendChatAction', 'App\Controllers\BotApi\MessagingController@sendChatAction');
    $router->post('/{token}/sendMediaGroup', 'App\Controllers\BotApi\MessagingController@sendMediaGroup');
    $router->post('/{token}/sendMessageDraft', 'App\Controllers\BotApi\MessagingController@sendMessageDraft');
    $router->post('/{token}/sendRichMessage', 'App\Controllers\BotApi\MessagingController@sendRichMessage');

    // === Edit Methods ===
    $router->post('/{token}/editMessageText', 'App\Controllers\BotApi\MessagingController@editMessageText');
    $router->post('/{token}/editMessageCaption', 'App\Controllers\BotApi\MessagingController@editMessageCaption');
    $router->post('/{token}/editMessageMedia', 'App\Controllers\BotApi\MessagingController@editMessageMedia');
    $router->post('/{token}/editMessageReplyMarkup', 'App\Controllers\BotApi\MessagingController@editMessageReplyMarkup');
    $router->post('/{token}/editMessageLiveLocation', 'App\Controllers\BotApi\MessagingController@editMessageLiveLocation');
    $router->post('/{token}/stopMessageLiveLocation', 'App\Controllers\BotApi\MessagingController@stopMessageLiveLocation');
    $router->post('/{token}/editMessageChecklist', 'App\Controllers\BotApi\MessagingController@editMessageChecklist');

    // === Delete Methods ===
    $router->post('/{token}/deleteMessage', 'App\Controllers\BotApi\MessagingController@deleteMessage');
    $router->post('/{token}/deleteMessages', 'App\Controllers\BotApi\MessagingController@deleteMessages');

    // === Reaction Methods ===
    $router->post('/{token}/setMessageReaction', 'App\Controllers\BotApi\MessagingController@setMessageReaction');
    $router->post('/{token}/deleteMessageReaction', 'App\Controllers\BotApi\MessagingController@deleteMessageReaction');
    $router->post('/{token}/deleteAllMessageReactions', 'App\Controllers\BotApi\MessagingController@deleteAllMessageReactions');

    // === Chat Management ===
    $router->post('/{token}/getChat', 'App\Controllers\BotApi\ChatController@getChat');
    $router->post('/{token}/getChatAdministrators', 'App\Controllers\BotApi\ChatController@getChatAdministrators');
    $router->post('/{token}/getChatMember', 'App\Controllers\BotApi\ChatController@getChatMember');
    $router->post('/{token}/getChatMemberCount', 'App\Controllers\BotApi\ChatController@getChatMemberCount');
    $router->post('/{token}/setChatTitle', 'App\Controllers\BotApi\ChatController@setChatTitle');
    $router->post('/{token}/setChatDescription', 'App\Controllers\BotApi\ChatController@setChatDescription');
    $router->post('/{token}/setChatPhoto', 'App\Controllers\BotApi\ChatController@setChatPhoto');
    $router->post('/{token}/deleteChatPhoto', 'App\Controllers\BotApi\ChatController@deleteChatPhoto');
    $router->post('/{token}/setChatPermissions', 'App\Controllers\BotApi\ChatController@setChatPermissions');
    $router->post('/{token}/setChatAdministratorCustomTitle', 'App\Controllers\BotApi\ChatController@setChatAdministratorCustomTitle');
    $router->post('/{token}/pinChatMessage', 'App\Controllers\BotApi\ChatController@pinChatMessage');
    $router->post('/{token}/unpinChatMessage', 'App\Controllers\BotApi\ChatController@unpinChatMessage');
    $router->post('/{token}/unpinAllChatMessages', 'App\Controllers\BotApi\ChatController@unpinAllChatMessages');
    $router->post('/{token}/banChatMember', 'App\Controllers\BotApi\ChatController@banChatMember');
    $router->post('/{token}/unbanChatMember', 'App\Controllers\BotApi\ChatController@unbanChatMember');
    $router->post('/{token}/restrictChatMember', 'App\Controllers\BotApi\ChatController@restrictChatMember');
    $router->post('/{token}/promoteChatMember', 'App\Controllers\BotApi\ChatController@promoteChatMember');
    $router->post('/{token}/setChatMenuButton', 'App\Controllers\BotApi\ChatController@setChatMenuButton');
    $router->post('/{token}/getChatMenuButton', 'App\Controllers\BotApi\ChatController@getChatMenuButton');
    $router->post('/{token}/exportChatInviteLink', 'App\Controllers\BotApi\ChatController@exportChatInviteLink');
    $router->post('/{token}/createChatInviteLink', 'App\Controllers\BotApi\ChatController@createChatInviteLink');
    $router->post('/{token}/editChatInviteLink', 'App\Controllers\BotApi\ChatController@editChatInviteLink');
    $router->post('/{token}/revokeChatInviteLink', 'App\Controllers\BotApi\ChatController@revokeChatInviteLink');
    $router->post('/{token}/approveChatJoinRequest', 'App\Controllers\BotApi\ChatController@approveChatJoinRequest');
    $router->post('/{token}/declineChatJoinRequest', 'App\Controllers\BotApi\ChatController@declineChatJoinRequest');
    $router->post('/{token}/leaveChat', 'App\Controllers\BotApi\ChatController@leaveChat');
    $router->post('/{token}/setChatStickerSet', 'App\Controllers\BotApi\ChatController@setChatStickerSet');
    $router->post('/{token}/deleteChatStickerSet', 'App\Controllers\BotApi\ChatController@deleteChatStickerSet');

    // === User Methods ===
    $router->post('/{token}/getUserProfilePhotos', 'App\Controllers\BotApi\UserController@getUserProfilePhotos');
    $router->post('/{token}/getUserProfileAudios', 'App\Controllers\BotApi\UserController@getUserProfileAudios');
    $router->post('/{token}/setUserEmojiStatus', 'App\Controllers\BotApi\UserController@setUserEmojiStatus');
    $router->post('/{token}/removeUserVerification', 'App\Controllers\BotApi\UserController@removeUserVerification');
    $router->post('/{token}/removeChatVerification', 'App\Controllers\BotApi\UserController@removeChatVerification');
    $router->post('/{token}/verifyUser', 'App\Controllers\BotApi\UserController@verifyUser');
    $router->post('/{token}/verifyChat', 'App\Controllers\BotApi\UserController@verifyChat');
    $router->post('/{token}/setMyProfilePhoto', 'App\Controllers\BotApi\UserController@setMyProfilePhoto');
    $router->post('/{token}/removeMyProfilePhoto', 'App\Controllers\BotApi\UserController@removeMyProfilePhoto');

    // === Callback ===
    $router->post('/{token}/answerCallbackQuery', 'App\Controllers\BotApi\CallbackController@answerCallbackQuery');

    // === Inline ===
    $router->post('/{token}/answerInlineQuery', 'App\Controllers\BotApi\InlineController@answerInlineQuery');
    $router->post('/{token}/savePreparedInlineMessage', 'App\Controllers\BotApi\InlineController@savePreparedInlineMessage');
    $router->post('/{token}/savePreparedKeyboardButton', 'App\Controllers\BotApi\InlineController@savePreparedKeyboardButton');
    $router->post('/{token}/answerWebAppQuery', 'App\Controllers\BotApi\InlineController@answerWebAppQuery');

    // === Files ===
    $router->post('/{token}/getFile', 'App\Controllers\BotApi\MediaController@getFile');

    // === Forum ===
    $router->post('/{token}/createForumTopic', 'App\Controllers\BotApi\ForumController@createForumTopic');
    $router->post('/{token}/editForumTopic', 'App\Controllers\BotApi\ForumController@editForumTopic');
    $router->post('/{token}/closeForumTopic', 'App\Controllers\BotApi\ForumController@closeForumTopic');
    $router->post('/{token}/reopenForumTopic', 'App\Controllers\BotApi\ForumController@reopenForumTopic');
    $router->post('/{token}/deleteForumTopic', 'App\Controllers\BotApi\ForumController@deleteForumTopic');
    $router->post('/{token}/unpinAllForumTopicMessages', 'App\Controllers\BotApi\ForumController@unpinAllForumTopicMessages');
    $router->post('/{token}/getForumTopicIconStickers', 'App\Controllers\BotApi\ForumController@getForumTopicIconStickers');
    $router->post('/{token}/hideGeneralForumTopic', 'App\Controllers\BotApi\ForumController@hideGeneralForumTopic');
    $router->post('/{token}/unhideGeneralForumTopic', 'App\Controllers\BotApi\ForumController@unhideGeneralForumTopic');
    $router->post('/{token}/editGeneralForumTopic', 'App\Controllers\BotApi\ForumController@editGeneralForumTopic');
    $router->post('/{token}/closeGeneralForumTopic', 'App\Controllers\BotApi\ForumController@closeGeneralForumTopic');
    $router->post('/{token}/reopenGeneralForumTopic', 'App\Controllers\BotApi\ForumController@reopenGeneralForumTopic');
    $router->post('/{token}/unpinAllGeneralForumTopicMessages', 'App\Controllers\BotApi\ForumController@unpinAllGeneralForumTopicMessages');

    // === Stickers ===
    $router->post('/{token}/getStickerSet', 'App\Controllers\BotApi\StickerController@getStickerSet');
    $router->post('/{token}/getCustomEmojiStickers', 'App\Controllers\BotApi\StickerController@getCustomEmojiStickers');
    $router->post('/{token}/uploadStickerFile', 'App\Controllers\BotApi\StickerController@uploadStickerFile');
    $router->post('/{token}/createNewStickerSet', 'App\Controllers\BotApi\StickerController@createNewStickerSet');
    $router->post('/{token}/addStickerToSet', 'App\Controllers\BotApi\StickerController@addStickerToSet');
    $router->post('/{token}/setStickerPositionInSet', 'App\Controllers\BotApi\StickerController@setStickerPositionInSet');
    $router->post('/{token}/deleteStickerFromSet', 'App\Controllers\BotApi\StickerController@deleteStickerFromSet');
    $router->post('/{token}/replaceStickerInSet', 'App\Controllers\BotApi\StickerController@replaceStickerInSet');
    $router->post('/{token}/setStickerSetTitle', 'App\Controllers\BotApi\StickerController@setStickerSetTitle');
    $router->post('/{token}/setStickerSetThumbnail', 'App\Controllers\BotApi\StickerController@setStickerSetThumbnail');
    $router->post('/{token}/setCustomEmojiStickerSetThumbnail', 'App\Controllers\BotApi\StickerController@setCustomEmojiStickerSetThumbnail');
    $router->post('/{token}/setStickerEmojiList', 'App\Controllers\BotApi\StickerController@setStickerEmojiList');
    $router->post('/{token}/setStickerKeywords', 'App\Controllers\BotApi\StickerController@setStickerKeywords');
    $router->post('/{token}/setStickerMaskPosition', 'App\Controllers\BotApi\StickerController@setStickerMaskPosition');

    // === Payments ===
    $router->post('/{token}/sendInvoice', 'App\Controllers\BotApi\PaymentController@sendInvoice');
    $router->post('/{token}/createInvoiceLink', 'App\Controllers\BotApi\PaymentController@createInvoiceLink');
    $router->post('/{token}/answerShippingQuery', 'App\Controllers\BotApi\PaymentController@answerShippingQuery');
    $router->post('/{token}/answerPreCheckoutQuery', 'App\Controllers\BotApi\PaymentController@answerPreCheckoutQuery');
    $router->post('/{token}/getStarTransactions', 'App\Controllers\BotApi\PaymentController@getStarTransactions');
    $router->post('/{token}/refundStarPayment', 'App\Controllers\BotApi\PaymentController@refundStarPayment');

    // === Game ===
    $router->post('/{token}/sendGame', 'App\Controllers\BotApi\GameController@sendGame');
    $router->post('/{token}/setGameScore', 'App\Controllers\BotApi\GameController@setGameScore');
    $router->post('/{token}/getGameHighScores', 'App\Controllers\BotApi\GameController@getGameHighScores');

    // === Passport ===
    $router->post('/{token}/setPassportDataErrors', 'App\Controllers\BotApi\PassportController@setPassportDataErrors');

}, [RateLimitMiddleware::class, AuthMiddleware::class]);
