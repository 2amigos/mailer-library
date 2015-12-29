DROP TABLE IF EXISTS `mail_queue`;

CREATE TABLE `mail_queue` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `message` MEDIUMTEXT NOT NULL,
  `attempt` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `state` CHAR(1) NOT NULL DEFAULT 'N', /* 'N': queued (new), 'A': processing (active), 'C': completed */
  `sentTime` TIMESTAMP NULL,
  `timeToSend` TIMESTAMP,
  `createdTime` TIMESTAMP,
  `updatedTime` TIMESTAMP,
  PRIMARY KEY  (id),
  KEY id (id),
  KEY time_to_send (timeToSend)
) ENGINE=INNODB;
