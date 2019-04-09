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
 * Add fields to tl_news
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['newsletter'] = [
    'label'   => &$GLOBALS['TL_LANG']['tl_news']['newsletter'],
    'exclude' => true,
    'eval'    => ['doNotCopy' => true],
    'sql'     => "char(1) NOT NULL default ''"
];

/**
 * Add the operation to tl_news
 */
$GLOBALS['TL_DCA']['tl_news']['list']['operations']['newsletter'] = [
    'label'           => &$GLOBALS['TL_LANG']['tl_news']['sendNewsletter'],
    'icon'            => 'system/modules/news_newsletter/assets/newsletter.png',
    'button_callback' => ['tl_news_newsletter', 'newsletterIcon']
];

class tl_news_newsletter extends tl_news
{

    /**
     * Return the "newsletter" button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     *
     * @return string
     */
    public function newsletterIcon($row, $href, $label, $title, $icon)
    {
        $objArchive = \NewsArchiveModel::findByPk($row['pid']);
        if (null === $objArchive) {
            return '';
        }

        if (!$objArchive->newsletter || !$objArchive->newsletter_channel || !$objArchive->nc_notification) {
            return '';
        }

        // Toggle the record
        if (Input::get('newsletter')) {
            if ($this->sendNewsMessage(Input::get('newsletter'))) {
                Message::addConfirmation($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_confirm']);
            } else {
                Message::addError($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_error']);
            }

            self::redirect(self::getReferer());
        }

        // Return just an image if newsletter was sent
        if ($row['newsletter']) {
            return Image::getHtml(str_replace('.png', '_.png', $icon), $label);
        }

        // Add the confirmation popup
        $intRecipients =
            \NewsletterRecipientsModel::countBy(array('pid=? AND active=1'), $objArchive->newsletter_channel);
        $attributes    =
            'onclick="if(!confirm(\'' . sprintf($GLOBALS['TL_LANG']['tl_news']['sendNewsletterConfirm'], $intRecipients)
            . '\'))return false;Backend.getScrollOffset()"';

        return '<a href="' . self::addToUrl($href . '&newsletter=' . $row['id']) . '" title="' . specialchars($title)
               . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }

    /**
     * Send the news message
     *
     * @param integer
     *
     * @return boolean
     */
    public function sendNewsMessage($intId)
    {
        /** @var NewsModel|Model $news */
        $news = \NewsModel::findByPk($intId);
        if ($news === null || $news->newsletter) {
            // News not found or newsletter already sent
            return false;
        }

        $newsArchive = $news->getRelated('pid');
        if ($newsArchive === null || !$newsArchive->newsletter || !$newsArchive->newsletter_channel
            || !$newsArchive->nc_notification) {
            return false;
        }

        $notification = \NotificationCenter\Model\Notification::findByPk($newsArchive->nc_notification);
        if ($notification === null) {
            return false;
        }

        $recipients = \NewsletterRecipientsModel::findBy(array('pid=? AND active=1'), $newsArchive->newsletter_channel);
        if ($recipients === null) {
            return false;
        }

        $tokens = [];

        // Generate news archive tokens
        foreach ($newsArchive->row() as $k => $v) {
            $tokens['news_archive_' . $k] = \Haste\Util\Format::dcaValue('tl_news_archive', $k, $v);
        }

        // Generate news tokens
        foreach ($news->row() as $k => $v) {
            $tokens['news_' . $k] = \Haste\Util\Format::dcaValue('tl_news', $k, $v);
        }

        $tokens['news_text'] = '';

        $contentElements = \ContentModel::findPublishedByPidAndTable($news->id, 'tl_news');

        // Generate news text
        if ($contentElements !== null) {
            while ($contentElements->next()) {
                $tokens['news_text'] .= $this->getContentElement($contentElements->id);
            }
        }

        // Generate news URL
        $objPage = \PageModel::findWithDetails($news->getRelated('pid')->jumpTo);
        if (null === $objPage) {
            throw new RuntimeException('Page not found: ' . $news->getRelated('pid')->jumpTo);
        }

        $tokens['news_url'] =
            ($objPage->rootUseSSL ? 'https://' : 'http://') . ($objPage->domain ?: \Environment::get('host')) . TL_PATH
            . '/' . $objPage->getFrontendUrl(
                (($GLOBALS['TL_CONFIG']['useAutoItem'] && !$GLOBALS['TL_CONFIG']['disableAlias']) ? '/' : '/items/')
                . ((!$GLOBALS['TL_CONFIG']['disableAlias'] && $news->alias != '') ? $news->alias : $news->id),
                $objPage->language
            );

        // Administrator e-mail
        $tokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        $sent = [];
        while ($recipients->next()) {
            $tokens['recipient_email'] = $recipients->email;
            $sent[] = $notification->send($tokens);
        }
        $sent = array_merge(...$sent);
        if (in_array(false, $sent, true)) {
            Message::addError('Mindestens eine Nachricht konnte nicht versendet werden.');
        }

        // Set the newsletter flag
        $news->newsletter = 1;
        $news->save();

        return true;
    }
}
