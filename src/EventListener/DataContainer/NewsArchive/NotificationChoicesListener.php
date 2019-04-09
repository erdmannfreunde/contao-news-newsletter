<?php

namespace ErdmannFreunde\ContaoNewsNewsletterBundle\EventListener\DataContainer\NewsArchive;

use Doctrine\DBAL\Connection;

class NotificationChoicesListener
{

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function onOptionsCallback(): array
    {
        $options = [];

        $statement = $this->connection->createQueryBuilder()
            ->select('id', 'title')
            ->from('tl_nc_notification')
            ->where('type=:type')
            ->orderBy('title')
            ->setParameter('type', 'news_newsletter_default')
            ->execute();

        while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
            $options[$row->id] = $row->title;

        }

        return $options;
    }
}
