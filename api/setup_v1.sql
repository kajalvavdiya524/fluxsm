CREATE TABLE `user` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `email` VARCHAR(254) NOT NULL , `password` VARCHAR(254) NOT NULL , `organization_id` INT(11) unsigned NULL , `isadmin` TINYINT(2) NOT NULL DEFAULT '0',PRIMARY KEY (`id`) ) ENGINE = InnoDB;

CREATE TABLE `organization` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `name` VARCHAR(254) NOT NULL,PRIMARY KEY (`id`) ) ENGINE = InnoDB;

ALTER TABLE `user`
  ADD CONSTRAINT `c_fk_user_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

ALTER TABLE `store` ADD `organization_id` INT(11) UNSIGNED NULL AFTER `laborrate`;
ALTER TABLE `store`
  ADD CONSTRAINT `c_fk_store_organization_id` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
  
CREATE TABLE `usersession` ( `tokenid` VARCHAR(191) NOT NULL , `created` DATETIME NOT NULL , `ipaddress` VARCHAR(16) NOT NULL , `lastused` DATETIME NOT NULL , PRIMARY KEY (`tokenid`(191))) ENGINE = InnoDB;
INSERT INTO `organization` (`id`, `name`) VALUES ('1', 'Flux Test');
UPDATE store SET organization_id=1;
UPDATE user SET organization_id=1;