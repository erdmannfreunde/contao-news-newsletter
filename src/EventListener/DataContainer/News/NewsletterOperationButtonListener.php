<?php


namespace ErdmannFreunde\ContaoNewsNewsletterBundle\EventListener\DataContainer\News;

use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\NewsArchiveModel;
use Contao\NewsletterRecipientsModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Haste\Util\Format;
use NotificationCenter\Model\Notification;

class NewsletterOperationButtonListener
{

    public function onButtonCallback($row, $href, $label, $title, $icon): string
    {
        $newsArchive = NewsArchiveModel::findByPk($row['pid']);
        if (null === $newsArchive) {
            return '';
        }

        if (!$newsArchive->newsletter || !$newsArchive->newsletter_channel || !$newsArchive->nc_notification) {
            return '';
        }

        // Toggle the record
        if (Input::get('newsletter')) {
            if ($this->sendNewsMessage(Input::get('newsletter'))) {
                Message::addConfirmation($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_confirm']);
            } else {
                Message::addError($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_error']);
            }

            throw new RedirectResponseException(Controller::getReferer());
        }

        // Return just an image if newsletter was sent
        if ($row['newsletter']) {
            return Image::getHtml(str_replace('.png', '_.png', $icon), $label);
        }

        // Add the confirmation popup
        $countRecipients = NewsletterRecipientsModel::countBy(['pid=? AND active=1'], $newsArchive->newsletter_channel);
        $attributes      =
            sprintf(
                'onclick="if(!confirm(\'%s\'))return false;Backend.getScrollOffset()"',
                sprintf(
                    $GLOBALS['TL_LANG']['tl_news']['sendNewsletterConfirm'],
                    $countRecipients
                )
            );

        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Controller::addToUrl($href . '&newsletter=' . $row['id']),
            StringUtil::specialchars($title),
            $attributes,
            Image::getHtml($icon, $label)
        );
    }

    private function sendNewsMessage($newsId): bool
    {
        $news = NewsModel::findByPk($newsId);
        if (null === $news || $news->newsletter) {
            // News not found or newsletter already sent
            return false;
        }

        $newsArchive = $news->getRelated('pid');
        if (null === $newsArchive || !$newsArchive->newsletter || !$newsArchive->newsletter_channel
            || !$newsArchive->nc_notification) {
            return false;
        }

        $notification = Notification::findByPk($newsArchive->nc_notification);
        if (null === $notification) {
            return false;
        }

        $recipients = NewsletterRecipientsModel::findBy(['pid=? AND active=1'], $newsArchive->newsletter_channel);
        if (null === $recipients) {
            return false;
        }

        $tokens = [];

        // Generate news archive tokens
        foreach ($newsArchive->row() as $k => $v) {
            $tokens['news_archive_' . $k] = Format::dcaValue('tl_news_archive', $k, $v);
        }

        // Generate news tokens
        foreach ($news->row() as $k => $v) {
            $tokens['news_' . $k] = Format::dcaValue('tl_news', $k, $v);
        }

        $tokens['news_text'] = '';

        $contentElements = ContentModel::findPublishedByPidAndTable($news->id, 'tl_news');

        // Generate news text
        if (null !== $contentElements) {
            while ($contentElements->next()) {
                $tokens['news_text'] .= Controller::getContentElement($contentElements->id);
            }
        }

        // Generate news URL
        $objPage = PageModel::findWithDetails($news->getRelated('pid')->jumpTo);
        if (null === $objPage) {
            throw new \RuntimeException('Page not found: ' . $news->getRelated('pid')->jumpTo);
        }

        $tokens['news_url'] =
            ($objPage->rootUseSSL ? 'https://' : 'http://') . ($objPage->domain ?: Environment::get('host')) . TL_PATH
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
            $sent[]                    = $notification->send($tokens);
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
