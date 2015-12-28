<?php
namespace Da\Mailer\Queue\Backend\Pdo;

use BadMethodCallException;
use Da\Mailer\Queue\Backend\MailJobInterface;
use Da\Mailer\Queue\Backend\QueueStoreAdapterInterface;
use PDO;

/**
 *
 * PdoQueueAdapter.php
 *
 * Date: 24/12/15
 * Time: 13:29
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 */
class PdoQueueStoreAdapter implements QueueStoreAdapterInterface
{
    /**
     * @var string the name of the table to store the messages. If not created, please use
     */
    private $tableName;
    /**
     * @var PdoQueueStoreConnection
     */
    protected $connection;

    public function __construct(PdoQueueStoreConnection $connection, $tableName = 'mail_queue')
    {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->init();
    }

    /**
     * @return PdoQueueStoreConnection
     */
    public function init()
    {
        return $this->getConnection()->connect();
    }

    /**
     * @return PdoQueueStoreConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param MailJobInterface|PdoMailJob $mailJob
     *
     * @return bool
     */
    public function enqueue(MailJobInterface $mailJob)
    {
        $sql = sprintf(
            'INSERT INTO `%s` (`message`, `timeToSend`) VALUES (:message, :timeToSend)',
            $this->tableName
        );
        $query = $this->getConnection()->getInstance()->prepare($sql);
        $query->bindValue(':message', $mailJob->getMessage());
        $query->bindValue(':timeToSend', $mailJob->getTimeToSend());

        return $query->execute();
    }

    /**
     * @return MailJobInterface|PdoMailJob
     */
    public function dequeue()
    {
        $this->getConnection()->getInstance()->beginTransaction();

        $mailJob = null;
        $sqlText = 'SELECT `id`, `message`, `attempt`
            FROM `%s` WHERE `timeToSend` <= NOW() AND `state`=:state
            ORDER BY id ASC LIMIT 1 FOR UPDATE';
        $sql = sprintf($sqlText, $this->tableName);
        $query = $this->getConnection()->getInstance()->prepare($sql);

        $query->bindValue(':state', PdoMailJob::STATE_NEW);
        $query->execute();
        $queryResult = $query->fetch(PDO::FETCH_ASSOC);

        if ($queryResult) {
            //
            $sqlText = 'UPDATE `%s` SET `state`=:state WHERE `id`=:id';
            $sql = sprintf($sqlText, $this->tableName);
            $query = $this->getConnection()->getInstance()->prepare($sql);
            $query->bindValue(':state', PdoMailJob::STATE_ACTIVE);
            $query->bindValue(':id', $queryResult['id'], PDO::PARAM_INT);
            $query->execute();

            $mailJob = new PdoMailJob($queryResult);
        }

        $this->getConnection()->getInstance()->commit();

        return $mailJob;
    }

    /**
     * @param MailJobInterface|PdoMailJob $mailJob
     *
     * @return bool
     */
    public function ack(MailJobInterface $mailJob)
    {
        if ($mailJob->isNewRecord()) {
            throw new BadMethodCallException('PdoMailJob cannot be a new object to be acknowledged');
        }

        $sqlText = 'UPDATE `%s`
                SET `attempt`=:attempt, `state`=:state, `timeToSend`=:timeToSend, `sentTime`=:sentTime
                WHERE `id`=:id';
        $sql = sprintf($sqlText, $this->tableName);
        $sentTime = $mailJob->isCompleted() ? date('Y-m-d H:i:s', time()) : null;
        $query = $this->getConnection()->getInstance()->prepare($sql);

        $query->bindValue(':id', $mailJob->getId(), PDO::PARAM_INT);
        $query->bindValue(':attempt', $mailJob->getAttempt(), PDO::PARAM_INT);
        $query->bindValue(':state', $mailJob->getState());
        $query->bindValue(':timeToSend', $mailJob->getTimeToSend());
        $query->bindValue(':sentTime', $sentTime);

        return $query->execute();

    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        $sql = sprintf(
            'SELECT COUNT(`id`) FROM `%s` WHERE `timeToSend` <= NOW() AND `state`=:state ORDER BY id ASC LIMIT 1',
            $this->tableName
        );
        $query = $this->getConnection()->getInstance()->prepare($sql);

        $query->bindValue(':state', PdoMailJob::STATE_NEW);
        $query->execute();

        return intval($query->fetchColumn(0)) === 0;
    }

}
