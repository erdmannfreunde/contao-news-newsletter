<?php

/**
 * news_newsletter extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-news_newsletter
 */

/**
 * Add new notification type
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['news_newsletter']['news_newsletter_default'] = [
    'recipients'          => ['admin_email', 'recipient_email'],
    'email_subject'       => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'email_text'          => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'email_html'          => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'file_name'           => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'file_content'        => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'email_recipient_cc'  => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'email_recipient_bcc' => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'email_replyTo'       => ['admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'],
    'attachment_tokens'   => [],
];
