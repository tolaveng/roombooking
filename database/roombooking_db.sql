/*
SQLyog Ultimate v11.33 (64 bit)
MySQL - 10.1.13-MariaDB : Database - roombooking_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`roombooking_db` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `roombooking_db`;

/*Table structure for table `tb_attendant` */

DROP TABLE IF EXISTS `tb_attendant`;

CREATE TABLE `tb_attendant` (
  `att_id` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(24) NOT NULL,
  `att_firstname` varchar(32) DEFAULT NULL,
  `att_lastname` varchar(32) DEFAULT NULL,
  `att_more` tinytext,
  PRIMARY KEY (`att_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tb_attendant` */

/*Table structure for table `tb_block` */

DROP TABLE IF EXISTS `tb_block`;

CREATE TABLE `tb_block` (
  `block_id` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `room_id` tinyint(4) DEFAULT NULL,
  `block_date` date DEFAULT NULL,
  `block_from` time DEFAULT NULL,
  `block_to` time DEFAULT NULL,
  PRIMARY KEY (`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tb_block` */

/*Table structure for table `tb_booking` */

DROP TABLE IF EXISTS `tb_booking`;

CREATE TABLE `tb_booking` (
  `booking_id` varchar(24) NOT NULL COMMENT 'roomid_date_time',
  `room_id` tinyint(4) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `booking_session` tinyint(1) NOT NULL,
  `booking_on` datetime NOT NULL,
  `notified` tinyint(1) NOT NULL DEFAULT '0',
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  PRIMARY KEY (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tb_booking` */

/*Table structure for table `tb_room` */

DROP TABLE IF EXISTS `tb_room`;

CREATE TABLE `tb_room` (
  `room_id` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `room_num` varchar(128) NOT NULL DEFAULT '',
  `building_id` tinyint(4) DEFAULT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `hidden` tinyint(1) DEFAULT NULL,
  `session` tinyint(1) NOT NULL,
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `tb_room` */

insert  into `tb_room`(`room_id`,`room_num`,`building_id`,`description`,`hidden`,`session`) values (1,'TD 303',NULL,'Photography Room',0,60),(2,'TC 210 Computer Lab',NULL,'Networking computer lab',0,60);

/*Table structure for table `tb_setting` */

DROP TABLE IF EXISTS `tb_setting`;

CREATE TABLE `tb_setting` (
  `key` varchar(16) NOT NULL DEFAULT '',
  `val` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tb_setting` */

/*Table structure for table `tb_user` */

DROP TABLE IF EXISTS `tb_user`;

CREATE TABLE `tb_user` (
  `user_id` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `firstname` varchar(32) NOT NULL DEFAULT '',
  `lastname` varchar(32) NOT NULL DEFAULT '',
  `phone` varchar(16) DEFAULT NULL,
  `verify_code` varchar(6) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT NULL,
  `role` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `tb_user` */

insert  into `tb_user`(`user_id`,`password`,`firstname`,`lastname`,`phone`,`verify_code`,`verified`,`blocked`,`role`) values ('admin@swin.edu.au','465c194afb65670f38322df087f0a9bb225cc257e43eb4ac5a0c98ef5b3173ac','Admin','Administrator','','407617',0,0,1),('demo@student.swin.edu.au','4253dfc1e6a2e8626f13696efe4f3c4ba4fc036a9dc31082639b9984bdd09150','Demo','User','DEMOPHONE','669338',0,0,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
