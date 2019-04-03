SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `snake_node`;
CREATE TABLE `snake_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `node_name` varchar(155) NOT NULL DEFAULT '' COMMENT '节点名称',
  `control_name` varchar(155) NOT NULL DEFAULT '' COMMENT '控制器名',
  `action_name` varchar(155) NOT NULL COMMENT '方法名',
  `is_menu` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否是菜单项 1不是 2是',
  `type_id` int(11) NOT NULL COMMENT '父级节点id',
  `style` varchar(155) DEFAULT '' COMMENT '菜单样式',
  `sort` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('1','用户管理','#','#','2','0','fa fa-users','99');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('2','管理员管理','user','index','2','1','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('3','添加管理员','user','useradd','1','2','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('4','编辑管理员','user','useredit','1','2','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('5','删除管理员','user','userdel','1','2','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('6','角色管理','role','index','2','1','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('7','添加角色','role','roleadd','1','6','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('8','编辑角色','role','roleedit','1','6','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('9','删除角色','role','roledel','1','6','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('10','分配权限','role','giveaccess','1','6','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('11','系统管理','#','#','2','0','fa fa-desktop','98');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('12','数据备份/还原','data','index','2','11','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('13','备份数据','data','importdata','1','12','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('14','还原数据','data','backdata','1','12','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('15','节点管理','node','index','2','1','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('16','添加节点','node','nodeadd','1','15','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('17','编辑节点','node','nodeedit','1','15','','0');
insert into `snake_node`(`id`,`node_name`,`control_name`,`action_name`,`is_menu`,`type_id`,`style`,`sort`) values('18','删除节点','node','nodedel','1','15','','0');
