.. _notifications:

Notifications
=============

Summary
+++++++
You can use various notification methods to alert you when a server or service goes down. This includes email, SMS, and Telegram.
Email and SMS are native to Laravel. Telegram is new and can be found in `app/Notifications/Channels/TelegramChannel.php`.

Development
++++++++++++
Send notifications using the `Notification` facade. You can use the `DynamicNotification` class to create notifications with dynamic content.
`Notification::send(Auth::user(), new DynamicNotification('test_message', ['text' => 'Test message for Telegram notification']));`
DynamicNotification contains the name of the notification event and an array of data that can be used in the notification template.

On the background: Notification will be sent using the `NotificationChannel` class, which handles the logic for sending notifications through different channels.


Telegram
++++++++
To use Telegram notifications, you need to set up a Telegram bot and get the bot token.
You can then use the `TelegramChannel` to send messages to your Telegram chat.
To set up Telegram notifications, follow these steps:

1. Create a new bot using the BotFather on Telegram.
2. Get the bot token from the BotFather.
3. Go to the config page in the web interface and enter the bot token. Also globally enable Telegram notifications here.
4. Get your chat ID from Telegram and add it on your user profile page in the web interface.
5. You can now receive notifications in your Telegram chat.