/*备份blog表*/

/*备份maxim表*/
INSERT INTO `maxim`(`id`,`author`,`motto`,`mtime`) values('1','李白','仰天大笑出门去，我辈岂是蓬蒿人。','2016-07-31 22:59:53');

/*备份role表*/
INSERT INTO `role`(`rid`,`role_name`,`status`) values('1','admin','Active');

/*备份role_permission表*/
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('1','admin','Blog\\Controller\\IndexController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('2','admin','Blog\\Controller\\IndexController','classlist');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('3','admin','Blog\\Controller\\IndexController','viewblog');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('4','admin','Application\\Controller\\IndexController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('5','admin','Blog\\Controller\\ListController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('6','admin','Blog\\Controller\\ListController','add');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('7','admin','Blog\\Controller\\ListController','edit');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('8','admin','Blog\\Controller\\ListController','delete');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('9','admin','Upload\\Controller\\UpfileController','upload');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('10','admin','Upload\\Controller\\UpfileController','upfileBrowser');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('11','admin','Upload\\Controller\\UpfileController','rename');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('12','admin','Upload\\Controller\\UpfileController','delfile');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('13','admin','Upload\\Controller\\UpfileController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('14','admin','Backup\\Controller\\IndexController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('15','admin','Motto\\Controller\\IndexController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('16','admin','Motto\\Controller\\IndexController','captcha');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('17','admin','Motto\\Controller\\IndexController','add');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('18','admin','Motto\\Controller\\IndexController','edit');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('19','admin','Motto\\Controller\\IndexController','delete');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('20','admin','ZF2AuthAcl\\Controller\\IndexController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('21','admin','ZF2AuthAcl\\Controller\\IndexController','logout');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('22','admin','ZF2AuthAcl\\Controller\\UserManagerController','index');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('23','admin','ZF2AuthAcl\\Controller\\UserManagerController','add');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('24','admin','ZF2AuthAcl\\Controller\\UserManagerController','deluser');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('25','admin','ZF2AuthAcl\\Controller\\UserManagerController','findpass');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('26','admin','ZF2AuthAcl\\Controller\\UserManagerController','resetpass');
INSERT INTO `role_permission`(`id`,`role_name`,`resource_name`,`permission_name`) values('27','admin','ZF2AuthAcl\\Controller\\UserManagerController','rpnexus');

/*备份user_role表*/
INSERT INTO `user_role`(`id`,`user_id`,`role_id`) values('1','1','1');

/*备份users表*/
INSERT INTO `users`(`id`,`account`,`email`,`password`,`status`,`created_on`,`modified_on`) values('1','DrowedFish','jiangwei386@126.com','20cedaeb18392575c403f07909d6df10ab0b2703','Y','2016-07-31 19:29:41','2016-07-31 19:29:41');

