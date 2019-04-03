SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `snake_role`;
CREATE TABLE `snake_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `role_name` varchar(155) NOT NULL COMMENT '角色名称',
  `rule` varchar(255) DEFAULT '' COMMENT '权限节点数据',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

insert into `snake_role`(`id`,`role_name`,`rule`) values('1','超级管理员','*');
insert into `snake_role`(`id`,`role_name`,`rule`) values('2','系统维护员','1,2,3,4,5,6,7,8,9,10,15,16,17,18,19,20,22');
